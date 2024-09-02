<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Item;

use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\Action\Context;
use Amasty\MWishlist\Traits\ComponentProvider;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;

class Remove extends UpdateAction
{
    use ComponentProvider;

    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        WishlistItemFactory $wishlistItemFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * @return array
     */
    protected function action(): array
    {
        $resultData = [];

        $id = (int) $this->getContext()->getRequest()->getParam('item');

        $item = $this->wishlistItemFactory->create()->load($id);
        if (!$item->getId()) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('We can\'t find item.'));
            return $resultData;
        }

        $wishlist = $this->wishlistProvider->getWishlist((int) $item->getWishlistId());
        if (!$wishlist) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('Something is wrong.'));
            return $resultData;
        }

        try {
            $item->delete();
            $wishlist->save();
            $this->getContext()->getMessageManager()->addComplexSuccessMessage(
                'removeWishlistItemSuccessMessage',
                [
                    'product_name' => $item->getProduct()->getName(),
                ]
            );
        } catch (LocalizedException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(
                __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage())
            );
        } catch (Exception $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(
                __('We can\'t delete the item from the Wish List right now.')
            );
        }

        return array_merge(
            $resultData,
            ['components' => $this->getComponentData($this->wishlistProvider->getWishlist())]
        );
    }
}
