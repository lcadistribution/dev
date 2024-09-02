<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model;

use Amasty\MWishlist\Api\Data\WishlistItemDataInterface;
use Magento\Framework\Model\AbstractModel;

class WishlistItemData extends AbstractModel implements WishlistItemDataInterface
{
    public function getQty(): float
    {
        return (float)$this->getData(WishlistItemDataInterface::QTY);
    }

    public function getSku(): string
    {
        return (string)$this->getData(WishlistItemDataInterface::SKU);
    }

    public function getDescription(): string
    {
        return (string)$this->getData(WishlistItemDataInterface::DESCRIPTION);
    }

    public function setDescription(string $description): WishlistItemDataInterface
    {
        return $this->setData(WishlistItemDataInterface::DESCRIPTION, $description);
    }

    public function setQty(float $qty): WishlistItemDataInterface
    {
        return $this->setData(WishlistItemDataInterface::QTY, $qty);
    }

    public function setSku(string $sku): WishlistItemDataInterface
    {
        return $this->setData(WishlistItemDataInterface::SKU, $sku);
    }
}
