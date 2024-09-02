<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Item;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use DomainException;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Item as WishlistItem;

class Copy extends AbstractMove
{
    /**
     * @param WishlistItem $item
     * @param WishlistInterface $wishlist
     * @param int|null $qty
     * @throws InvalidArgumentException|DomainException|LocalizedException
     */
    protected function moveAction(
        WishlistItem $item,
        WishlistInterface $wishlist,
        ?int $qty
    ) {
        $this->getItemManagement()->copy($item, $wishlist, $qty);
    }

    /**
     * @return string
     */
    protected function getNotAllowedMessage(): string
    {
        return __('%s items cannot be copied: %s.')->render();
    }

    /**
     * @return string
     */
    protected function getFailedMessage(): string
    {
        return __('We can\'t copy %s items.')->render();
    }

    /**
     * @return string
     */
    protected function getSuccessMessage(): string
    {
        return __('%s got copied to <a href="%s">%s</a> successfully.')->render();
    }
}
