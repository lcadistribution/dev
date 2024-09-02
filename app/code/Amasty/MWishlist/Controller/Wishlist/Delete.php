<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Wishlist;

use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\Action\Context;
use Amasty\MWishlist\Model\Wishlist\Management;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends UpdateAction
{
    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var Management
     */
    private $wishlistManagement;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        Management $wishlistManagement,
        WishlistRepositoryInterface $wishlistRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistRepository = $wishlistRepository;
        $this->wishlistManagement = $wishlistManagement;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * @return array
     */
    protected function action(): array
    {
        try {
            $wishlist = $this->wishlistProvider->getWishlist();
            if (!$this->wishlistManagement->isWishlistDefault($wishlist->getWishlistId())) {
                $this->wishlistRepository->delete($wishlist);
            } else {
                $this->getContext()->getMessageManager()->addErrorMessage(__(
                    'We can\'t delete the default Wish List.'
                ));
                return [];
            }
        } catch (NoSuchEntityException | CouldNotDeleteException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(__(
                'We can\'t delete the Wish List right now: %1.',
                $e->getMessage()
            ));
            return [];
        } catch (Exception $e) {
            $this->getContext()->getLogger()->error($e->getMessage());
            $this->getContext()->getMessageManager()->addErrorMessage(
                __('We can\'t add the item to Wish List right now.')
            );
            return [];
        }

        $this->getContext()->getMessageManager()->addSuccessMessage(
            __('"%1" wish list has been deleted.', $wishlist->getName())
        );

        return [];
    }
}
