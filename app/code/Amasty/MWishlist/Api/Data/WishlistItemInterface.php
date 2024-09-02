<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api\Data;

/**
 * @api
 */
interface WishlistItemInterface
{
    public const WISHLIST_ITEM_ID = 'wishlist_item_id';
    public const WISHLIST_ID = 'wishlist_id';
    public const PRODUCT_ID = 'product_id';
    public const STORE_ID = 'store_id';
    public const ADDED_AT = 'added_at';
    public const DESCRIPTION = 'description';
    public const QTY = 'qty';
    public const PRODUCT_PRICE = 'product_price';

    /**
     * @return int
     */
    public function getWishlistItemId(): int;

    /**
     * @param int $wishlistItemId
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setWishlistItemId(int $wishlistItemId): WishlistItemInterface;

    /**
     * @return int
     */
    public function getWishlistId(): int;

    /**
     * @param int $wishlistId
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setWishlistId(int $wishlistId): WishlistItemInterface;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setProductId(int $productId): WishlistItemInterface;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setStoreId(int $storeId): WishlistItemInterface;

    /**
     * @return string
     */
    public function getAddedAt(): string;

    /**
     * @param string $addedAt
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setAddedAt(string $addedAt): WishlistItemInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setDescription(string $description): WishlistItemInterface;

    /**
     * @return int
     */
    public function getQty(): int;

    /**
     * @param int $qty
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setQty($qty): WishlistItemInterface;

    /**
     * @return string
     */
    public function getProductPrice(): string;

    /**
     * @param string $productPrice
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface
     */
    public function setProductPrice(string $productPrice): WishlistItemInterface;
}
