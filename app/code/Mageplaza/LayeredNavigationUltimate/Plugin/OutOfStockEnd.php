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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Plugin;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;
use Mageplaza\LayeredNavigationUltimate\Helper\Data;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\OutOfStockEnd as OutOfStockEndSource;
use Zend_Db_Expr;

/**
 * Class OutOfStockEnd
 * @package Mageplaza\LayeredNavigationUltimate\Plugin
 */
class OutOfStockEnd
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * OutOfStockEnd constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Toolbar $subject
     * @param callable $proceed
     * @param Collection $collection
     *
     * @return mixed
     */
    public function aroundSetCollection(Toolbar $subject, callable $proceed, $collection)
    {
        $baseOn = (int) $this->helper->getShowStockEnd();
        if ($baseOn === OutOfStockEndSource::DISABLED || !$this->helper->isEnabled()) {
            return $proceed($collection);
        }
        $stockItemTable = $collection->getTable('cataloginventory_stock_item');
        $condition      = "e.entity_id = {$stockItemTable}.product_id";

        if ($baseOn === OutOfStockEndSource::BASE_ON_QTY) {
            $collection->getSelect()->joinInner(
                [$stockItemTable],
                $condition,
                ["IF(e.type_id = 'simple',{$stockItemTable}.qty,{$stockItemTable}.is_in_stock) AS sort_qty"]
            );
            $collection->getSelect()->order(new Zend_Db_Expr(
                "sort_qty = 0"
            ));
            return $proceed($collection);
        } elseif ($baseOn === OutOfStockEndSource::BASE_ON_LABEL) {
            $collection->getSelect()->order('is_salable DESC');
        }

        return $proceed($collection);
    }
}
