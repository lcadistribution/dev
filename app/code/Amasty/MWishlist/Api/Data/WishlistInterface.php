<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Api\Data;

interface WishlistInterface
{
    public const MAIN_TABLE = 'wishlist';

    public const WISHLIST_ID = 'wishlist_id';
    public const CUSTOMER_ID = 'customer_id';
    public const SHARED = 'shared';
    public const SHARING_CODE = 'sharing_code';
    public const UPDATED_AT = 'updated_at';
    public const NAME = 'name';
    public const TYPE = 'type';

    /**
     * @return int
     */
    public function getWishlistId();

    /**
     * @param int $wishlistId
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setWishlistId($wishlistId);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getShared();

    /**
     * @param int $shared
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setShared($shared);

    /**
     * @return string
     */
    public function getSharingCode();

    /**
     * @param string $sharingCode
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setSharingCode(string $sharingCode);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function setType($type);

    /**
     * @return \Amasty\MWishlist\Api\Data\WishlistItemInterface[]
     */
    public function getWishlistItems(): array;
}
