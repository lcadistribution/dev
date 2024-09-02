<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block;

use Magento\Theme\Block\Html\Pager as NativePager;

class Pager extends NativePager
{
    public const WISHLIST_ID = 'wishlist';

    /**
     * Limit '_current' param for support multi-pagers on one page.
     * Remove '_escape' for avoid &amp; int url. Added in url only trusted params such as current page and limit.
     *
     * @param array $params
     *
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = $this->getRouteParams();

        $urlParams['_current'] = ['wishlist_id'];
        $urlParams['_use_rewrite'] = true;
        $urlParams['_fragment'] = $this->getFragment();
        $urlParams['_query'] = array_merge(
            [
                $this->getPageVarName() => $this->getRequest()->getParam($this->getPageVarName()),
                $this->getLimitVarName() => $this->getRequest()->getParam($this->getLimitVarName()),
            ],
            $params
        );

        return $this->getUrl($this->getPath(), $urlParams);
    }

    protected function getRouteParams(): array
    {
        $params = [];

        if ($this->getWishlistId()) {
            $params['wishlist_id'] = $this->getWishlistId();
        }

        return $params;
    }

    public function setWishlistId(int $wishlistId): Pager
    {
        return $this->setData(self::WISHLIST_ID, $wishlistId);
    }

    public function getWishlistId(): ?int
    {
        return $this->getData(self::WISHLIST_ID);
    }

    public function clearCollection(): void
    {
        $this->_collection = null;
    }
}
