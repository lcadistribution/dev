<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Index;

class Offers extends \Magento\Framework\App\Action\Action
{
    const MD_LAYER = 'mdlayer';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_requestObject;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;
    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
   
    protected $response;
    protected $redirect;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     *
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\DataObject $requestObject
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\DataObject $requestObject,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_requestObject = $requestObject;
        $this->scopeConfig = $scopeConfig;
        $this->layerResolver = $layerResolver;
        $this->helper = $helper;
        $this->url = $url;
        $this->response = $context->getResponse();
        $this->redirect = $context->getRedirect();
        $this->httpContext = $httpContext;
    }

    public function execute()
    {
        if ($this->helper->isEnabled()) {
            $this->layerResolver->create(self::MD_LAYER);
            $resultPage = $this->resultPageFactory->create();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $layout = $this->scopeConfig->getValue('customerprice/general/layout', $storeScope);

            if ($layout != 'empty') {
                $resultPage->getConfig()->addBodyClass('page-products');
            }
            if ($layout == '1column' || $layout == '3columns') {
                $resultPage->getConfig()->addBodyClass('page-with-filter');
            }

            $resultPage->getConfig()->setPageLayout($layout);
            $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

            if ($isLoggedIn) {
                return $resultPage;
            } else {
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('customer/account/login');
            }
        } else {
            $homeUrl = $this->url->getUrl();
            $this->getResponse()->setRedirect($homeUrl);
            return;
        }
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
