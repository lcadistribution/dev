<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Purchased\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    public function addLimit(int $limit): void
    {
        $this->getSelect()->limit($limit);
    }

    /**
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->modifySelect();

        return $this;
    }

    private function modifySelect(): void
    {
        $this->getSelect()
            ->joinInner(
                ['order_item' => $this->getTable('sales_order_item')],
                'main_table.quote_item_id = order_item.quote_item_id'
            )->joinInner(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                'order_item.product_id = product_entity.entity_id'
            )->reset(\Magento\Framework\DB\Select::COLUMNS)->columns([
                'entity_id' => 'order_item.product_id',
                'placed_from_list' => 'COUNT(order_item.product_id)',
                'sku' => 'product_entity.sku',
                'name' => 'order_item.name',
                'qty' => 'ROUND(SUM(order_item.qty_ordered))'
            ])->group('order_item.product_id');
    }
}
