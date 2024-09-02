<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;

class ProcessFinalPrice implements ObserverInterface
{

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CalculatorInterface
     */
    protected $catalogPriceCalculator;

    /**
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CalculatorInterface $catalogPriceCalculator
     */
    public function __construct(
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CalculatorInterface $catalogPriceCalculator
    ) {
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return \Magedelight\Customerprice\Observer\ProcessFinalPrice
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $ppcFinalPrice = null;

        if ($this->helper->isCustomerPriceAllow()) {
            $oldFinalPrice = $product->getData('final_price');
            foreach ($product->getPriceInfo()->getPrices() as $price) {
                if ($price->getPriceCode() == 'ppc_rule_price') {
                    $ppcFinalPrice = $price->getValue();
                }
            }
            if ($ppcFinalPrice) {
                $newFinalPrice = min(
                    $oldFinalPrice,
                    $this->convertCurrentToBase($ppcFinalPrice)
                );
                if ($newFinalPrice !== $oldFinalPrice) {
                    $product->setPpcPrice(1);
                }

                $product->setData('final_price', $newFinalPrice);
            } else {
                if ($this->helper->isAdvanced()) {
                    $discount = $this->catalogPriceCalculator->calculate($oldFinalPrice, $product);
                    if ($discount) {
                        $product->setData('final_price', $discount);
                    }
                }
            }
        }
    }
    
    private function convertCurrentToBase($amount = 0, $store = null, $currency = null)
    {
        if ($store == null) {
            $store = $this->storeManager->getStore()->getStoreId();
        }
        $rate = $this->priceCurrency->convert($amount, $store, $currency);
        $rate = $this->priceCurrency->convert($amount, $store) / $amount;
        $amount = $amount / $rate;
        return $this->priceCurrency->round($amount);
    }
}
