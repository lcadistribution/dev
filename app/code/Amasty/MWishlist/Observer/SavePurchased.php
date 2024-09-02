<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Observer;

use Amasty\MWishlist\Model\ResourceModel\Purchased;
use Magento\Framework\Event\Observer;

class SavePurchased implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Purchased
     */
    private $purchased;

    public function __construct(
        Purchased $purchased
    ) {
        $this->purchased = $purchased;
    }

    public function execute(Observer $observer)
    {
        $fromWishlistItems = [];
        $addedItems = [];
        $cart = $observer->getCart();
        foreach ($cart->getItems() as $item) {
            if ($item->getIsFromWishlist() || in_array($item->getParentItemId(), $addedItems)) {
                $fromWishlistItems[] = [Purchased::QUOTE_ITEM_ID => $item->getId()];
                $addedItems[] = $item->getId();
            }
        }

        if ($fromWishlistItems) {
            $this->purchased->saveItems($fromWishlistItems);
        }
    }
}
