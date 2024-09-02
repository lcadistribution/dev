<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class HelperContext implements ArgumentInterface
{
    /**
     * @var WishlistHelper
     */
    private $wishlistHelper;

    public function __construct(WishlistHelper $wishlistHelper)
    {
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * @return WishlistHelper
     */
    public function getWishlistHelper(): WishlistHelper
    {
        return $this->wishlistHelper;
    }
}
