<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;
use Magedelight\Customerprice\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class CatalogRulePrice
 */
class PpcRulePrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'ppc_rule_price';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface
     */
    protected $calculatorInterface;

    /**
     * @var use Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var use \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param TimezoneInterface $dateTime
     * @param StoreManager $storeManager
     * @param Session $customerSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param CalculatorInterface $calculatorInterface
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        StoreManager $storeManager,
        Session $customerSession,
        CalculatorInterface $calculatorInterface,
        Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->calculatorInterface = $calculatorInterface;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (in_array($this->request->getFullActionName(), $this->helper->getActionArray())) {
            if ($this->helper->getConfig('customerprice/general/hide_price') && $this->helper->isEnabled()) {
                return false;
            }
        }
        
        if ($this->helper->isAdvanced()) {
            $this->value = false;
        } else {
            if (null === $this->value) {
                if ($this->product->hasData(self::PRICE_CODE)) {
                    $this->value = (float)$this->product->getData(self::PRICE_CODE) ?: false;
                } else {
                    $this->value = $this->calculatorInterface->
                    calculate($this->product->getData('price'), $this->product);
                    $this->value = $this->value ? (float)$this->value : false;
                }
                if ($this->value) {
                    $this->value = $this->priceCurrency->convertAndRound($this->value);
                }
            }
        }
        return $this->value;
    }
}
