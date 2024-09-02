<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\ViewModel\Tabs as TabsHelper;
use Magento\Framework\View\Element\Template;

class WishlistList extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/list/wishlist.phtml';

    /**
     * @var int|null
     */
    private $activeTabId;

    public function getTabs(): array
    {
        return $this->getTabsHelper()->getTabs();
    }

    public function isActiveTab(int $tabId): bool
    {
        if ($this->activeTabId === null) {
            $this->activeTabId = $this->getTabsHelper()->resolveActiveTabId();
        }

        return $tabId === $this->activeTabId;
    }

    public function getTabsHelper(): TabsHelper
    {
        return $this->_data['tabs_helper'];
    }
}
