<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\Block\Pager;
use Amasty\MWishlist\ViewModel\HelperContext;
use Amasty\MWishlist\ViewModel\Pagination;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Wishlist\Block\Customer\Wishlist as NativeWishlist;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as ItemCollection;

class Wishlist extends NativeWishlist
{
    public const AVAILABLE_LIMIT = [8 => 8, 16 => 16, 40 => 40];

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('My Wish List'));
        /** @var Pager $pager */
        if ($pager = $this->getChildBlock('wishlist_item_pager')) {
            $pager->setAvailableLimit(static::AVAILABLE_LIMIT);
            $pager->setData('path', PostHelper::VIEW_WISHLIST_ROUTE);
            $pager->setUseContainer(
                true
            )->setShowAmounts(
                true
            )->setFrameLength(
                $this->getPaginationHelper()->getPaginationFrame()
            )->setJump(
                $this->getPaginationHelper()->getPaginationFrameSkip()
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getWishlistItems()
            )->setWishlistId(
                (int) $this->getWishlistInstance()->getId()
            );
        }

        return $this;
    }

    /**
     * @return HelperContext
     */
    public function getHelperContext(): HelperContext
    {
        return $this->_data['helper_context'];
    }

    /**
     * @return Pagination
     */
    public function getPaginationHelper(): Pagination
    {
        return $this->_data['pagination'];
    }

    /**
     * @param ItemCollection $collection
     * @return $this
     */
    protected function _prepareCollection($collection)
    {
        parent::_prepareCollection($collection);
        $collection->setOrder('added_at', AbstractDb::SORT_ORDER_DESC);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateUrl(): string
    {
        return $this->getUrl(PostHelper::UPDATE_WISHLIST_ROUTE, [
            'wishlist_id' => $this->getWishlistInstance()->getId()
        ]);
    }
}
