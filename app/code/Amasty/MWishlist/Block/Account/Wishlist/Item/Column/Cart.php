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
use Magento\Catalog\Model\Product;
use Magento\Wishlist\Block\AbstractBlock;
use Magento\Wishlist\Model\Item;

class Cart extends AbstractBlock
{
    /**
     * @param Product|Item|string $item
     * @return string
     */
    public function getItemAddCartParams($item): string
    {
        return $this->getPostHelper()->getPostData(
            $this->getUrl(PostHelper::IN_CART_ITEM_ROUTE),
            array_merge(
                $this->getPostHelper()->getCartItemParams($item),
                [
                    'wishlist_id' => $item->getWishlistId(),
                    UpdateAction::BLOCK_PARAM => 'customer.wishlist',
                    UpdateAction::COMPONENT_PARAM => 'itemsQty'
                ]
            )
        );
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[AbstractPostBlock::POST_HELPER_KEY];
    }

    /**
     * @return string
     */
    public function getPostAttribute(): string
    {
        if ($this->getPostHelper()->getModuleManager()->isEnabled('Amasty_Cart')
            && $this->_scopeConfig->getValue('amasty_cart/general/enable')
        ) {
            $attribute = 'data-post';
        } else {
            $attribute = 'data-mwishlist-ajax';
        }

        return $attribute;
    }

    /**
     * @param Item $item
     * @return float
     */
    public function getAddToCartQty(Item $item)
    {
        $qty = $item->getQty();
        return $qty ? $qty : 1;
    }

    /**
     * @return Product
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}
