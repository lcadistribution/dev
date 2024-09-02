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
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Controller\Cart;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class AddEach
 * @package Mageplaza\TableCategoryView\Controller\Cart
 */
class AddEach extends Action
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
     * AddToCart constructor.
     *
     * @param CheckoutCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param FormKey $formKey
     * @param Context $context
     */
    public function __construct(
        CheckoutCart $cart,
        ProductRepositoryInterface $productRepository,
        FormKey $formKey,
        Context $context
    ) {
        $this->cart              = $cart;
        $this->productRepository = $productRepository;
        $this->formKey           = $formKey;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $qty     = $this->getRequest()->getParam('qty');
            $storeId = $this->getRequest()->getParam('storeId');
            $params  = [];
            parse_str($this->getRequest()->getParam('product_params'), $params);
            $productId = empty($this->getRequest()->getParam('productId'))
                ? $params['product'] : $this->getRequest()->getParam('productId');
            if (empty($productId)) {
                $this->messageManager->addErrorMessage(__('No products selected'));

                $this->getResponse()->representJson(Data::jsonEncode([
                    'status' => 0,
                    'notify' => ''
                ]));
            }

            $response = $this->addItemToCart($productId, $qty, $storeId, $params);

            return $this->getResponse()->representJson(Data::jsonEncode($response));
        }

        return $this->getResponse()->representJson('{}');
    }

    /**
     * @param $productID
     * @param $qty
     * @param $storeId
     * @param array $options
     *
     * @return array
     */
    public function addItemToCart($productID, $qty, $storeId, $options = [])
    {
        /** @var Product $product */
        try {
            $product = $this->productRepository->getById(
                $productID,
                false,
                $storeId,
                true
            );
        } catch (Exception $e) {
            return [
                'status' => 0,
                'notify' => $e->getMessage()
            ];
        }

        if (!is_numeric($qty) || $qty < 1) {
            $qty = 1;
        }

        $params = ['qty' => $qty];
        $params = array_merge($params, $options);

        try {
            $this->cart->addProduct($product, $params);
        } catch (Exception $e) {
            $this->cart->removeItem($productID);

            return [
                'status' => 0,
                'notify' => $e->getMessage()
            ];
        }

        $this->cart->save();
        $this->messageManager->addSuccessMessage(__(
            'You added %1 to your shopping cart.',
            $product->getName()
        ));

        return [
            'status' => 1,
            'notify' => ''
        ];
    }
}
