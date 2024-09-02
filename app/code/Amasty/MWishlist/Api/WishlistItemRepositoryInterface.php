<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api;

/**
 * @api
 */
interface WishlistItemRepositoryInterface
{
    /**
     * @param int $wishlistId
     * @param int $itemId
     * @param int $customerId
     * @return bool
     */
    public function deleteItemFromWishlist(int $wishlistId, int $itemId, int $customerId): bool;

    /**
     * @param int $wishlistId
     * @param int $itemId
     * @param int $customerId
     * @return bool
     */
    public function addProductToCartFromWishlist(int $wishlistId, int $itemId, int $customerId): bool;

    /**
     * @param int $wishlistId
     * @param int $cartItemId
     * @param int $customerId
     * @return bool
     */
    public function addProductFromCartToWishlist(int $wishlistId, int $cartItemId, int $customerId): bool;
}
