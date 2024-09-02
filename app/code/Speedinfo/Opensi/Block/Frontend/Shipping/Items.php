<?php
/**
 * 2003-2017 OpenSi Connect
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Speedinfo
 * @package     Speedinfo_Opensi
 * @copyright   Copyright (c) 2017 Speedinfo SARL (http://www.speedinfo.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Speedinfo\Opensi\Block\Frontend\Shipping;

class Items extends \Magento\Shipping\Block\Items
{
  protected $_coreRegistry = null;
  protected $_scopeConfig;
  protected $_documentsFactory;
  protected $_timezone;
  protected $_urlInterface;
  protected $_shippingData;

  /**
   * @param \Magento\Framework\View\Element\Template\Context $context
   * @param \Magento\Framework\Registry $registry
   * @param \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
   * @param \Magento\Framework\Url $urlInterface,
   * @param \Magento\Shipping\Helper\Data $shippingData
   */
  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
    \Magento\Framework\Url $urlInterface,
    \Magento\Shipping\Helper\Data $shippingData
  ) {
    $this->_coreRegistry = $registry;
    $this->_scopeConfig = $context->getScopeConfig();
    $this->_documentsFactory = $documentsFactory;
    $this->_timezone = $context->getLocaleDate();
    $this->_urlInterface = $urlInterface;
    $this->_shippingData = $shippingData;

    parent::__construct($context, $registry);
  }


  /**
   * Get deliverynotes preference (depending on store)
   */
  public function getDeliveryNotesConfig()
  {
    return $this->_scopeConfig->getValue('opensi_preferences/manage_downloadable_documents/deliverynotes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  }


  /**
	 * Get OpenSi documents
   *
	 * @param $incrementId
	 */
	public function getOpensiDocuments($incrementId)
	{
    $collection = null;
    $resultPage = $this->_documentsFactory->create();

    if ($this->_scopeConfig->getValue('opensi_preferences/manage_downloadable_documents/display_deliverynotes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
    {
      // Display deliverynotes
      $collection = $resultPage->getCollection();
      $collection
    	 ->addFieldToFilter('document_type', array('eq' => 'BL'))
       ->addFieldToFilter('main_table.increment_id', $incrementId)
       ->getSelect()
       ->joinLeft(array('sfs' => $collection->getTable('sales_shipment')), 'main_table.document_number = sfs.opensi_delivery_note', 'sfs.increment_id as shipment_id')
       ->order('created_at DESC');
    }

    return $collection;
  }


  /**
   * Get date format
   *
   * @return date
   */
  public function getDateFormat($date)
  {
    return $this->_timezone->formatDateTime($date, \IntlDateFormatter::LONG);
  }


  /**
   * Get document URL
   *
   * @param $entityId
   * @param $key
   */
  public function getDocumentUrl($entityId, $key)
  {
    return $this->_urlInterface->getUrl(
      'opensi/documents/index',
      ['id' => $entityId, 'key' => $key,'_nosid' => true]
    );
  }


  /**
   * Get tracking numbers
   * Display tracking number with link if available
   *
   * @param $shipment
   */
  public function getTrackingNumbers($shipment)
  {
    $trackingNumbers = array();
    $tracks = $shipment->getTracksCollection();

    if ($tracks->count())
    {
      foreach ($tracks as $track)
      {
        if($track->isCustom())
				{
					$trackingNumbers[] = $track->getNumber();
				} else {
					$trackingNumbers[] = '<a href="#" class="action track" title="'.__('Track all shipments').'" data-mage-init=\'{"popupWindow": {"windowURL":"'.$this->trackAllShipments($track).'","windowName":"trackorder","width":800,"height":600,"left":0,"top":0,"resizable":1,"scrollbars":1}}\'>'.$track->getNumber().'</a>';
				}
      }
    }

    return $trackingNumbers;
  }


  /**
   * Track all shipments
   * Get link to open popup with tracking informations
   *
   * @param $model
   */
  public function trackAllShipments($model)
  {
    return $this->_shippingData->getTrackingPopupUrlBySalesModel($model);
  }


  /**
   * Get OpenSi delivery notes
   * Display delivery notes with hypertext link if available
   *
   * @param $incrementId
   */
  public function getOpenSiDeliveryNotes($incrementId, $shipment)
  {
    $deliveryNotes = array();

    if ($this->getOpensiDocuments($incrementId)->getSize() > 0)
    {
      foreach ($this->getOpensiDocuments($incrementId) as $document)
      {
        if ($document->getDocumentNumber() && $document->getShipmentId() == $shipment->getIncrementId()) {
          $deliveryNotes[] = '<a href="'.$this->getDocumentUrl($document->getDocumentId(), md5($document->getDocumentKey())).'">'.$document->getDocumentNumber().'</a>';
        }
      }
    } else {
      $deliveryNotes[] = '-';
    }

    return $deliveryNotes;
  }


  /**
   * Get all items for the current shipment
   *
   * @param $shipment
   */
  public function getShipmentItems($shipment)
  {
    foreach ($shipment->getItemsCollection() as $item)
    {
      // If there is a configurable or bundle product, continue (details are taken in the "parent" product)
      if ($item->getOrderItem()->getParentItemId()) {
        continue;
      }

      echo '
        <tr>
          <td><strong><i>'.$item->getName().'</i></strong>';

      if (count($item->getOrderItem()->getProductOptions()) > 0)
      {
        foreach ($item->getOrderItem()->getProductOptions() as $key => $productOptions)
        {
          // Get configurable or bundle product informations
          switch ($key)
          {
            case 'attributes_info':
              foreach ($productOptions as $option)
              {
                echo '<br />'.$option['label'].' : '.$option['value'];
              }
              break;

            case 'bundle_options':
              foreach ($productOptions as $option)
              {
                foreach ($option['value'] as $value) {
                  echo '<br />'.$value['qty'].' x '.$value['title'];
                }
              }
              break;
          }
        }
      }

      echo '
          </td>
          <td>'.$item->getSku().'</td>
          <td>'.$item->getQty().'</td>
        </tr>';
    }
  }
}
