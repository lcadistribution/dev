<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Wishlist\Item;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Model\Wishlist;
use Amasty\MWishlist\Model\Wishlist\Management as WishlistManagement;
use DomainException;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Item as WishlistItem;

class Management
{
    /**
     * @var WishlistManagement
     */
    private $wishlistManagement;

    public function __construct(WishlistManagement $wishlistManagement)
    {
        $this->wishlistManagement = $wishlistManagement;
    }

    /**
     * @param WishlistItem $item
     * @param WishlistInterface|Wishlist $wishlist
     * @param null|int $qty
     * @throws InvalidArgumentException|DomainException|LocalizedException
     */
    public function copy(
        WishlistItem $item,
        WishlistInterface $wishlist,
        ?int $qty = null
    ) {
        if (!$item->getId()) {
            throw new InvalidArgumentException();
        }

        if ($item->getWishlistId() == $wishlist->getId()) {
            throw new DomainException();
        }

        $buyRequest = $item->getBuyRequest();
        if ($qty) {
            $buyRequest->setQty($qty);
        }

        $wishlist->addNewItem($item->getProduct(), $buyRequest);
    }

    /**
     * @param WishlistItem $item
     * @param WishlistInterface|Wishlist $wishlist
     * @param null|int $qty
     * @throws InvalidArgumentException|DomainException|LocalizedException
     */
    public function move(
        WishlistItem $item,
        WishlistInterface $wishlist,
        ?int $qty = null
    ) {
        if (!$item->getId()) {
            throw new InvalidArgumentException();
        }

        if ($item->getWishlistId() == $wishlist->getId()) {
            throw new DomainException(null, 1);
        }

        if (!$this->wishlistManagement->getCustomerWishlists()->getItemById($item->getWishlistId())) {
            throw new DomainException(null, 2);
        }

        $buyRequest = $item->getBuyRequest();
        if ($qty) {
            $buyRequest->setQty($qty);
        }

        $wishlist->addNewItem($item->getProduct(), $buyRequest);

        $qtyDiff = $item->getQty() - $qty;
        if ($qty && $qtyDiff > 0) {
            $item->setQty($qtyDiff);
            $item->save();
        } else {
            $item->delete();
        }
    }
}
