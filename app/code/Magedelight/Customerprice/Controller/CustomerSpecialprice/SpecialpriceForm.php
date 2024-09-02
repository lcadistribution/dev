<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\CustomerSpecialprice;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class SpecialpriceForm extends Action
{

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {

        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return array
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $productId = $this->getRequest()->getParam('pid');
       
        $block = $resultPage->getLayout()
                ->createBlock('Magedelight\Customerprice\Block\SpecialpriceForm')
                ->setTemplate('Magedelight_Customerprice::specialpriceform.phtml')
                ->setData('productid', $productId)
                ->toHtml();

        $result->setData(['output' => $block]);
        return $result;
    }
}
