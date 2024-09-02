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
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\TableCategoryView\Helper\Data as MpHelper;

/**
 * Class AddEach
 * @package Mageplaza\TableCategoryView\Controller\Cart
 */
class AddAllToCart extends Action
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
     * @var ProductFactory
     */
    protected $productLoader;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var MpHelper
     */
    protected $_helperData;

    /**
     * AddToCart constructor.
     *
     * @param CheckoutCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param Context $context
     * @param ProductFactory $_productLoader
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param MpHelper $helperData
     */
    public function __construct(
        CheckoutCart $cart,
        ProductRepositoryInterface $productRepository,
        Context $context,
        ProductFactory $_productLoader,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        MpHelper $helperData
    ) {
        $this->cart              = $cart;
        $this->checkoutSession   = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->productLoader     = $_productLoader;
        $this->storeManager      = $storeManager;
        $this->_helperData       = $helperData;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $products = $this->getRequest()->getParam('products');
            $storeId  = $this->getRequest()->getParam('storeId');

            if (empty($products)) {
                $this->messageManager->addErrorMessage(__('Please check the selection checkbox of your products before adding all to cart'));

                return '';
            }

            $html = '<table class="table mptcv-table"><thead><tr><td>Produit</td><td></td><td>Qté</td>'
                . '<td></td></tr></thead><tbody>';

            $responses = [];
            foreach ($products as $productId => $options) {
                $qty = $options['qty'] ?: 1;

                if ($qty === 'NaN') {
                    $qty = 1;
                }

                $params = [];
                if (isset($options['product_params'])) {
                    parse_str($options['product_params'], $params);
                }

                if (empty($productId)) {
                    $this->messageManager->addErrorMessage(__('No products selected'));

                    $this->getResponse()->representJson(MpHelper::jsonEncode([
                        'status' => 0,
                        'notify' => ''
                    ]));
                }

                if (isset($options['mp_cpgv'])) {
                    foreach ($options['mp_cpgv'] as $childId => $childValue) {
                        $params['super_attribute'] = $childValue['super_attribute'];
                        $subQty                    = (float) $qty * (float) $childValue['qty'];
                        $params['qty']             = $subQty;
                        $response                  = $this->addItemToCart($productId, $storeId, $params);
                    }
                    $response['qty']  = $qty;
                    $response['html'] = $options['html'];

                    $responses[] = $response;
                } else {
                    $params['qty']    = $qty;
                    $response         = $this->addItemToCart($productId, $storeId, $params);
                    $response['html'] = isset($options['html']) ? $options['html'] : '';
                    $responses[]      = $response;
                }
            }

            $this->removeErrorItem();

            $this->cart->save();

            foreach ($responses as $response) {
                $html .= $this->getResponseHtml($response, $storeId);
            }

            $html .= '</tbody></table><div class="mptcv-table-action">'
                . '<button class="mptcv-button-continue" type="button">' . __('Continue Shopping') . '</button>'
                . '<button class="mptcv-button-cart" type="button">' . __('View Cart') . '</button></div>';

            return $this->getResponse()->representJson(MpHelper::jsonEncode($html));
        }

        return $this->getResponse()->representJson('{}');
    }

    /**
     * remove Item Error
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function removeErrorItem()
    {
        $items       = $this->checkoutSession->getQuote()->getItemsCollection();
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
                            $removeChild[$child->getSku()] = $child->getQty();
                        }
                    }
                    $keys[] = $key;
                }
            }
        }
        unset($item);
        foreach ($keys as $key) {
            $items->removeItemByKey($key);
        }

        foreach ($items as $key => $item) {
            if (isset($removeChild[$item->getSku()])) {
                $qtyToAdd = $removeChild[$item->getSku()];
                if ($item->getQty() === $qtyToAdd) {
                    $items->removeItemByKey($key);
                } else {
                    $item->unsQtyToAdd();
                }
            }
        }
    }

    /**
     * @param $response
     * @param $storeId
     *
     * @return string
     */
    public function getResponseHtml($response, $storeId)
    {
        try {
            $product = $this->productLoader->create()->load($response['product']->getId());
            if ($response['status']) {
                $statusLabel = 'Success';
                $statusClass = 'mptcv-success';

                $qty = $response['qty'];
            } else {
                $statusLabel = 'Fail<div class="message-error error message">' . $response['notify'] . '</div>';
                $statusClass = 'mptcv-errors';
                $qty         = 0;
            }

            if (empty($product->getImage())) {
                $imageLink = $this->_helperData->getDefaultImage();
            } else {
                $imageLink = $this->storeManager->getStore($storeId)
                        ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                    . 'catalog/product/' . $product->getImage();
            }
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return '';
        }

        return '<tr><td><img class="mptcv-table-image" src="' . $imageLink . '" alt="Image"></td>'
            . '<td><div class="mptcv-table-name">' . $product->getName() . '</div>'
            . '<div class="mptablecategoryview-list-options">' . $response['html'] . '</div>'
            . '</td><td><div class="mptcv-table-qty">' . $qty . '</div></td>'
            . '<td><div class="mptcv-table-status ' . $statusClass . '">' . $statusLabel . '</div></td></tr>';
    }

    /**
     * @param $productID
     * @param $storeId
     * @param array $options
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function addItemToCart($productID, $storeId, $options = [])
    {
        $product = $this->productRepository->getById(
            $productID,
            false,
            $storeId,
            true
        );
        try {
            /** @var Product $product */
            $this->cart->addProduct($product, $options);

            return [
                'status'  => 1,
                'notify'  => '',
                'product' => $product,
                'qty'     => $options['qty']
            ];
        } catch (Exception $e) {
            $this->cart->removeItem($productID);

            return [
                'status'  => 0,
                'notify'  => $e->getMessage(),
                'product' => $product
            ];
        }
    }
}
