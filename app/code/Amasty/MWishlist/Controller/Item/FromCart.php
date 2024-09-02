<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Item;

use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAbstractIndex;
use Psr\Log\LoggerInterface;

class FromCart extends WishlistAbstractIndex implements AbstractIndexInterface
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        CheckoutCart $cart,
        CartHelper $cartHelper,
        Escaper $escaper,
        Validator $formKeyValidator,
        LoggerInterface $logger
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->cart = $cart;
        $this->cartHelper = $cartHelper;
        $this->escaper = $escaper;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([]);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage('Something wrong');
            return $resultJson;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a wishlist.'));
            return $resultJson;
        }

        try {
            $itemId = (int) $this->getRequest()->getParam('item');
            $item = $this->cart->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new LocalizedException(
                    __("The cart item doesn't exist.")
                );
            }

            $productId = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $wishlist->addNewItem($productId, $buyRequest);

            $this->cart->getQuote()->removeItem($itemId);
            $this->cart->save();

            $wishlist->save();

            $this->messageManager->addComplexSuccessMessage(
                'moveFromCartItemMWishlist',
                [
                    'product_name' => $item->getProduct()->getName(),
                    'wishlist_url' => $this->_url->getUrl(
                        PostHelper::VIEW_WISHLIST_ROUTE,
                        [
                            'wishlist_id' => $wishlist->getWishlistId()
                        ]
                    ),
                    'wishlist_name' => $wishlist->getName()
                ]
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('We can\'t move the item to the wish list.'));
        }

        return $resultJson;
    }
}
