<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block;

use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\View\Element\Template;

abstract class AbstractPostBlock extends Template
{
    public const POST_HELPER_KEY = 'post_helper';

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[self::POST_HELPER_KEY];
    }
}
