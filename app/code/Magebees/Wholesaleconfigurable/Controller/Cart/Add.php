<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\Wholesaleconfigurable\Controller\Cart;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filter\LocalizedToNormalized;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Framework\App\Action\Action
{
      /**
      * @var \Magento\Checkout\Model\Cart
      */
    protected $cart;
    protected $productFactory;
    protected $resultPageFactory;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

   	public function execute()
    {
		$params = $this->getRequest()->getParams();
		
		
		if(isset($params['config_table_qty'])){
			$options = '';
			if(isset($params['options'])){
				$options = $params['options'];
			}
			
			$qtyData = $params['config_table_qty'];
			$remove = [0];
			$resultQty = array_diff($qtyData, $remove);
			$configData = array();
			foreach ($resultQty as $x => $v) {
				if ($v > 0) {
					$super_attribute = $params['super_attribute'][$x];
					$configData[] = ['uenc' => $params['uenc'] , 'form_key' => $params['form_key'] ,'product' => $params['product'] , 'selected_configurable_option' => '' , 'super_attribute' => $super_attribute , 'qty' => $v, 'row_name' => $params['row_name'][$x], 'options' => $options ];
				}
			}
		}else{
			
			if (isset($params['qty'])) {
                  $filter = new LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
		
			$_product = $this->productFactory->create()->load($params['product']);
			if ($_product) {
				 try {
					$this->cart->addProduct($_product, $params);
					$this->cart->save();
					$message = __(
                            'You added %1 to your shopping cart.',
                            $_product->getName()
                        );
                    $this->messageManager->addSuccessMessage($message);
				 }catch (\Magento\Framework\Exception\LocalizedException $e) {
				 	$this->messageManager->addError(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
				 }
			}else{
				return $this->goBack();
			}
			
			 $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $_product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                   if ($this->shouldRedirectToCart()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $_product->getName()
                        );
                    } else {
                       // $this->messageManager->addComplexSuccessMessage(
                          //  'addCartSuccessMessage',
                          //  [
                            //    'product_name' => $_product->getName(),
                             //   'cart_url' => $this->getCartUrl(),
                           // ]
                       // );
                    }
                }
                return $this->goBack(null, $_product);
            }
			
		}	
	
		
		$cartresult = [];
        $success = false;
        $z = 0;
        try {
            $productNames = [];
            foreach ($configData as $paramsdata) {
                $qty = $paramsdata['qty'];
                if ($qty>0) {
                    $_product = $this->productFactory->create()->load($paramsdata['product']);
                    if ($_product) {
                        $productNames[] = '"' . $_product->getName() . '"';
                        $this->cart->addProduct($_product, $paramsdata);
                        $success = true;
                    }
                }
                $z++;
            }

            if ($success) {
                $this->cart->save();
                $cartresult['status'] = true;
				$name = array_unique($productNames);
                $message = __(
                    'You added %1 to your shopping cart.',
                    join(', ', $name)
                );
                $this->messageManager->addSuccessMessage($message);
                $cartresult['message']  = $message;
                return $this->goBack();
            } else {
                $cartresult['status'] = false;
                $this->messageManager->addError(
                    __('Please specify the quantity of product(s).

')
                );
                return $this->goBack();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
			
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
                    $cartresult['status'] = false;
					if(isset($configData[$z]['row_name'])){
                    	$cartresult['row_name'] = $configData[$z]['row_name'];
					}
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($messages[0])
                    );

                $cartresult['status'] = false;
				if(isset($configData[$z]['row_name'])){
                	$cartresult['row_name'] = $configData[$z]['row_name'];
				}	
                $cartresult['url'] = '';
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $cartresult['status'] = false;
			if(isset($configData[$z]['row_name'])){	
            	$cartresult['row_name'] = $configData[$z]['row_name'];
			}	

        }

        if ($cartresult['status']) {
            $this->_objectManager->create('Magento\Catalog\Model\Session')->unsFastorderVal();
             $baseUrl =$this->_objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl()."checkout/cart/index";
             $cartresult['url'] = $baseUrl;
        }
		$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($cartresult));
        return;

	}	
	private function shouldRedirectToCart()
    {
        return $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
	public function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
	
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }
        
        return $resultRedirect;
    }
	
	
	protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }

        if ($this->shouldRedirectToCart() || $this->getRequest()->getParam('in_cart')) {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }
            return $this->_url->getUrl('checkout/cart');
        }

        return $defaultUrl;
    }
	 private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
