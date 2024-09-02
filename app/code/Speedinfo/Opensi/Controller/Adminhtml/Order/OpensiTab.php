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

namespace Speedinfo\Opensi\Controller\Adminhtml\Order;

class OpensiTab extends \Magento\Sales\Controller\Adminhtml\Order
{
  /**
   * @var \Magento\Framework\View\LayoutFactory
   */
  protected $layoutFactory;

  /**
   * @param \Magento\Backend\App\Action $context
   * @param \Magento\Framework\Registry $coreRegistry
   * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
   * @param \Magento\Framework\Translate\InlineInterface $translateInline
   * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
   * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
   * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
   * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
   * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
   * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Magento\Framework\View\LayoutFactory $layoutFactory
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
   */
  public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Framework\Registry $coreRegistry,
    \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
    \Magento\Framework\Translate\InlineInterface $translateInline,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
    \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
    \Magento\Sales\Api\OrderManagementInterface $orderManagement,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\View\LayoutFactory $layoutFactory
  ) {
    $this->layoutFactory = $layoutFactory;

    parent::__construct(
        $context,
        $coreRegistry,
        $fileFactory,
        $translateInline,
        $resultPageFactory,
        $resultJsonFactory,
        $resultLayoutFactory,
        $resultRawFactory,
        $orderManagement,
        $orderRepository,
        $logger
    );
  }

  /**
   * Generate order history for ajax request
   *
   * @return \Magento\Framework\Controller\Result\Raw
   */
  public function execute()
  {
    $this->_initOrder();

    $layout = $this->layoutFactory->create();
    $html = $layout->createBlock('Speedinfo\Opensi\Block\Adminhtml\Order\View\Tab\Opensi')->toHtml();
    $this->_translateInline->processResponseBody($html);
    $resultRaw = $this->resultRawFactory->create();
    $resultRaw->setContents($html);

    return $resultRaw;
  }
}
