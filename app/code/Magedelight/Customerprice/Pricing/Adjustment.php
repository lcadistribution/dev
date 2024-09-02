<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;
use Magento\Catalog\Model\Product\Type;
use Magedelight\Customerprice\Helper\Data as helper;

class Adjustment implements AdjustmentInterface
{
    /**
     * Adjustment code tax
     */
    const ADJUSTMENT_CODE = 'pricepercustomer';

    /**
     * @var CalculatorInterface
     */
    protected $catalogPriceCalculator;

    /**
     * @var helper
     */
    protected $helper;
    /**
     * @var int|null
     */
    protected $sortOrder;

    /**
     * @var use \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param CalculatorInterface $catalogPriceCalculator
     * @param helper $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param $sortOrder = null
     */
    public function __construct(
        CalculatorInterface $catalogPriceCalculator,
        helper $helper,
        \Magento\Framework\App\Request\Http $request,
        $sortOrder = null
    ) {
        $this->catalogPriceCalculator = $catalogPriceCalculator;
        $this->helper = $helper;
        $this->sortOrder = $sortOrder;
        $this->request = $request;
    }

    /**
     * Get adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    /**
     * Define if adjustment is included in base price
     *
     * @return bool
     */
    public function isIncludedInBasePrice()
    {
        return true;
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     */
    public function isIncludedInDisplayPrice()
    {
        return true;
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return 0;
    }

    /**
     * Apply adjustment amount and return result value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {

        if (in_array($this->request->getFullActionName(), $this->helper->getActionArray())) {
            if ($this->helper->getConfig('customerprice/general/hide_price') && $this->helper->isEnabled()) {
                return $amount;
            }
        }
        
        if (isset($context[CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG])) {
            return $amount;
        }

//        if (isset($context[BundleSelectionPrice::CONFIGURATION_OPTION_FLAG])) {
//            return $amount;
//        }

        if (!$this->helper->isAdvanced()) {
            return $amount;
        }
        
        
        $value = $this->catalogPriceCalculator->calculate($amount, $saleableItem);
        if ($value) {
            return $value;
        }
        return $amount;
    }

    /**
     * Check if adjustment should be excluded from calculations along with the given adjustment
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function isExcludedWith($adjustmentCode)
    {
        return $this->getAdjustmentCode() === $adjustmentCode;
    }

    /**
     * Return sort order position
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
