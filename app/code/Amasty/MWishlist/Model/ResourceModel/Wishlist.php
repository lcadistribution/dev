<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Model\ResourceModel\Wishlist as NativeWishlist;

class Wishlist extends NativeWishlist
{
    protected function _construct()
    {
        $this->_init(WishlistInterface::MAIN_TABLE, WishlistInterface::WISHLIST_ID);
    }
}
