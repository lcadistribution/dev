<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api\Data;

interface WishlistSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\MWishlist\Api\Data\WishlistInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
