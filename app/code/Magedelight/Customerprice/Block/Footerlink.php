<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block;

class Footerlink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpcontext,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->httpcontext = $httpcontext;
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function getHref()
    {
        $context = $this->httpcontext;
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        if ($isLoggedIn) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $url = trim($this->scopeConfig->getValue('customerprice/general/identifier', $storeScope));

            return $this->getUrl($url);
        } else {
            return $this->getUrl('customer/account/login/');
        }
    }

    public function getLabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $label = $this->scopeConfig->getValue('customerprice/general/title', $storeScope);

        return __($label);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $mode_enable = $this->scopeConfig->getValue('customerprice/general/enable', $storeScope);
        $top_enable = $this->scopeConfig->getValue('customerprice/general/footer_enable', $storeScope);

        if (!$mode_enable) {
            return '';
        }

        if (!$top_enable) {
            return '';
        }

        return parent::_toHtml();
    }
}
