<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Pricing\Render\Bundle;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use Magedelight\Customerprice\Helper\Data;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;

class FinalPriceBox extends \Magento\Bundle\Pricing\Render\FinalPriceBox
{
    /**
     * @var use Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var use \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var use \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
    * @var use CustomerpriceRepositoryInterface
    */
    protected $customerPriceRepository;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param \Magento\Framework\Pricing\Render\RendererPool $rendererPool
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerpriceRepositoryInterface $customerPriceRepository
     * @param array $data
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver
     * @param \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface $minimalPriceCalculator
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        \Magento\Framework\Pricing\Price\PriceInterface $price,
        \Magento\Framework\Pricing\Render\RendererPool $rendererPool,
        Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerpriceRepositoryInterface $customerPriceRepository,
        array $data = [],
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver = null,
        \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->httpContext = $httpContext;
        $this->customerPriceRepository = $customerPriceRepository;
        parent::__construct($context, $saleableItem, $price, $rendererPool, $data, $salableResolver, $minimalPriceCalculator);
    }
    
    protected function wrapResult($html)
    {
       $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $specialPriceHtml = '';

        // Ask Your price button
        if ($this->helper->isEnabled() && $this->helper->specialPriceButton() && !in_array($this->getSaleableItem()->getTypeId(), ['bundle', 'grouped', 'configurable'])) {
            $specialPriceLabel = $this->helper->specialPriceButtonLabel();
            $specialPriceHtml  = $isLoggedIn ? '<div class="special-price-button" data-product-id=' . $this->getSaleableItem()->getId() . '><span>' . $specialPriceLabel . '</span></div>' : '<a class="md-tooltip" href="javascript:void(0)" data-toggle="tooltip" data-html="true" title="This feature is available for registered customers only.">' . $specialPriceLabel . '</a>';
        }

        $priceBoxHtml = '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';

        //Valid Date Display
        if ($isLoggedIn && $this->helper->isEnabled()) {

            if (!$this->helper->getConfig('customerprice/general/hide_price')) {

                $date = $this->customerPriceRepository->getCustomerPriceValidDate($this->getSaleableItem()->getId(), $this->helper->getUserId(), $this->helper->getCurrentWebsiteId());
            
                $date = ($date) ? __('( Valid till %1 )',$date) : "";

                $priceBoxHtml = '<div class="price-box ' . $this->getData('css_classes') . '" ' .
                'data-role="priceBox" ' .
                'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
                '>' . $html . ' '.$date.'</div>';
            }
        }

        // ajax base price html

        $loaderHtml = '<div id="' . $this->getSaleableItem()->getId() . '" class="price-loader price-loader-' . $this->getSaleableItem()->getId() . '">
            <div style="display: inline-block;max-width: 27px;height: auto;">
                <img src="' . $this->getViewFileUrl('Magedelight_Customerprice::images/loader.gif') . '" width="50%" height="50%">
            </div>
            <span style="font-weight: lighter;">' . __("Fetching Price...") . '</span>
            </div>';

        if ($isLoggedIn && $this->helper->isEnabled() && (in_array($this->request->getFullActionName(), $this->helper->getActionArray()))&& $this->helper->getConfig('customerprice/general/hide_price')) {
            if ($this->helper->getConfig('customerprice/general/show_price')) {
                return $specialPriceHtml . $priceBoxHtml;
            }elseif (!$this->helper->getConfig('customerprice/general/show_price')) {
                return $specialPriceHtml . '<div class="price-box"></div>';
            }

        }elseif(!$this->helper->getConfig('customerprice/general/hide_price') && $this->helper->getConfig('customerprice/general/ajax_price') && (in_array($this->request->getFullActionName(), $this->helper->getActionArray())) && $this->helper->isCustomerPriceAllow()) {
            return $specialPriceHtml . $loaderHtml. '<div class="price-box"></div>';
        }else{
            return $specialPriceHtml . $priceBoxHtml;
        }
    }
}
