<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response.
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Data
     */
    protected $helper;

    /**
     *
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magedelight\Customerprice\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResponseInterface $response,
        \Magedelight\Customerprice\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_response = $response;
        $this->helper = $helper;
    }

    /**
     * Validate and Match.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }
        
        if ($request->getModuleName() == 'md_customerprice') {
            return;
        }
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $route = trim($this->scopeConfig->getValue('customerprice/general/identifier', $storeScope));

        $identifier = trim($request->getPathInfo(), '/');
        if ($identifier == $route) {
            $request->setModuleName('md_customerprice')->setControllerName('index')->setActionName('offers');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } else {
            return false;
        }

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class,
            ['request' => $request]
        );
    }
}
