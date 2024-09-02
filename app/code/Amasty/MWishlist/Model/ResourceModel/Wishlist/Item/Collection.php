<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Wishlist\Item;

use Magento\Wishlist\Model\ResourceModel\Item\Collection as NativeCollection;

class Collection extends NativeCollection
{
    public const ADDED_AT = 'added_at';

    public function limitAndOrderByDate(int $limit): void
    {
        $this->addOrder(self::ADDED_AT, Collection::SORT_ORDER_DESC);
        $this->setPageSize($limit);
    }

    public function getProductIdsForAlert(): array
    {
        $this->addFieldToSelect('product_id')->getSelect()
            ->joinInner(
                ['wishlist' => $this->getTable('wishlist')],
                'wishlist.wishlist_id = main_table.wishlist_id'
            )
            ->joinInner(
                ['customer_entity' => $this->getTable('customer_entity')],
                'customer_entity.entity_id = wishlist.customer_id'
            )
            ->joinInner(
                ['product_price' => $this->getTable('catalog_product_index_price')],
                'product_price.entity_id = main_table.product_id'
                . ' AND product_price.final_price != main_table.product_price'
                . ' AND product_price.customer_group_id = customer_entity.group_id'
                . ' AND product_price.website_id = customer_entity.website_id'
            );

        return array_unique($this->getColumnValues('product_id'));
    }
}
