<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Amasty\MWishlist\Model\Wishlist as WishlistModel;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as NativeCollection;

class Collection extends NativeCollection
{
    protected function _construct()
    {
        $this->_init(WishlistModel::class, WishlistResourceModel::class);
    }

    /**
     * @param string $wishlistName
     * @return $this
     */
    public function filterByName(string $wishlistName)
    {
        $this->addFieldToFilter(
            $this->getConnection()->getIfNullSql(
                WishlistInterface::NAME,
                sprintf('"%s"', __('Wish List'))
            ),
            $wishlistName
        );

        return $this;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function filterByType(int $type)
    {
        $this->addFieldToFilter(WishlistInterface::TYPE, $type);
        return $this;
    }

    public function orderByDate()
    {
        $this->setOrder(WishlistInterface::UPDATED_AT, $this::SORT_ORDER_DESC);
    }
}
