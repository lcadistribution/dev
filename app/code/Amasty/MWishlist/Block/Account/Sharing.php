<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account;

use Amasty\MWishlist\Controller\WishlistProvider;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Wishlist\Block\Customer\Sharing as NativeSharing;
use Magento\Wishlist\Helper\Rss;

class Sharing extends NativeSharing
{
    /**
     * @var WishlistProvider
     */
    private $wishlistProvider;

    /**
     * @var Rss
     */
    private $rssHelper;

    public function __construct(
        WishlistProvider $wishlistProvider,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Framework\Session\Generic $wishlistSession,
        Rss $rssHelper,
        array $data = []
    ) {
        parent::__construct($context, $wishlistConfig, $wishlistSession, $data);
        $this->wishlistProvider = $wishlistProvider;
        $this->rssHelper = $rssHelper;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('List Sharing'));
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl(PostHelper::SEND_WISHLIST, [
            'wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId()
        ]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(PostHelper::VIEW_WISHLIST_ROUTE, [
            'wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId()
        ]);
    }

    /**
     * @return Rss
     */
    public function getRssHelper(): Rss
    {
        return $this->rssHelper;
    }
}
