<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Controller\Cart;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\ConfigureGridView\Helper\Data;

/**
 * Class AddToCart
 * @package Mageplaza\ConfigureGridView\Controller\Cart
 */
class AddToCart extends Action
{
    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var StockStateInterface
     */
    protected $_stockState;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * AddToCart constructor.
     *
     * @param CheckoutCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param StockStateInterface $stockState
     * @param FormKey $formKey
     * @param Session $checkoutSession
     * @param Context $context
     */
    public function __construct(
        CheckoutCart $cart,
        ProductRepositoryInterface $productRepository,
        StockStateInterface $stockState,
        FormKey $formKey,
        Session $checkoutSession,
        Context $context
    ) {
        $this->cart              = $cart;
        $this->productRepository = $productRepository;
        $this->formKey           = $formKey;
        $this->checkoutSession   = $checkoutSession;
        $this->_stockState       = $stockState;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $status  = false;
            $items   = $this->getRequest()->getParam('mpcpgvItems')
                ? Data::jsonDecode($this->getRequest()->getParam('mpcpgvItems'))
                : Data::jsonDecode($this->getRequest()->getParam('items'));
            $storeId = $this->getRequest()->getParam('storeId');
            $options = $this->getRequest()->getParam('options');
            if (empty($items)) {
                $this->messageManager->addErrorMessage(__('No products selected'));
            } else {
                foreach ($items as $item) {
                    /** @var Product $product */
                    try {
                        $product = $this->productRepository->getById(
                            $item['id'],
                            false,
                            $storeId,
                            true
                        );
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(
                            __('Error when adding the product to cart')
                        );
                        continue;
                    }

                    $productStock = $product->getExtensionAttributes()->getStockItem();
                    $isBackorders = $productStock->getBackorders();

                    $stock = $this->_stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                    if ($stock < $item['qty'] && !$isBackorders) {
                        $this->messageManager->addErrorMessage(
                            __('We can\'t add %1 to your shopping cart right now.', $product->getName())
                        );
                        continue;
                    }
                    $params = [
                        'form_key' => $this->formKey->getFormKey(),
                        'product'  => $item['configurableProductId'],
                        'item'     => $item['configurableProductId'],
                        'qty'      => $item['qty']
                    ];

                    foreach ($item['attributes'] as $attribute) {
                        $attributeId                             = $attribute['attributeId'];
                        $attributeValue                          = $attribute['attributeValue'];
                        $params['super_attribute'][$attributeId] = $attributeValue;
                    }

                    if (!empty($options)) {
                        $params['options'] = $options;
                    }

                    try {
                        $product = $this->productRepository->getById(
                            $item['configurableProductId'],
                            false,
                            $storeId,
                            true
                        );
                        $this->cart->addProduct($product, $params);
                        $status = true;
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        $this->cart->removeItem($item['id']);
                    }
                }
                if ($status) {
                    $this->messageManager->addSuccessMessage(__('Add to cart successfully'));
                } else {
                    return $this->getResponse()->representJson(Data::jsonEncode(['status' => $status]));
                }

                $items = $this->checkoutSession->getQuote()->getItemsCollection();

                $keys        = [];
                $removeChild = [];

                foreach ($items as $key => &$item) {
                    /** @var Item $item */
                    if ($item->getHasError()) {
                        if ($item->getItemId()) {
                            $item->setQty($item->getQty() - $item->getQtyToAdd());
                            $item->unsQtyToAdd();
                        } else {
                            if ($item->getChildren()) {
                                foreach ($item->getChildren() as $child) {
                                    $removeChild[$child->getProductId()] = $child->getQtyToAdd();
                                }
                            }
                            $keys[] = $key;
                        }
                    }
                }
                foreach ($keys as $key) {
                    $items->removeItemByKey($key);
                }

                foreach ($items as $key => $item) {
                    if (isset($removeChild[$item->getProductId()])) {
                        $qtyToAdd = $removeChild[$item->getProductId()];
                        if ($item->getQtyToAdd() === $qtyToAdd) {
                            $items->removeItemByKey($key);
                        } else {
                            $item->setQty($item->getQty() - $qtyToAdd);
                            $item->unsQtyToAdd();
                        }
                    }
                }
                $this->cart->save();
            }

            return $this->getResponse()->representJson(Data::jsonEncode(['status' => $status]));
        }

        return $this->getResponse()->representJson('{}');
    }
}
