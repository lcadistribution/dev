<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Wishlist\Model;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Model\Source\Type;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item;
use Psr\Log\LoggerInterface;

class ItemPlugin
{
    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession,
        WishlistRepositoryInterface $wishlistRepository,
        LoggerInterface $logger
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Item $subject
     * @param Cart $cart
     * @param bool $delete
     * @return array
     */
    public function beforeAddToCart(Item $subject, Cart $cart, bool $delete = false): array
    {
        try {
            if ($this->getWishlist((int)$subject->getWishlistId())->getType() == Type::REQUISITION) {
                $delete = false;
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }

        $subject->getProduct()->setIsFromWishlist(true);

        return [$cart, $delete];
    }

    /**
     * @param int $wishlistId
     * @return WishlistInterface
     * @throws NoSuchEntityException
     */
    private function getWishlist(int $wishlistId): WishlistInterface
    {
        return $this->wishlistRepository->getById($wishlistId);
    }

    public function beforeSave(Item $subject): void
    {
        $groupId = $this->customerSession->getCustomerGroupId();
        $price = $subject->getProduct()->setCustomerGroupId($groupId)->getFinalPrice($subject->getQty());
        $subject->setProductPrice($price);
    }
}
