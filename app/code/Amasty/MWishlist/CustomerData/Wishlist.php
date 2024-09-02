<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\CustomerData;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Model\ConfigProvider;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\Model\Wishlist as WishlistModel;
use Amasty\MWishlist\Model\Wishlist\Management as WishlistManagement;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\UrlInterface;

class Wishlist implements SectionSourceInterface
{
    /**
     * @var WishlistManagement
     */
    private $wishlistManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ConfigProvider $configProvider,
        WishlistManagement $wishlistManagement,
        UrlInterface $urlBuilder
    ) {
        $this->wishlistManagement = $wishlistManagement;
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $sectionData = [];

        if ($this->configProvider->isEnabled()) {
            $sectionData['wishlist_list'] = $this->getWishlistList();
            $sectionData['recently_list'] = $this->getRecentlyList();
        }

        return $sectionData;
    }

    /**
     * @return array
     */
    protected function getWishlistList(): array
    {
        return [
            Type::WISH => $this->convertToArray($this->wishlistManagement->getWishlistList()),
            Type::REQUISITION => $this->convertToArray($this->wishlistManagement->getRequisitionList())
        ];
    }

    /**
     * @return array
     */
    protected function getRecentlyList(): array
    {
        return $this->convertToArray($this->wishlistManagement->getCustomerWishlists(), true);
    }

    /**
     * @param WishlistCollection $list
     * @param bool $withUrl
     * @return array
     */
    private function convertToArray(WishlistCollection $list, $withUrl = false): array
    {
        $wishlistData = [];

        /** @var WishlistInterface|WishlistModel $wishlist */
        foreach ($list as $wishlist) {
            $wishlistDataTemp = [
                WishlistInterface::WISHLIST_ID => $wishlist->getWishlistId(),
                WishlistInterface::NAME => $wishlist->getName(),
                'items_count' => $wishlist->getItemCollection()->getSize()
            ];

            if ($withUrl) {
                $wishlistDataTemp['url'] = $this->urlBuilder->getUrl(PostHelper::VIEW_WISHLIST_ROUTE, [
                    'wishlist_id' => $wishlist->getWishlistId()
                ]);
            }

            $wishlistData[] = $wishlistDataTemp;
        }

        return $wishlistData;
    }
}
