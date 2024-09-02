<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var WishlistInterface
     */
    private $wishlist;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    public function __construct(
        WishlistRepositoryInterface $wishlistRepository,
        CustomerSession $customerSession,
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->wishlistRepository = $wishlistRepository;
    }

    /**
     * @inheritdoc
     */
    public function getWishlist(?int $wishlistId = null)
    {
        if ($this->wishlist) {
            return $this->wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = $this->request->getParam('wishlist_id');
            }
            $customerId = (int) $this->customerSession->getCustomerId();

            if (!$wishlistId && !$customerId) {
                return $this->wishlistRepository->create();
            }

            if ($wishlistId) {
                $wishlist = $this->wishlistRepository->getById($wishlistId);
            } elseif ($customerId) {
                $wishlist = $this->wishlistRepository->getByCustomerId($customerId);
            }

            $this->validateAccess($wishlist, $customerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t create the Wish List right now.'));
            return false;
        }

        $this->wishlist = $wishlist;

        return $wishlist;
    }

    /**
     * @param WishlistInterface $wishlist
     * @param int $currentCustomerId
     * @throws NoSuchEntityException
     */
    private function validateAccess(WishlistInterface $wishlist, int $currentCustomerId)
    {
        if ($wishlist->getCustomerId() != $currentCustomerId) {
            throw new NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
    }
}
