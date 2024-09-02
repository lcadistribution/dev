<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Repository;

use Amasty\MWishlist\Api\WishlistItemRepositoryInterface;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist\RemoveProductsFromWishlist as RemoveProductsFromWishlistModel;

class WishlistItemRepository implements WishlistItemRepositoryInterface
{
    /**
     * @var WishlistRepository
     */
    private $wishlistRepository;

    /**
     * @var ItemCollectionFactory
     */
    private $wishlistItemCollectionFactory;

    /**
     * @var RemoveProductsFromWishlistModel
     */
    private $removeProductsFromWishlist;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepostitory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    public function __construct(
        ItemCollectionFactory $wishlistItemCollectionFactory,
        WishlistRepository $wishlistRepository,
        RemoveProductsFromWishlistModel $removeProductsFromWishlist,
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory
    ) {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->wishlistRepository = $wishlistRepository;
        $this->removeProductsFromWishlist = $removeProductsFromWishlist;
        $this->cartRepostitory = $cartRepository;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @throws \Exception
     */
    public function deleteItemFromWishlist(int $wishlistId, int $itemId, int $customerId): bool
    {
        $wishlist = $this->wishlistRepository->getById($wishlistId, $customerId);
        $output = $this->removeProductsFromWishlist->execute($wishlist, [$itemId]);
        $errors = [];
        if ($output->getErrors()) {
            foreach ($output->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            throw new LocalizedException(__('Error while removing items from wishlist: %1', $errors));
        }

        return true;
    }

    /**
     * @throws LocalizedException
     * @throws \Exception
     */
    public function addProductFromCartToWishlist(int $wishlistId, int $cartItemId, int $customerId): bool
    {
        $wishlist = $this->wishlistRepository->getById($wishlistId, $customerId);
        $cart = $this->cartRepostitory->getForCustomer($customerId);
        $item = $cart->getItemById($cartItemId);

        if (!$item) {
            throw new LocalizedException(
                __("The cart item doesn't exist.")
            );
        }

        $wishlist->addNewItem($item->getProductId(), $item->getBuyRequest());
        $cart->removeItem($item->getId());
        $cart->collectTotals();
        $cart->save();

        return true;
    }

    /**
     * @throws LocalizedException
     * @throws \Exception
     */
    public function addProductToCartFromWishlist(int $wishlistId, int $itemId, int $customerId): bool
    {
        $wishlist = $this->wishlistRepository->getById($wishlistId, $customerId);
        $item = $wishlist->getItem($itemId);

        if (!$item) {
            throw new LocalizedException(
                __("The wishlist item doesn't exist.")
            );
        }

        $quote = $this->cartRepostitory->getActiveForCustomer($customerId);
        $cart = $this->quoteFactory->create()->loadActive($quote->getId());
        $cart->addProduct($item->getProduct(), $item->getProduct()->getQty());
        $cart->collectTotals()->save();

        $this->deleteItemFromWishlist($wishlistId, $itemId, $customerId);

        return true;
    }

    /**
     * @throws LocalizedException
     */
    public function getWishlistItem(int $itemId): Item
    {
        $wishlistItemFactory = $this->wishlistItemCollectionFactory->create();
        /* @var $item Item */
        $item = $wishlistItemFactory->getItemById($itemId);
        if (!$item) {
            throw new LocalizedException(__('Item with ID %1 not found', $itemId));
        }

        return $item;
    }
}
