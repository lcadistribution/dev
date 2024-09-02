<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebees\Wholesaleconfigurable\Controller\Cart;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateItemOptions extends \Magento\Framework\App\Action\Action
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
				$configData[] = ['form_key' => $params['form_key'] ,'product' => $params['product'] , 'selected_configurable_option' => '' , 'super_attribute' => $super_attribute , 'qty' => $v, 'row_name' => $params['row_name'][$x], 'id' => $params['cartid'][$x], 'item' => $params['cartid'][$x], 'options' => $options  ];
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
						if($paramsdata['id'] == "add"){
							unset($paramsdata['id']);
							unset($paramsdata['item']);
                        	$this->cart->addProduct($_product, $paramsdata);
						}else{	
							$item = $this->cart->updateItem($paramsdata['id'], new \Magento\Framework\DataObject($paramsdata));
						}
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
            } else {
                $cartresult['status'] = false;
                $this->messageManager->addError(
                    __('Please insert product(s).')
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
                    $cartresult['status'] = false;
                    $cartresult['row_name'] = $configData[$z]['row_name'];
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
                $cartresult['messages'] = $messages;
                $cartresult['status'] = false;
                $cartresult['row_name'] = $configData[$z]['row_name'];
                $cartresult['url'] = '';
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $cartresult['status'] = false;
        }

        if ($cartresult['status']) {
            $this->_objectManager->create('Magento\Catalog\Model\Session')->unsFastorderVal();
             $baseUrl =$this->_objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl()."checkout/cart/index";
             $cartresult['url'] = $baseUrl;
        }
		//$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($cartresult));
        //return;
		  return $this->resultRedirectFactory->create()->setPath('*/*');

	}	
}
