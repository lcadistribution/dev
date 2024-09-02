<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist\Item\Column;

use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Wishlist\Block\AbstractBlock;

class Move extends AbstractBlock
{
    /**
     * @return string
     */
    public function getPostData(): string
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::MOVE_ITEMS_ROUTE), [
            sprintf('selected[%s]', $this->getItem()->getId()) => 1,
            'wishlist_id' => $this->getItem()->getWishlistId(),
            UpdateAction::COMPONENT_PARAM => 'itemsQty',
            UpdateAction::BLOCK_PARAM => 'customer.wishlist'
        ]);
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[AbstractPostBlock::POST_HELPER_KEY];
    }
}