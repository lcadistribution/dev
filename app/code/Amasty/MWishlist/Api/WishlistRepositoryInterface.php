<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api;

/**
 * @api
 */
interface WishlistRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\MWishlist\Api\Data\WishlistInterface $wishlist
     * @param int|null $customerId
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function save(\Amasty\MWishlist\Api\Data\WishlistInterface $wishlist, ?int $customerId = null);

    /**
     * Get by id
     *
     * @param int $id
     * @param int|null $customerId
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, ?int $customerId = null);

    /**
     * Get by Customer Id
     *
     * @param int $customerId
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId($customerId);

    /**
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function create();

    /**
     * Delete
     *
     * @param \Amasty\MWishlist\Api\Data\WishlistInterface $wishlist
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\MWishlist\Api\Data\WishlistInterface $wishlist);

    /**
     * Delete by id
     *
     * @param int $wishlistId
     * @param int|null $customerId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $wishlistId, ?int $customerId = null): bool;

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $customerId
     * @param null $type
     * @return \Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection
     */
    public function getWishlistsByCustomerId(int $customerId, $type = null);

    /**
     * @param int $customerId
     * @param string $wishlistName
     * @return bool
     */
    public function isWishlistExist(int $customerId, string $wishlistName);

    /**
     * @param int $wishlistId
     * @param \Amasty\MWishlist\Api\Data\WishlistItemDataInterface[] $wishlistItems
     * @param int $customerId
     * @return bool
     */
    public function addProductToWishlist(int $wishlistId, array $wishlistItems, int $customerId): bool;
}
