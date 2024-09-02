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

namespace Mageplaza\TableCategoryView\Plugin\CatalogModel\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magento\Bundle\Block\DataProviders\OptionPriceRenderer;
use Magento\Bundle\ViewModel\ValidateQuantity;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class OptionPlugin
 * @package Mageplaza\TableCategoryView\Plugin\CatalogModel\Product\View\Type\Bundle
 */
class OptionPlugin
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * OptionPlugin constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Option $subject
     */
    public function beforeGetData(Option $subject)
    {
        if (class_exists(OptionPriceRenderer::class)) {
            $subject->setTierPriceRenderer(
                $this->helper->getObject(OptionPriceRenderer::class)
            );
        }
        if (class_exists(ValidateQuantity::class)) {
            $subject->setData('validateQuantityViewModel', $this->helper->getObject(ValidateQuantity::class)
            );
        }
    }
}
