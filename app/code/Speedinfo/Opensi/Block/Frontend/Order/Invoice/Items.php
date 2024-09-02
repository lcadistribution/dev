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

namespace Speedinfo\Opensi\Block\Frontend\Order\Invoice;

class Items extends \Magento\Sales\Block\Order\Invoice\Items
{
  protected $_coreRegistry = null;
  protected $_scopeConfig;
  protected $_documentsFactory;
  protected $_timezone;
  protected $_urlInterface;
  protected $_priceHelper;

  /**
   * @param \Magento\Framework\View\Element\Template\Context $context
   * @param \Magento\Framework\Registry $registry
   * @param \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
   * @param \Magento\Framework\Url $urlInterface
   */
   public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
    \Magento\Framework\Url $urlInterface,
    \Magento\Framework\Pricing\Helper\Data $priceHelper
  ) {
    $this->_coreRegistry = $registry;
    $this->_scopeConfig = $context->getScopeConfig();
    $this->_documentsFactory = $documentsFactory;
    $this->_timezone = $context->getLocaleDate();
    $this->_urlInterface = $urlInterface;
    $this->_priceHelper = $priceHelper;

    parent::__construct($context, $registry);
  }

  /**
   * Get invoices preference (depending on store)
   */
  public function getInvoicesConfig()
  {
    return $this->_scopeConfig->getValue('opensi_preferences/manage_downloadable_documents/invoices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    $displayOpenSiInvoices = $this->_scopeConfig->getValue('opensi_preferences/manage_downloadable_documents/display_invoices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $displayOpenSiCreditMemos = $this->_scopeConfig->getValue('opensi_preferences/manage_downloadable_documents/display_credit_memos', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    if ($displayOpenSiInvoices && $displayOpenSiCreditMemos)
    {
      // Display all documents
      $collection = $resultPage->getCollection();
      $collection->getSelect()->where('increment_id = "'.$incrementId.'" AND document_type != "BL"');
    }
    elseif ($displayOpenSiInvoices)
    {
      // Display only invoices
      $collection = $resultPage->getCollection();
      $collection->getSelect()->where('increment_id = "'.$incrementId.'" AND document_type = "F"');
    }
    elseif ($displayOpenSiCreditMemos)
    {
      // Display only credit memos
      $collection = $resultPage->getCollection();
      $collection->getSelect()->where('increment_id = "'.$incrementId.'" AND document_type = "A"');
    }

    return $collection;
  }

  /**
   * Get document type text
   *
   * @param $type
   * @return string
   */
  public function getDocumentTypeText($type)
  {
    switch ($type)
    {
      case 'F':
        return __('Invoice #');
        break;
      case 'A':
        return __('Credit memo #');
        break;
    }
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
   * Get all items for the current invoice
   *
   * @param $order
   */
  public function getInvoiceDetails($invoice)
  {
    foreach ($invoice->getAllItems() as $items)
    {
      foreach ($items->getOrderItem() as $item)
      {
        // If there is a configurable or bundle product, continue (details are taken in the "parent" product)
        if ($item['parent_item_id']) {
          continue;
        }

        echo '
          <tr>
            <td><strong><i>'.$item['name'].'</i></strong>';

        if (count($item['product_options']) > 0)
        {
          foreach ($item['product_options'] as $key => $productOptions)
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
                    echo '<br />'.$value['qty'].' x '.$value['title']. ' - '.$this->_priceHelper->currency($value['price'], true, false);
                  }
                }
                break;
            }
          }
        }

        echo '
            </td>
            <td>'.$item['sku'].'</td>
            <td>'.$item['qty_invoiced'].'</td>
            <td>'.$this->_priceHelper->currency($item['price'], true, false).'</td>
            <td>'.$this->_priceHelper->currency($item['row_total'], true, false).'</td>
          </tr>';
      }
    }

    echo '
      <tr>
        <th colspan="4" class="mark" scope="row">'.__('Subtotal').' :</th>
        <td>'.$this->_priceHelper->currency($invoice->getSubtotal(), true, false).'</td>
      </tr>
      <tr class="discount">
        <th colspan="4" class="mark" scope="row">'.__('Discount').' :</th>
        <td>'.$this->_priceHelper->currency($invoice->getDiscountAmount(), true, false).'</td>
      </tr>
      <tr class="totals-tax">
        <th colspan="4" class="mark" scope="row">'.__('Taxes').' :</th>
        <td>'.$this->_priceHelper->currency($invoice->getTaxAmount(), true, false).'</td>
      </tr>
      <tr class="grand_total">
        <th colspan="4" class="mark" scope="row"><strong>'.__('Grand Total').'</strong> :</th>
        <td><strong>'.$this->_priceHelper->currency($invoice->getGrandTotal(), true, false).'</strong></td>
      </tr>';
  }
}
