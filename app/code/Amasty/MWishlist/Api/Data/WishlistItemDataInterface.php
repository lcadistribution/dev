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
interface WishlistItemDataInterface
{
    public const SKU = 'sku';
    public const QTY = 'quantity';
    public const DESCRIPTION = 'description';

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return \Amasty\MWishlist\Api\Data\WishlistItemDataInterface
     */
    public function setDescription(string $description): WishlistItemDataInterface;

    /**
     * @param float $qty
     * @return \Amasty\MWishlist\Api\Data\WishlistItemDataInterface
     */
    public function setQty(float $qty): WishlistItemDataInterface;

    /**
     * @param string $sku
     * @return \Amasty\MWishlist\Api\Data\WishlistItemDataInterface
     */
    public function setSku(string $sku): WishlistItemDataInterface;
}
