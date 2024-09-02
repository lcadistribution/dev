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
 * @category  Mageplaza
 * @package   Mageplaza_ConfigureGridView
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Controller\Customer;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Mageplaza\ConfigureGridView\Helper\Data;

/**
 * Class AddToWishlist
 * @package Mageplaza\ConfigureGridView\Controller\Customer
 */
class AddToWishlist extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var RedirectInterface
     */
    protected $reDirector;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var WishlistHelper
     */
    protected $wishlistHelper;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * AddToWishlist constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param AuthenticationStateInterface $authenticationState
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param WishlistHelper $wishlistHelper
     * @param FormKey $formKey
     * @param UrlInterface $url
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AuthenticationStateInterface $authenticationState,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        WishlistHelper $wishlistHelper,
        FormKey $formKey,
        UrlInterface $url,
        Data $helperData
    ) {
        $this->_customerSession    = $customerSession;
        $this->authenticationState = $authenticationState;
        $this->reDirector          = $context->getRedirect();
        $this->helperData          = $helperData;
        $this->wishlistProvider    = $wishlistProvider;
        $this->productRepository   = $productRepository;
        $this->wishlistHelper      = $wishlistHelper;
        $this->formKey             = $formKey;
        $this->url                 = $url;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $session        = $this->_customerSession;
        $requestParams  = $this->getRequest()->getParams();
        if ($this->authenticationState->isEnabled() && !$session->isLoggedIn()) {
            if (!$session->getBeforeWishlistUrl()) {
                $session->setBeforeWishlistUrl($this->reDirector->getRefererUrl());
            }

            $session->setBeforeWishlistRequest($requestParams);
            $session->setBeforeRequestParams($requestParams);
            $session->setBeforeModuleName('mpcpgv');
            $session->setBeforeControllerName('customer');
            $session->setBeforeAction('addtowishlist');

            $this->messageManager->addErrorMessage(__('You must login or register to add items to your wishlist.'));

            $result = [
                'error'   => true,
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->setBeforeWishlistRequest(null);
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $this->messageManager->addErrorMessage(__('Page not found.'));
            $result = [
                'error' => true
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $productId = isset($requestParams['product']) ? (int) $requestParams['product'] : null;
        if (!$productId) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $result = [
                'error' => true
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $result = [
                'error' => true
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $result = ['error' => true];

        try {
            $items = Data::jsonDecode($this->getRequest()->getParam('items'))
                ?: Data::jsonDecode($requestParams['items']);

            if (empty($items)) {
                $this->messageManager->addErrorMessage(__('No products selected'));

                return $this->getResponse()->representJson(Data::jsonEncode(['error' => true]));
            }

            foreach ($items as $item) {
                $params = [
                    'product'  => $requestParams['product'],
                    'form_key' => $this->formKey->getFormKey(),
                    'qty'      => $item['qty'],
                ];

                foreach ($item['attributes'] as $attribute) {
                    $attributeId                             = $attribute['attributeId'];
                    $attributeValue                          = $attribute['attributeValue'];
                    $params['super_attribute'][$attributeId] = $attributeValue;
                }
                $buyRequest = new DataObject($params);
                $result     = $wishlist->addNewItem($product, $buyRequest);
                if (is_string($result)) {
                    $error = new LocalizedException(__($result));
                    $this->messageManager->addErrorMessage($error->getMessage());
                    $result = ['error' => true, 'message' => $error->getMessage()];

                    return $this->getResponse()->representJson(Data::jsonEncode($result));
                }

                if ($wishlist->isObjectNew()) {
                    $wishlist->save();
                }

                $this->wishlistHelper->calculate();

                $result = [
                    'error'   => false,
                    'backUrl' => $this->url->getUrl('wishlist', ['wishlist_id' => $wishlist->getId()])
                ];
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
            $result = [
                'error' => true
            ];
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now.')
            );
            $result = [
                'error' => true
            ];
        }

        if (!$result['error']) {
            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_redirect->getRefererUrl();
            }

            $this->messageManager->addComplexSuccessMessage(
                'addProductSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'referer'      => $referer
                ]
            );
        }

        if (!$this->getRequest()->isAjax()) {
            $resultRedirect->setPath('wishlist', ['wishlist_id' => $wishlist->getId()]);

            return $resultRedirect;
        }

        return $this->getResponse()->representJson(Data::jsonEncode($result));
    }
}
