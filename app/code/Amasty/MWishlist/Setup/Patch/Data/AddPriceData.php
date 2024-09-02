<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Setup\Patch\Data;

use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\GetWishlistItem;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;

class AddPriceData implements DataPatchInterface
{
    private const PRODUCT_PRICE = 'product_price';

    /**
     * @var WishlistItemCollection
     */
    private $wishlistItemCollection;

    /**
     * @var WishlistCollection
     */
    private $wishlistCollection;

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    /**
     * @var GetWishlistItem
     */
    private $getWishlistItem;

    public function __construct(
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        WishlistCollectionFactory $wishlistCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        GetWishlistItem $getWishlistItem
    ) {
        $this->wishlistItemCollection = $wishlistItemCollectionFactory->create();
        $this->wishlistCollection = $wishlistCollectionFactory->create();
        $this->customerCollection = $customerCollectionFactory->create();
        $this->getWishlistItem = $getWishlistItem;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        if ($this->isApplied()) {
            return;
        }

        $customerIds = array_unique($this->wishlistCollection->getColumnValues('customer_id'));
        $this->customerCollection->addAttributeToFilter('entity_id', $customerIds);
        foreach ($this->wishlistCollection as $wishlist) {
            if ($wishlist->getItemsCount()) {
                $customerGroupId = $this->customerCollection->getItemById($wishlist->getCustomerId())->getGroupId();
                foreach ($wishlist->getItemCollection() as $item) {
                    $price = $item->getProduct()->setCustomerGroupId($customerGroupId)->getFinalPrice($item->getQty());
                    $item->setProductPrice($price);
                    $item->save();
                }
            }
        }
    }

    private function isApplied(): bool
    {
        $wishlistItem = $this->getWishlistItem->execute();

        return $wishlistItem && $wishlistItem[self::PRODUCT_PRICE];
    }
}
