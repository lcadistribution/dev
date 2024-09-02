<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Block\CatalogProduct\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class Giftcard
 * @package Mageplaza\TableCategoryView\Block\CatalogProduct\View
 */
class Giftcard extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Giftcard constructor.
     *
     * @param Template\Context $context
     * @param Data $helperData
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->helperData    = $helperData;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getGiftCardBlock()
    {
        return $this->helperData->createObject('\Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard');
    }

    /**
     * @param float $amount
     * @param bool $includeContainer
     *
     * @return string
     */
    public function convertAndFormatCurrency($amount, $includeContainer = true)
    {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function checkDisplayAmount($product)
    {
        return !empty($product->getAllowOpenAmount());
    }
}
