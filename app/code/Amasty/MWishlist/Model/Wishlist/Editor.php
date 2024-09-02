<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Wishlist;

use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Model\Wishlist;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Editor
{
    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var Management
     */
    private $management;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        WishlistRepositoryInterface $wishlistRepository,
        Management $management,
        CustomerSession $customerSession
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->management = $management;
        $this->customerSession = $customerSession;
    }

    /**
     * Edit wishlist
     *
     * @param int $customerId
     * @param string $wishlistName
     * @param int $wishlistId
     * @param array $additionalData
     * @return Wishlist
     * @throws LocalizedException
     */
    public function edit($customerId, $wishlistName, $wishlistId = null, array $additionalData = [])
    {
        if (!$customerId) {
            throw new LocalizedException(__('Sign in to edit wish lists.'));
        }

        if ($wishlistId) {
            try {
                $wishlist = $this->wishlistRepository->getById($wishlistId);
            } catch (NoSuchEntityException $e) {
                $wishlist = $this->wishlistRepository->create();
            }
            if ($wishlist->getCustomerId() !== $this->customerSession->getCustomerId()) {
                throw new LocalizedException(
                    __('The wish list is not assigned to your account and can\'t be edited.')
                );
            }
        } else {
            if (empty($wishlistName)) {
                throw new LocalizedException(__('Provide the wish list name.'));
            }

            if ($this->management->isWishlistExist($wishlistName, $customerId)) {
                throw new LocalizedException(
                    __('Wish list "%1" already exists.', $wishlistName)
                );
            }
            /** @var Wishlist $wishlist */
            $wishlist = $this->wishlistRepository->create();

            $wishlist->setCustomerId($customerId);
            $wishlist->generateSharingCode();
        }

        $wishlist->setName($wishlistName)
            ->addData($additionalData)
            ->setVisibility(false)
            ->save();

        return $wishlist;
    }
}
