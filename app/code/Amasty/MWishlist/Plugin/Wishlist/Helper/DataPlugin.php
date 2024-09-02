<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Wishlist\Helper;

use Amasty\MWishlist\Model\ConfigProvider;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;

class DataPlugin
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        UrlInterface $urlBuilder,
        Escaper $escaper,
        JsonSerializer $jsonSerializer,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Data $subject
     * @param string $result
     * @return string
     */
    public function afterGetAddParams(Data $subject, string $result): string
    {
        return $this->modifyAction($result, PostHelper::ADD_ITEM_ROUTE);
    }

    /**
     * @param Data $subject
     * @param string $result
     * @return string
     */
    public function afterGetMoveFromCartParams(Data $subject, string $result): string
    {
        return $this->modifyAction($result, PostHelper::FROM_CART_ITEM_ROUTE);
    }

    /**
     * @param Data $subject
     * @param string $result
     * @param Product|Item|string $item
     * @return string
     */
    public function afterGetAddToCartUrl(Data $subject, string $result, $item): string
    {
        if ($this->configProvider->isEnabled()) {
            $params = [
                'item' => is_string($item) ? $item : $item->getWishlistItemId(),
            ];
            if ($item instanceof Item) {
                $params['qty'] = $item->getQty();
                $params['redirect'] = $this->urlBuilder->getUrl(
                    PostHelper::VIEW_WISHLIST_ROUTE,
                    ['wishlist_id' => $item->getWishlistId()]
                );
            }

            $result = $this->getUrlStore($item)->getUrl(PostHelper::IN_CART_ITEM_ROUTE, $params);
        }

        return $result;
    }

    /**
     * @param string $postData
     * @param string $newActionRoute
     * @return string
     */
    private function modifyAction(string $postData, string $newActionRoute): string
    {
        if ($this->configProvider->isEnabled()) {
            $postData = $this->jsonSerializer->unserialize($postData);

            $postData['action'] = $this->escaper->escapeUrl(
                $this->urlBuilder->getUrl($newActionRoute)
            );

            $postData = $this->jsonSerializer->serialize($postData);
        }

        return $postData;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param Product|Item|String $item
     * @return StoreInterface|Store
     */
    protected function getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }

        return $this->storeManager->getStore($storeId);
    }
}
