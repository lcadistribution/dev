<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Plugin\Catalog\Product;

use Magedelight\Customerprice\Helper\Data;

class ListProduct
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var use \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var use \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        Data $helper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->layout = $layout;
        $this->helper = $helper;
        $this->httpContext = $httpContext;
        $this->request = $request;
    }
    
    public function afterGetProductDetailsHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result,
        \Magento\Catalog\Model\Product $product
    ) {

        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn && $this->helper->isEnabled() && $this->helper->displayButton() && (in_array($this->request->getFullActionName(), $this->helper->getActionArray()))) {
            $buttonTitle = ($this->helper->getConfig('customerprice/general/display_button_label')) ? $this->helper->getConfig('customerprice/general/display_button_label') : __('View your Price');
            $result .= '<a href="javascript:void(0)" class="askprice" id="'.$product->getId().'"><span>'.$buttonTitle.'</span></a>';
        }

        return $result;
    }
}
