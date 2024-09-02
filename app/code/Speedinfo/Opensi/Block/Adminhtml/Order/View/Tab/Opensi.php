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

namespace Speedinfo\Opensi\Block\Adminhtml\Order\View\Tab;

class Opensi extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
  protected $_template = 'order/view/tab/opensi.phtml';
  protected $_coreRegistry = null;
  protected $_timezone;
  protected $_documentsFactory;
  protected $_urlInterface;

  /**
   * @param \Magento\Backend\Block\Template\Context $context
   * @param \Magento\Framework\Registry $registry
   * @param \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
   * @param \Magento\Framework\Url $urlInterface
   */
  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
    \Magento\Framework\Url $urlInterface
  ) {
    parent::__construct($context);

    $this->_coreRegistry = $registry;
    $this->_timezone = $context->getLocaleDate();
    $this->_documentsFactory = $documentsFactory;
    $this->_urlInterface = $urlInterface;
  }

  /**
   * Retrieve order model instance
   *
   * @return \Magento\Sales\Model\Order
   */
  public function getOrder()
  {
    return $this->_coreRegistry->registry('current_order');
  }

  /**
   * {@inheritdoc}
   */
  public function getTabLabel()
  {
    return __('OpenSi Connect');
  }

  /**
   * {@inheritdoc}
   */
  public function getTabTitle()
  {
    return __('OpenSi Connect');
  }

  /**
   * {@inheritdoc}
   */
  public function canShowTab()
  {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden()
  {
    return false;
  }

  /**
   * Get Tab Class
   *
   * @return string
   */
  public function getTabClass()
  {
    return 'ajax only';
  }

  /**
   * Get Class
   *
   * @return string
   */
  public function getClass()
  {
    return $this->getTabClass();
  }

  /**
   * Get Tab Url
   *
   * @return string
   */
  public function getTabUrl()
  {
    return $this->getUrl('opensitab/*/opensiTab', ['_current' => true]);
  }

  /**
   * Get date format
   *
   * @return date
   */
  public function getDateFormat($date)
  {
    return $this->_timezone->formatDateTime($date, \IntlDateFormatter::MEDIUM);
  }

  /**
   * Get documents
   *
   * @return collection
   */
  public function getDocuments($incrementId)
  {
    $resultPage = $this->_documentsFactory->create();
    $collection = $resultPage->getCollection();
    $collection
			//->addFieldToFilter('document_type', array('neq' => 'BL'))
			->addFieldToFilter('increment_id', $incrementId)
			->getSelect()
			->order('created_at DESC');

    return $collection;
  }

  /**
   * Get document type
   *
   * @return string
   */
  public function getDocumentType($type)
  {
    switch ($type) {
      case 'F':
        $html = __('Invoice');
        break;
      case 'BL':
        $html = __('Delivery note');
        break;
      case 'A':
        $html = __('Credit memo');
        break;
    }

    return $html;
  }

  /**
   * Get document URL
   */
  public function getDocumentUrl($entityId, $key)
  {
    return $this->_urlInterface->getUrl(
      'opensi/documents/index',
      ['id' => $entityId, 'key' => $key,'_nosid' => true]
    );
  }
}
