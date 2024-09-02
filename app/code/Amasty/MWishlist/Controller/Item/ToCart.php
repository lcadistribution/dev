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
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\Model\Wishlist;
use Amasty\MWishlist\ViewModel\PostHelper;
use Exception;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Model\Item\OptionFactory as OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor as QuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection as OptionCollection;

class ToCart extends UpdateAction
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var QuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        ItemFactory $itemFactory,
        QuantityProcessor $quantityProcessor,
        OptionFactory $optionFactory,
        Cart $cart,
        ProductHelper $productHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->itemFactory = $itemFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->optionFactory = $optionFactory;
        $this->cart = $cart;
        $this->productHelper = $productHelper;
    }

    /**
     * @return array
     */
    protected function action(): array
    {
        $itemId = (int) $this->getContext()->getRequest()->getParam('item');
        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('Can\'t load item.'));
            return [];
        }
        /** @var Wishlist $wishlist */
        $wishlist = $this->wishlistProvider->getWishlist((int) $item->getWishlistId());
        if (!$wishlist) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('Can\'t load wishlist.'));
            return [];
        }

        // Set qty
        $qty = $this->getContext()->getRequest()->getParam('qty');
        $postQty = $this->getContext()->getRequest()->getPostValue('qty');
        if ($postQty !== null && $qty !== $postQty) {
            $qty = $postQty;
        }
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        try {
            /** @var OptionCollection $options */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                $this->getContext()->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );

            $item->mergeBuyRequest($buyRequest);
            $deleteFlag = false;
            if ($wishlist->getType() === Type::WISH) {
                $deleteFlag = true;
            }
            $item->addToCart($this->cart, $deleteFlag);
            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->getContext()->getEscaper()->escapeHtml($item->getProduct()->getName())
                );

                $this->getContext()->getMessageManager()->addSuccessMessage($message);
            }
        } catch (ProductException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('This product(s) is out of stock.'));
            return [];
        } catch (LocalizedException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage($e->getMessage());
            return [
                'backUrl' => $this->getContext()->getUrlBuilder()->getUrl(
                    PostHelper::CONFIGURE_ITEM_ROUTE,
                    [
                        'id' => $item->getId(),
                        'product_id' => $item->getProductId(),
                    ]
                )
            ];
        } catch (Exception $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(
                __('We can\'t add the item to the cart right now.')
            );
        }

        $result = [];

        if ($backUrl = $this->getContext()->getRequest()->getParam('redirect')) {
            $result['backUrl'] = $backUrl;
        }

        return $result;
    }
}
