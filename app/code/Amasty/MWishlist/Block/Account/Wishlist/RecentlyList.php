<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Magento\Framework\View\Element\Template;

class RecentlyList extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::components/page/recently_list.phtml';

    /**
     * @return false|string
     */
    public function getJsLayout()
    {
        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }
}
