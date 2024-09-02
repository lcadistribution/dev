<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model;

use Amasty\MWishlist\Api\Data\WishlistItemInterface;
use Magento\Wishlist\Model\Item as NativeItem;

class WishlistItem extends NativeItem implements WishlistItemInterface
{
    public function getWishlistItemId(): int
    {
        return (int)$this->_getData(WishlistItemInterface::WISHLIST_ITEM_ID);
    }

    public function setWishlistItemId(int $wishlistItemId): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::WISHLIST_ITEM_ID, $wishlistItemId);

        return $this;
    }

    public function getWishlistId(): int
    {
        return (int)$this->_getData(WishlistItemInterface::WISHLIST_ID);
    }

    public function setWishlistId(int $wishlistId): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::WISHLIST_ID, $wishlistId);

        return $this;
    }

    public function getProductId(): int
    {
        return (int)$this->_getData(WishlistItemInterface::PRODUCT_ID);
    }

    public function setProductId(int $productId): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::PRODUCT_ID, $productId);

        return $this;
    }

    public function getStoreId(): int
    {
        return (int)$this->_getData(WishlistItemInterface::STORE_ID);
    }

    public function setStoreId(int $storeId): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::STORE_ID, $storeId);

        return $this;
    }

    public function getAddedAt(): string
    {
        return (string)$this->_getData(WishlistItemInterface::ADDED_AT);
    }

    public function setAddedAt(string $addedAt): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::ADDED_AT, $addedAt);

        return $this;
    }

    public function getDescription(): string
    {
        return (string)$this->_getData(WishlistItemInterface::DESCRIPTION);
    }

    public function setDescription(string $description): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::DESCRIPTION, $description);

        return $this;
    }

    public function getQty(): int
    {
        return (int)$this->_getData(WishlistItemInterface::QTY);
    }

    public function setQty($qty): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::QTY, $qty);

        return $this;
    }

    public function getProductPrice(): string
    {
        return (string)$this->_getData(WishlistItemInterface::PRODUCT_PRICE);
    }

    public function setProductPrice(string $productPrice): WishlistItemInterface
    {
        $this->setData(WishlistItemInterface::PRODUCT_PRICE, $productPrice);

        return $this;
    }
}
