<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;

class ValidateWishlistAccess
{
    /**
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function execute(WishlistInterface $wishlist, ?int $customerId = null)
    {
        if (null === $customerId) {
            return;
        }

        if (0 === $customerId) {
            throw new LocalizedException(__('The current user cannot perform operations on wishlist.'));
        }

        if ($customerId !== (int)$wishlist->getCustomerId()) {
            throw new NotFoundException(__('The wishlist was not found for this customer.'));
        }
    }
}
