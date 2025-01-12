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
use Amasty\MWishlist\Model\Wishlist;
use Amasty\MWishlist\Traits\ComponentProvider;
use Amasty\MWishlist\ViewModel\PostHelper;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends UpdateAction
{
    use ComponentProvider;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        WishlistProviderInterface $wishlistProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * @return array
     */
    protected function action(): array
    {
        $resultData = [];

        $requestParams = $this->getContext()->getRequest()->getParams();

        $productId = isset($requestParams['product']) ? (int) $requestParams['product'] : null;
        if (!$productId) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('We can\'t specify a product.'));
            return $resultData;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('We can\'t specify a product.'));
            return $resultData;
        }

        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistProvider->getWishlist();
        try {
            $buyRequest = new DataObject($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                $this->getContext()->getMessageManager()->addErrorMessage(__(
                    'We can\'t add the item to Wish List right now: %1.',
                    $result
                ));
                return $resultData;
            }

            if ($wishlist->isObjectNew()) {
                $wishlist->save();
            }

            $this->getContext()->getEventManager()->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
            );
        } catch (LocalizedException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(__(
                'We can\'t add the item to Wish List right now: %1.',
                $e->getMessage()
            ));
            return $resultData;
        } catch (Exception $e) {
            $this->getContext()->getLogger()->error($e->getMessage());
            $this->getContext()->getMessageManager()->addErrorMessage(
                __('We can\'t add the item to Wish List right now.')
            );
            return $resultData;
        }

        $this->getContext()->getMessageManager()->addComplexSuccessMessage(
            'addItemMWishlist',
            [
                'product_name' => $product->getName(),
                'wishlist_url' => $this->getContext()->getUrlBuilder()->getUrl(
                    PostHelper::VIEW_WISHLIST_ROUTE,
                    [
                        'wishlist_id' => $wishlist->getWishlistId()
                    ]
                ),
                'wishlist_name' => $wishlist->getName()
            ]
        );

        return array_merge(
            $resultData,
            ['components' => $this->getComponentData($this->wishlistProvider->getWishlist())]
        );
    }
}
