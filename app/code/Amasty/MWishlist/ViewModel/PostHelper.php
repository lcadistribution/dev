<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Model\Item;

class PostHelper implements ArgumentInterface
{
    public const LIST_WISHLIST_ROUTE = 'mwishlist/index/index';
    public const DELETE_WISHLIST_ROUTE = 'mwishlist/wishlist/delete';
    public const CREATE_WISHLIST_ROUTE = 'mwishlist/wishlist/create';
    public const VIEW_WISHLIST_ROUTE = 'mwishlist/wishlist/index';
    public const VALIDATE_WISHLIST_NAME_ROUTE = 'mwishlist/wishlist/validateWishlistName';
    public const SEND_WISHLIST = 'wishlist/index/send';
    public const UPDATE_WISHLIST_ROUTE = 'mwishlist/wishlist/update';

    public const CONFIGURE_ITEM_ROUTE = 'wishlist/index/configure';
    public const ADD_ITEM_ROUTE = 'mwishlist/item/add';
    public const IN_CART_ITEMS_ROUTE = 'wishlist/index/allcart';
    public const IN_CART_ITEM_ROUTE = 'mwishlist/item/toCart';
    public const MOVE_ITEMS_ROUTE = 'mwishlist/item/move';
    public const COPY_ITEMS_ROUTE = 'mwishlist/item/copy';
    public const REMOVE_ITEMS_ROUTE = 'mwishlist/item/remove';
    public const FROM_CART_ITEM_ROUTE = 'mwishlist/item/fromCart';

    public const PRODUCT_SEARCH_ROUTE = 'mwishlist/product/search';

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        JsonSerializer $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $action
     * @param array $data
     * @param string|null $redirect
     * @return string
     */
    public function getPostData(string $action, array $data, ?string $redirect = null): string
    {
        $data = ['action' => $action, 'data' => $data];
        if ($redirect !== null) {
            $data['redirect'] = $redirect;
        }

        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Get cart URL parameters
     *
     * @param string|Product|Item $item
     * @return array
     */
    public function getCartItemParams($item): array
    {
        $params = [
            'item' => is_string($item) ? $item : $item->getWishlistItemId()
        ];
        if ($item instanceof Item) {
            $params['qty'] = $item->getQty();
        }

        return $params;
    }

    /**
     * @return ModuleManager
     */
    public function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }
}
