<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api;

use Amasty\MWishlist\Api\Data\WishlistInterface;

interface WishlistProviderInterface
{
    /**
     * Retrieve current wishlist
     *
     * @param int $wishlistId
     * @return WishlistInterface
     */
    public function getWishlist(?int $wishlistId = null);
}
