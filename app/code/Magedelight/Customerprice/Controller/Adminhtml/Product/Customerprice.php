<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Product;

class Customerprice extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $_resultPageFactory;

    /**
    * @var \Magento\Framework\Controller\Result\RawFactory
    */
    protected $resultRawFactory;

    /**
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \Magento\Framework\View\Result\PageFactory      $resultPageFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('product_customerprice');
        $this->getResponse()->appendBody($block->toHtml());
    }
}
