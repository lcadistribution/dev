<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Block\Account\Wishlist\WishlistList\Tab as WishlistTab;
use Amasty\MWishlist\Model\Networks;
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\Model\Wishlist\Management;
use Amasty\MWishlist\ViewModel\Pagination;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Title extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::components/page/title.phtml';

    /**
     * @var Type
     */
    private $type;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var Management
     */
    private $wishlistManagement;

    /**
     * @var Networks
     */
    private $networks;

    /**
     * @var WishlistTab
     */
    private $wishlistTab;

    public function __construct(
        Management $wishlistManagement,
        WishlistProviderInterface $wishlistProvider,
        Networks $networks,
        Type $type,
        Context $context,
        WishlistTab $wishlistTab,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->type = $type;
        $this->wishlistProvider = $wishlistProvider;
        $this->wishlistManagement = $wishlistManagement;
        $this->networks = $networks;
        $this->wishlistTab = $wishlistTab;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->updateTypes($this->jsLayout);
        $this->jsLayout = $this->updateActions($this->jsLayout);
        $this->jsLayout = $this->updateItemsQty($this->jsLayout);
        $this->jsLayout = $this->updateListName($this->jsLayout);
        $this->jsLayout = $this->updateDefaultWishlist($this->jsLayout);
        $this->jsLayout = $this->updateBackUrl($this->jsLayout);
        $this->jsLayout = $this->updateSocialLinks($this->jsLayout);

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    private function updateSocialLinks(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $wishlist = $this->wishlistProvider->getWishlist();
            $wishlistName = $wishlist->getName();
            $wishlistImage = $this->getWishlistImage($wishlist);
            $wishlistSharingUrl = $this->getUrl(
                'wishlist/shared/index',
                [
                    'code' => $wishlist->getSharingCode(),
                    Networks::NETWORKS_URL_PARAMS => true
                ]
            );
            foreach ($this->networks->getNetworks() as $network) {
                $url = $network->getUrl();
                $url = str_replace("{url}", urlencode($wishlistSharingUrl), $url);
                $url = str_replace("{title}", urlencode($wishlistName), $url);
                $url = str_replace("{image}", urlencode($wishlistImage ?? ''), $url);
                $jsLayout['components']['ampagetitle']['config'][$network->getValue()] = $url;
            }
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateListName(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $jsLayout['components']['ampagetitle']['config']['listName'] = $this->wishlistProvider->getWishlist()
                ->getName();
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateTypes(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $jsLayout['components']['ampagetitle']['config']['types'] = $this->type->toArray();
            $jsLayout['components']['ampagetitle']['config']['selectedType'] = (int) $this->wishlistProvider
                ->getWishlist()->getType();
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateActions(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $jsLayout['components']['ampagetitle']['config']['deleteAjaxParam'] = $this->getPostHelper()->getPostData(
                $this->getUrl(PostHelper::DELETE_WISHLIST_ROUTE),
                ['wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId()],
                $this->getUrl(PostHelper::LIST_WISHLIST_ROUTE)
            );
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateItemsQty(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $jsLayout['components']['ampagetitle']['config']['itemsQty'] = $this->getLayout()
                ->getBlock('customer.wishlist')->getWishlistItems()->getSize();
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateDefaultWishlist(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $isDefault =  $this->wishlistManagement->isWishlistDefault(
                $this->wishlistProvider->getWishlist()->getWishlistId()
            );
            $jsLayout['components']['ampagetitle']['config']['defaultWishlist'] = $isDefault;
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateBackUrl(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampagetitle']['config'])) {
            $jsLayout['components']['ampagetitle']['config']['backUrl'] = $this->getUrl(
                PostHelper::LIST_WISHLIST_ROUTE,
                [
                    '_query' => [
                        $this->getPaginationHelper()->getPageVarName(
                            $this->wishlistProvider->getWishlist()->getType()
                        ) => 1
                    ]
                ]
            );
        }

        return $jsLayout;
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[AbstractPostBlock::POST_HELPER_KEY];
    }

    public function getPaginationHelper(): Pagination
    {
        return $this->_data['pagination_helper'];
    }

    /**
     * @param WishlistInterface $wishlist
     * @return false|mixed|null
     */
    private function getWishlistImage(WishlistInterface $wishlist)
    {
        $images = $this->wishlistTab->getItemImages($wishlist);

        return !empty($images) ? reset($images) : null;
    }
}
