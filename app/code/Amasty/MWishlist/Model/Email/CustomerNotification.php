<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Email;

use Amasty\MWishlist\Block\Email\PriceAlert;
use Amasty\MWishlist\Model\ConfigProvider;
use Amasty\MWishlist\Model\ResourceModel\UnsubscribePriceAlerts;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection as ItemCollection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;

class CustomerNotification
{
    public const UNSUBSCRIBE_URL = 'mwishlist/unsubscribe/priceAlerts';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var WishlistCollectionFactory
     */
    private $wishlistCollectionFactory;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var UnsubscribePriceAlerts
     */
    private $unsubscribePriceAlerts;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ItemCollection
     */
    private $itemCollection;

    /**
     * @var PriceAlert
     */
    private $alertBlock;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Emulation
     */
    private $appEmulation;

    public function __construct(
        ConfigProvider $configProvider,
        WishlistCollectionFactory $wishlistCollectionFactory,
        TransportBuilder $transportBuilder,
        UnsubscribePriceAlerts $unsubscribePriceAlerts,
        UrlInterface $urlBuilder,
        CustomerCollectionFactory $customerCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ItemCollection $itemCollection,
        PriceAlert $alertBlock,
        State $appState,
        Emulation $appEmulation
    ) {
        $this->configProvider = $configProvider;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->unsubscribePriceAlerts = $unsubscribePriceAlerts;
        $this->urlBuilder = $urlBuilder;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->productCollection = $productCollectionFactory->create();
        $this->itemCollection = $itemCollection;
        $this->alertBlock = $alertBlock;
        $this->appState = $appState;
        $this->appEmulation = $appEmulation;
    }

    public function notify(): void
    {
        $productIds = $this->itemCollection->getProductIdsForAlert();
        if ($productIds) {
            $this->initProductCollection($productIds);
        } else {
            return;
        }

        if (!$this->productCollection->getSize()) {
            return;
        }

        foreach ($this->getNotifiedCustomers() as $customer) {
            $this->appEmulation->startEnvironmentEmulation(
                $customer->getStoreId(),
                Area::AREA_FRONTEND,
                true
            );

            if (!$this->configProvider->isPriceAlertsEnabled()) {
                return;
            }
            $this->notifyCustomer($customer);

            $this->appEmulation->stopEnvironmentEmulation();
        }
    }

    private function notifyCustomer(Customer $customer): void
    {
        $itemsToNotify = [];
        foreach ($this->getWishlistCollection((int) $customer->getId()) as $wishlist) {
            foreach ($wishlist->getItemCollection() as $item) {
                if ($product = $this->productCollection->getItemById($item->getProductId())) {
                    $product->setCustomerGroupId($customer->getGroupId());
                    $currentPrice = $product->getFinalPrice($item->getQty());
                    if ($currentPrice != $item->getProductPrice()) {
                        $itemsToNotify[$wishlist->getId()]['items'][] = $this->updateItemData($item, $product);
                        $itemsToNotify[$wishlist->getId()]['wishlist'] = $wishlist;
                    }
                }
            }
        }

        if ($itemsToNotify) {
            $this->alertBlock->setItemsToNotify($itemsToNotify);
            $itemsToNotify = $this->alertBlock->setData('area', Area::AREA_FRONTEND)->toHtml();
            $this->send($customer, $itemsToNotify);
        }
    }

    private function updateItemData(Item $item, Product $product): Item
    {
        $currentPrice = $product->getFinalPrice($item->getQty());
        $item->setPriceStatus($item->getProductPrice() <=> $currentPrice);
        $item->setProductPrice($currentPrice);
        $item->save();

        return $item;
    }

    private function getWishlistCollection(int $customerId): WishlistCollection
    {
        return $this->wishlistCollectionFactory->create()->filterByCustomerId($customerId);
    }

    private function initProductCollection(array $productIds): void
    {
        $this->productCollection->addIdFilter($productIds)
            ->addAttributeToSelect('*');
    }

    private function getNotifiedCustomers(): array
    {
        $customerIds = array_unique($this->wishlistCollectionFactory->create()->getColumnValues('customer_id'));
        $unsubscribedCustomer = $this->unsubscribePriceAlerts->getUserIds();
        $customerIds = array_diff($customerIds, $unsubscribedCustomer);

        $collection = null;
        if ($customerIds) {
            $collection = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('entity_id', $customerIds);
        }

        return $collection ? $collection->getItems() : [];
    }

    private function send(Customer $customer, string $wishlistItems): void
    {
        $sender = $this->configProvider->getEmailSender();
        $sendTo = $customer->getEmail();
        if ($sender && $sendTo) {
            $storeId = $customer->getStoreId();

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $this->configProvider->getEmailTemplate()
            )->setTemplateOptions(
                ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                [
                    'wishlist_items' => $wishlistItems,
                    'customer_name' => $customer->getName(),
                    'unsubscribe_url' => $this->urlBuilder->getUrl(self::UNSUBSCRIBE_URL)
                ]
            )->setFrom(
                $sender
            )->addTo(
                $sendTo
            )->getTransport();

            $transport->sendMessage();
        }
    }
}
