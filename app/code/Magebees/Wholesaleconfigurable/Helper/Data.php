<?php
namespace Magebees\Wholesaleconfigurable\Helper;
use Magento\Store\Model\ScopeInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $storeManager;
    protected $date;
    protected $_filesystem;
    protected $_customerSession;
    protected $pricingHelper;
    protected $swatchdata;
    protected $currency;
    protected $imageHelper;
    protected $currencyLocale;
    protected $price;
    protected $product;
    protected $productRepository;
    protected $objectmanager;
    protected $stockRegistry;
    protected $_checkoutSession;
    protected $serialize;
    protected $_coreRegistry;
    protected $jsonEncoder;
    protected $localeFormat;
    

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
		\Magento\Directory\Model\PriceCurrency $currency,
		\Magento\Framework\Locale\Currency $currencyLocale,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Checkout\Model\Cart $_checkoutSession,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Catalog\Model\Product\Type\Price $price,
		\Magento\Catalog\Model\Product $product,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Helper\Image $imageHelper,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchdata
    ) {
        $this->_customerSession = $customerSession;
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->pricingHelper = $pricingHelper;
        $this->swatchdata = $swatchdata;
        $this->currency = $currency;
		$this->imageHelper = $imageHelper;
		$this->_coreRegistry = $coreRegistry;
		$this->_request = $request;
		$this->currencyLocale = $currencyLocale;
		$this->price = $price;
		$this->product = $product;
		$this->productRepository = $productRepository;
		$this->objectmanager = $objectmanager;
		$this->stockRegistry = $stockRegistry;
		$this->_checkoutSession = $_checkoutSession;
		$this->serialize = $serialize;
		parent::__construct($context);
    }

	public function getserData($data){
		return $this->serialize->unserialize($data);
	}

    public function moduleEnabled()
    {
        $moduleEnabled = $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled', ScopeInterface::SCOPE_STORE);
        if ($moduleEnabled) {
            $groupEnabled = trim($this->groupEnabled());
            $customerId =  trim($this->customerGroupId());
            if ($groupEnabled == "all") {
                return true;
            } elseif ($groupEnabled == $customerId) {
                return true;
            } elseif ($groupEnabled == "No") {
                return false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
	
	public function loadProduct($sku) {
   		return $this->productRepository->get($sku);
	}
	public function getProductImage($sku) {
   		$product = $this->loadProduct($sku);
		if($product->getImage() != "no_selection" && $product->getImage() != ""){
			$imagePath = $this->imageHelper->init($product, 'small_image')
				->setImageFile($product->getImage())
				->resize(100)
				->getUrl();
		}else{
			$imagePath =  $this->imageHelper->getDefaultPlaceholderUrl('image'); 
		}			
		return $imagePath;
	}	

	
	public function getStockRegistry()
	{
    	return $this->stockRegistry;
	}
    public function getCustomerGroup()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/customer_group', ScopeInterface::SCOPE_STORE);
    }
	
	public function getObjectManager()
    {
        return $this->objectmanager;
    }
	
	public function enabledMinQty()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_min_qty', ScopeInterface::SCOPE_STORE);
    }
	
	public function enabledShowOptions()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_show_options', ScopeInterface::SCOPE_STORE);
    }
	
	public function enabledGropedPrd()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_group', ScopeInterface::SCOPE_STORE);
    }
	
	public function enabledMaxQty()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_max_qty', ScopeInterface::SCOPE_STORE);
    }
	
	public function productListing()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_product_listing', ScopeInterface::SCOPE_STORE);
    }
	
	public function eachRowAddtocart()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_add_to_cart', ScopeInterface::SCOPE_STORE);
    }
    
	public function addAlltocart()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/enabled_add_all_to_cart', ScopeInterface::SCOPE_STORE);
    }
    
    public function customerGroupId()
    {
        $customerSession = $this->objectmanager->create('Magento\Customer\Model\Session');	
		if($customerSession->isLoggedIn()) {
        	return  $customerSession->getCustomer()->getGroupId();
		}else {
            return "0";
        }
		
    }
    
    public function groupEnabled()
    {
        $customerGroup = explode(",", $this->getCustomerGroup());
	
        if (in_array("all", $customerGroup)) {
            return "all";
        } else {
            $customerId =  $this->customerGroupId();
			if (in_array(trim($customerId), $customerGroup)) {
                return $customerId;
            } else {
                return "No";
            }
        }
    }
    
    public function getGroupAttribute()
    {
        return $this->scopeConfig->getValue('wholesaleconfigurable/setting/displayattributes', ScopeInterface::SCOPE_STORE);
    }
	
	public function getHidePrice()
    {
        $hidePirce = $this->scopeConfig->getValue('wholesaleconfigurable/setting/hide_price_group', ScopeInterface::SCOPE_STORE);
		
		$hidePirces = explode(",", (string)$hidePirce);
        if (in_array("all", $hidePirces)) {
            return false;
        } else {
            $customerId =  $this->customerGroupId();
            if (in_array($customerId, $hidePirces)) {
                return false;
            } else {
                return true;
            }
        }
    }
	
    
    public function getPrice($productPrice)
    {
        return $this->pricingHelper->currency($productPrice, true, false);
    }
    
    public function getPriceFormat()
    {
        $config = $this->localeFormat->getPriceFormat();
        return $this->jsonEncoder->encode($config);
    }
    
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
	
	
    public function getChildrenInfo($product, $productAttributes)
    {
		
        $obj = $this->objectmanager;
        $associative_products = $product->getTypeInstance(false)->getUsedProducts($product);
        $assc_product_data = [];
        $labels = [];
        $options = [];
		
        foreach ($associative_products as $assc_products) {
            if ($assc_products->getStatus() == 1) {
				$product = $obj->create('\Magento\Catalog\Model\Product')->load($assc_products->getId());
                //$stockState = $obj->create('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
				$productStock = $product->getExtensionAttributes()->getStockItem();
                //$productStock = $stockState->get($assc_products->getId(), $assc_products->getStore()->getWebsiteId());
                $productQty = $productStock->getData('qty');
                $available =  $productStock->getData('manage_stock');
            			
                $instock = false;
                $backorder = false;
				
                if ($assc_products->isSaleable()) {
                    $instock = true;
                }
				 $stock = number_format($productQty);
                if ($available) {
                    $stock = number_format($productQty);
                } else {
                    $stock = "0";
                } 
			

                $assc_product_data[$assc_products->getId()]['info'] = ['price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId(), 'status_stock' => $instock, 'backorder' => $backorder, 'tier_price' => $this->_pushTierPrice($assc_products)];

                foreach ($productAttributes as $attribute) {
                    $additional_data = [];
                    $attributeData = $attribute->getProductAttribute()->getData('additional_data');
                    if (!empty($attributeData)) {
                       	$objectManager = $this->objectmanager;
						$swatchjason = $objectManager->create('Magento\Framework\Serialize\Serializer\Json');
                        $additional_data = $swatchjason->unserialize($attributeData);
                    }
					
				
                    if (!array_key_exists("swatch_input_type",$additional_data)) {
                        $additional_data['swatch_input_type'] = "";
                    }	
                    if ($additional_data['swatch_input_type'] == "visual") {
                        $frontend_input = "swatch_visual";
                    } elseif ($additional_data['swatch_input_type'] == "text") {
                        $frontend_input = "swatch_text";
                    } else {
                        $frontend_input = "drop_down";
                    }
                    
            
                    $_attributePrice = $attribute->getOptions();
                    $labels[$attribute->getLabel()] = $attribute->getLabel();
                    $value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
                    $options[$value] = $value;
                    $att_array = ['code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId(), 'frontend_input' => $frontend_input];

                    foreach ($_attributePrice as $optionVal) {
                        if ($optionVal['label'] == $value) {
                            $att_array['option_id'] = $optionVal['value_index'];
                    
                            $swatch_value = $obj->create('Magento\Swatches\Model\ResourceModel\Swatch\Collection')->addFieldtoFilter('option_id', $optionVal['value_index'])->addFieldtoFilter('store_id', 0)->getFirstItem()->getData();
                            
                            if ($swatch_value) {
                                $att_array['swatch_value'] = $swatch_value['value'];
                                $att_array['swatch_type'] = $swatch_value['type'];
                            } else {
                                $att_array['swatch_value'] = $value;
                                $att_array['swatch_type'] = '5';
                            }
                        }
                    }
                    $assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
                }
            }
        }
	
	    $assc_product_data = $assc_product_data;
        $configurable_products = ['num_attributes' => count($productAttributes), 'products' => $assc_product_data, 'labels' => $labels, 'options' => $options];
		
		$configurbaleData = $this->serialize->serialize($configurable_products);
		
        return $configurbaleData;
    }
	
	public function _pushTierPrice($child)
    {
        $tierPriceModel = $child->getPriceInfo()->getPrice('tier_price');
        $tierPricesList = $tierPriceModel->getTierPriceList();
        $detailedPrice = '';
        $tierPriceHtml = '';
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $index => $price) {
                $detailedPrice .= '<li class="item">';
                $detailedPrice .= __(
                    'Buy %1 for %2 each and <strong>save&nbsp;%4%</strong></li>',
                    $price['price_qty'],
                    $this->getFormatPrice($price['price']->getValue()),
                    $index,
                    $tierPriceModel->getSavePercent($price['price'])
                );
                $detailedPrice .= '</li>';
            }
        }
        if ($detailedPrice != '' && !$this->checkCustomer('hide_price')) {
            $tierPriceHtml = '<ul class="prices-tier items">' . $detailedPrice . '</ul>';
        }
        return $tierPriceHtml;
    }
	

	
	public function checkCustomer($field = null)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $customerConfig = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/'.$field,
            $scope
        );
        if ($customerConfig != '') {
            $customerConfigArr = explode(',', $customerConfig);
            if ($this->customerSession->create()->isLoggedIn()) {
                $customerGroupId = $this->customerSession->create()->getCustomerGroupId();
                if (in_array($customerGroupId, $customerConfigArr)) {
                    return true;
                }
            } else {
                if (in_array(0, $customerConfigArr)) {
                    return true;
                }
            }
        }
        return false;
    }
	
	public function getFormatPrice($price = null)
    {
        $currencyCode = $this->currency->getCurrency()->getCurrencyCode();
        return $this->currencyLocale->getCurrency($currencyCode)->toCurrency($price);
    }
    
    public function getHasSpecialPrice($product)
    {
        $customerGroup = $this->customerGroupId();
        $groupPrices = $product->getData('group_price');
        $groupPriceRes ='';
        $specialPriceRes = '';
        if ($groupPrices) {
            foreach ($groupPrices as $groupPrice) {
                if ($groupPrice['cust_group'] == $customerGroup && $groupPrice['price'] == $product->getFinalPrice()) {
                    $groupPriceRes = $groupPrice['price'];
                    break;
                }
            }
        }
        if ($product->getSpecialPrice() && $product->getSpecialPrice() == $product->getFinalPrice()) {
            $specialPriceRes = $product->getSpecialPrice();
        }
        if ($groupPriceRes && !$specialPriceRes) {
            return $groupPriceRes;
        }
        if (!$groupPriceRes && $specialPriceRes) {
            return $specialPriceRes;
        }
        return min([$groupPriceRes,$specialPriceRes]);
    }
	
	public function getExpandNo(){
		return $this->scopeConfig->getValue('wholesaleconfigurable/setting/expandno',ScopeInterface::SCOPE_STORE);
	}
	
	public function getCurrentProductId(){
		return $this->_coreRegistry->registry('current_product')->getId();
	}	
	
    public function getCartProduct($currentProductId){	
		return $this->_checkoutSession->getQuote()->hasProductId($currentProductId);
	}
	
	public function getCartItems(){	
		$carts = $this->_checkoutSession->getQuote();
		$result = $carts->getAllItems();
		$itemsIds = array();
		foreach ($result as $cartItem) {
			if ($cartItem->getHasChildren() ) {
				foreach ($cartItem->getChildren() as $child) {
					$itemsIds[$child->getProductId()] = $cartItem->getQty();
				}
			}
		}
		return $itemsIds;
	}
	
	public function getCartId(){	
		$carts = $this->_checkoutSession->getQuote();
		$result = $carts->getAllItems();
		$cartIds = array();
		foreach ($result as $cartItem) {
			if ($cartItem->getHasChildren() ) {
				foreach ($cartItem->getChildren() as $child) {
					$cartIds[$child->getProductId()] = $cartItem->getId();
				}
			}
		}
		return $cartIds;
	}
	
	public function getActionName(){	
		return $this->_request->getFullActionName();
	}
	
	public function getSubTotal($qty,$id){	
		$productInfo = $this->product->load($id);
		$cntprice = $this->price->getFinalPrice($qty, $productInfo);
		return $cntprice;
	}
	
	
}
