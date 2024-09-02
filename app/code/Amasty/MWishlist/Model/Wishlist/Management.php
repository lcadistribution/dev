<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\Model\Wishlist;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Psr\Log\LoggerInterface;

class Management
{
    /**
     * @var WishlistHelper
     */
    private $wishlistHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var array
     */
    private $defaultWishlistsByCustomer = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Registry $registry,
        WishlistHelper $wishlistHelper,
        WishlistRepositoryInterface $wishlistRepository,
        LoggerInterface $logger
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->registry = $registry;
        $this->wishlistRepository = $wishlistRepository;
        $this->logger = $logger;
    }

    /**
     * @param int|null $customerId
     * @return WishlistCollection
     */
    public function getCustomerWishlists($customerId = null)
    {
        if ($customerId === null) {
            $customerId = $this->getCurrentCustomerId();
        }

        $wishlistsByCustomer = $this->registry->registry('mwishlists_by_customer');

        if (!isset($wishlistsByCustomer[$customerId])) {
            $wishlists = $this->wishlistRepository->getWishlistsByCustomerId((int) $customerId);
            if ($customerId && empty($wishlists)) {
                $wishlists->addItem($this->addWishlist($customerId));
            }
            $wishlistsByCustomer[$customerId] = $wishlists;
            $this->registry->register('mwishlists_by_customer', $wishlistsByCustomer);
        }

        return $wishlistsByCustomer[$customerId];
    }

    /**
     * @param $customerId
     * @param string $wishlistName
     * @return bool
     */
    public function isWishlistExist(string $wishlistName, $customerId = null)
    {
        if ($customerId === null) {
            $customerId = $this->getCurrentCustomerId();
        }

        return $this->wishlistRepository->isWishlistExist((int) $customerId, $wishlistName);
    }

    /**
     * @param int|null $customerId
     * @return WishlistCollection
     */
    public function getWishlistList($customerId = null)
    {
        if ($customerId === null) {
            $customerId = (int) $this->getCurrentCustomerId();
        }

        return $this->wishlistRepository->getWishlistsByCustomerId($customerId, Type::WISH);
    }

    /**
     * @param int|null $customerId
     * @return WishlistCollection
     */
    public function getRequisitionList($customerId = null)
    {
        if ($customerId === null) {
            $customerId = (int) $this->getCurrentCustomerId();
        }

        return $this->wishlistRepository->getWishlistsByCustomerId($customerId, Type::REQUISITION);
    }

    /**
     * @param int $wishlistId
     * @param int|null $customerId
     * @return bool
     */
    public function isWishlistDefault(int $wishlistId, ?int $customerId = null): bool
    {
        try {
            $result = $this->getDefaultWishlist($customerId)->getWishlistId() === $wishlistId;
        } catch (LocalizedException $e) {
            $result = false;
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param null|int $customerId
     * @return WishlistInterface
     * @throws NoSuchEntityException
     */
    public function getDefaultWishlist(?int $customerId): WishlistInterface
    {
        if ($customerId === null) {
            $customerId = (int) $this->getCurrentCustomerId();
        }

        if (!isset($this->defaultWishlistsByCustomer[$customerId])) {
            $this->defaultWishlistsByCustomer[$customerId] = $this->wishlistRepository->getByCustomerId($customerId);
        }

        return $this->defaultWishlistsByCustomer[$customerId];
    }

    /**
     * @return int|null
     */
    protected function getCurrentCustomerId()
    {
        $customerId = null;
        if ($customer = $this->wishlistHelper->getCustomer()) {
            $customerId = (int) $customer->getId();
        }

        return $customerId;
    }

    /**
     * Create new wishlist
     *
     * @param int $customerId
     * @return Wishlist
     */
    protected function addWishlist($customerId)
    {
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistRepository->create();
        $wishlist->setCustomerId($customerId);
        $wishlist->generateSharingCode();
        $this->wishlistRepository->save($wishlist);

        return $wishlist;
    }
}
