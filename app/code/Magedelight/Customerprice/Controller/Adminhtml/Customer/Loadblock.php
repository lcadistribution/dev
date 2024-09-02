<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Customer;

class Loadblock extends \Magento\Framework\App\Action\Action
{   
    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var  \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \Magento\Framework\Registry                     $registry
     * @param \Magento\Framework\View\Result\PageFactory      $resultPageFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $request = $this->getRequest();
        $block = $request->getParam('block');

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->addHandle('md_customerprice_customer_load_block_json');
        $resultPage->addHandle('md_customerprice_customer_load_block_'.$block);

        $result = $resultPage->getLayout()->renderElement('content');

        return $this->resultRawFactory->create()->setContents($result);
    }
}
