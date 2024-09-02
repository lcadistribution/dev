<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AttrBaseSplitcart\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * AttrBaseSplitcart data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger
     */
    protected $attrBaseLogger;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;
    
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @var \CustomerInterfaceFactory
     */
    protected $customerDataFactory;
    
    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Cookie\Guestcart
     */
    protected $guestCart;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;
    
    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;
    
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;
    
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

     /**
      * @var array
      */
    protected $groupParams;

    /**
     * @var \Magento\Catalog\Model\Product
     */
      protected $_product;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger $attrBaseLogger
     * @param AttributeFactory $attributeFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Checkout\Model\Cart $cart
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\AttrBaseSplitcart\Cookie\Guestcart $guestCart
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $productModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger $attrBaseLogger,
        AttributeFactory $attributeFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\AttrBaseSplitcart\Cookie\Guestcart $guestCart,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $productModel
    ) {
        parent::__construct($context);
        $this->attrBaseLogger = $attrBaseLogger;
        $this->attributeFactory = $attributeFactory;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerMapper = $customerMapper;
        $this->jsonHelper = $jsonHelper;
        $this->guestCart = $guestCart;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->product = $product;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteItem = $quoteItem;
        $this->priceHelper = $priceHelper;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->_product = $productModel;
    }

    /**
     * [getEnableAttributeSplitcartSettings used to get spitcart is enable or not].
     *
     * @return [integer] [returns 0 if disable else return 1]
     */
    public function getEnableAttributeSplitcartSettings()
    {
        try {
            return $this->scopeConfig->getValue(
                'attrbasesplitcart/general_settings/active',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getEnableAttributeSplitcartSettings : ".$e->getMessage());
        }
    }

    /**
     * [getSelectedAttribute used to get selected attribute].
     *
     * @return [string] [returns attribute code]
     */
    public function getSelectedAttribute()
    {
        try {
            return $this->scopeConfig->getValue(
                'attrbasesplitcart/general_settings/attribute',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getSelectedAttribute : ".$e->getMessage());
        }
    }

    /**
     * Function isModuleEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        try {
            $flag =  $this->_moduleManager->isEnabled($moduleName);
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data isModuleEnabled : ".$e->getMessage());
        }
        return $flag;
    }

    /**
     * Function isOutputEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isOutputEnabled($moduleName)
    {
        try {
            $flag =  $this->_moduleManager->isOutputEnabled($moduleName);
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data isOutputEnabled : ".$e->getMessage());
        }
        return $flag;
    }

    /**
     * Function checkAttributesplitcartStatus checks a given module status
     *
     * @return boolean
     */
    public function checkAttributesplitcartStatus()
    {
        try {
            $moduleEnabled = $this->isModuleEnabled('Webkul_AttrBaseSplitcart');
            $moduleOutputEnabled = $this->isOutputEnabled('Webkul_AttrBaseSplitcart');
            $flag = ($this->getEnableAttributeSplitcartSettings()
                && $moduleEnabled
                && $moduleOutputEnabled
            ) ? true : false;
            return $flag;
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data checkAttributesplitcartStatus : ".$e->getMessage());
            return false;
        }
    }

    /**
     * Function getCatalogPriceIncludingTax get value of shipping include tax
     *
     * @return boolean
     */
    public function getCatalogPriceIncludingTax()
    {
        try {
            return $this->scopeConfig->getValue(
                'tax/calculation/shipping_includes_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getCatalogPriceIncludingTax : ".$e->getMessage());
            return false;
        }
    }

    /**
     * Function getAttributeValue get value of selected Attribute
     *
     * @param string $attrCode
     * @param string $attrValue
     * @param string $productType
     * @return string
     */
    public function getAttributeValue($attrCode, $attrValue = "", $productType = "")
    {
        try {
            $attrDetailArr = ['label'=>"", 'value'=>"N/A"];
            $attributeInfoColl = $this->attributeFactory->create()->getCollection()
                                ->addFieldToFilter('attribute_code', ['eq' => $attrCode]);
            foreach ($attributeInfoColl as $attrInfoData) {
                $attrDetailArr['label'] = $attrInfoData->getFrontendLabel();
                $options = $attrInfoData->getSource()->getAllOptions();
                foreach ($options as $option) {
                    if ($option['value']==$attrValue) {
                        $attrDetailArr['value'] = $option['label'];
                    }
                }
            }
            return $attrDetailArr;
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getCatalogPriceIncludingTax : ".$e->getMessage());
            return $attrDetailArr;
        }
    }

    /**
     * [getUpdatedQuote used to remove items of other attribute value from the quote].
     *
     * @param [array] $attrsplitcartData [current attribute data]
     *
     * @return void
     */
    public function getUpdatedQuote($attrsplitcartData)
    {
        try {
            $this->checkoutSession->setAttrsplitcartData($attrsplitcartData);
            $oldQuote = $this->cart->getQuote();
            $newQuote = $this->quoteFactory->create();
            $newQuote->setStoreId($oldQuote->getStoreId());
            if ($oldQuote->getCustomerId()) {
                $newQuote->setCustomerId($oldQuote->getCustomerId());
                $newQuote->setCustomerGroupId($oldQuote->getCustomerGroupId());
                $newQuote->setCustomerEmail($oldQuote->getCustomerEmail());
                $newQuote->setCustomerFirstname($oldQuote->getCustomerFirstname());
                $newQuote->setCustomerLastname($oldQuote->getCustomerLastname());
                $newQuote->setCustomerDob($oldQuote->getCustomerDob());
                $newQuote->setCustomerGender($oldQuote->getCustomerGender());
            }
            $newQuote->merge($oldQuote);
            $newQuote->collectTotals()->save();
            $oldQuote->setIsActive(0)->save();
            $this->checkoutSession->replaceQuote($newQuote);
            $newQuote->setIsActive(1)->save();
            $this->checkoutSession->setCartWasUpdated(true);
            $attrCode = $this->getSelectedAttribute();
            $count = 0;
            foreach ($newQuote->getAllItems() as $item) {
                $TypeId= $item->getProduct()->getTypeId();
                if (!$item->hasParentItemId()) {
                    if ($TypeId =='configurable') {
                        $ItemId =  $item->getItemId();
                        $quoteId =  $item->getQuoteId();
                        $productId= $item->getProduct()->getEntityId();
                        
                        $quoteItem = $this->quoteItemFactory->create()->getCollection()
                        ->addFieldToFilter('quote_id', ['eq'=>$quoteId])
                        ->addFieldToFilter('parent_item_id', ['eq'=>$ItemId])
                        ->getFirstItem();
                        $productSku = $this->product->create()->load($quoteItem->getProductId())->getSku();
            
                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    } else {
                        $productId= $item->getProduct()->getEntityId();
                            
                        $productSku = $this->product->create()->load($productId)->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    }

                    if ($attributeOptionId===0) {
                        $attributeOptionId = 0;
                    }
                    if ($attributeOptionId=="") {
                        $attributeOptionId = -1;
                    }
                   
                    if ($attrsplitcartData['attrsplitcart_value'] != $attributeOptionId) {
                         $newQuote->deleteItem($item);
                    } else {
                        $count +=$item->getQty();
                    }
                }
            }
            $newQuote->setItemsQty($count);
            $newQuote->save();
         
            $newQuote->collectTotals()->save();
            
            $this->checkoutSession->replaceQuote($newQuote);
            $this->checkoutSession->setCartWasUpdated(true);
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getUpdatedQuote : ".$e->getMessage());
        }
    }

    /**
     * [addQuoteToVirtualCart used to set cart was updated true].
     *
     * @return void
     */
    public function addQuoteToVirtualCart()
    {
        try {
            $quote = $this->cart->getQuote();
            $attrCode = $this->getSelectedAttribute();
            $virtualCart = [];

            if ($virtualCart == null
                || !is_array($virtualCart)
                || $virtualCart == ""
            ) {
                $virtualCart = [];
            }
            
            foreach ($quote->getAllItems() as $item) {
                $productType= $item->getProduct()->getTypeId();
                $attributesData = [];
                $bundleOption = [];
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $isGrouped = false;
                if ($productType=="grouped") {
                    $isGrouped = true;
                }
                if ($productType == "bundle" && $item->getHasChildren()) {
                    $bundleOption = $this->processBundleOption($options);
                } else {
                    $attributesData = $this->updateRequestData($options);
                }
                
                if (!$item->hasParentItemId()) {
                    if ($productType =='configurable') {
                        $itemId =  $item->getItemId();
                        $quoteId =  $item->getQuoteId();
                        $productId = $item->getProduct()->getEntityId();
                        
                        $quoteItem = $this->quoteItemFactory->create()->getCollection()
                        ->addFieldToFilter('quote_id', ['eq'=>$quoteId])
                        ->addFieldToFilter('parent_item_id', ['eq'=>$itemId])
                        ->getFirstItem();
                       
                        $productSku = $this->product->create()->load($quoteItem->getProductId())->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    } else {
                        $productId= $item->getProduct()->getEntityId();
                        $productSku = $this->product->create()->load($productId)->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attributeOptionId = $product->getData($attrCode);
                    }

                    if ($attributeOptionId===0) {
                        $attributeOptionId = 0;
                    }
                    if ($attributeOptionId=="") {
                        $attributeOptionId = -1;
                    }
                
                    $price =  $item->getRowTotal();
                    if ($this->getCatalogPriceIncludingTax()) {
                        $price = $item->getRowTotalInclTax();
                    }
                    $cartArray = [];
                    $formattedPrice = $this->priceHelper->currency($price, true, false);
                    if (!isset($cartArray[$attributeOptionId]['attr_detail'])) {
                        $attrDetail = $this->getAttributeValue($attrCode, $attributeOptionId, $productType);
                        $cartArray[$attributeOptionId]['attr_detail'] = $attrDetail;
                    }
                    $cartArray[$attributeOptionId][$item->getId()] = $formattedPrice;

                    if (!isset($cartArray[$attributeOptionId]['total'])
                        || $cartArray[$attributeOptionId]['total']==null
                    ) {
                        $cartArray[$attributeOptionId]['total'] = $price;
                    } else {
                        $cartArray[$attributeOptionId]['total'] += $price;
                    }
                    $formattedPrice = $this->priceHelper->currency(
                        $cartArray[$attributeOptionId]['total'],
                        true,
                        false
                    );
                    $cartArray[$attributeOptionId]['formatted_total'] = $formattedPrice;

                    $virtualCart = $this->updateVirtualCartItemData(
                        $isGrouped,
                        $virtualCart,
                        $attributeOptionId,
                        $productId,
                        $item,
                        $attributesData
                    );
                }

                if (!empty($bundleOption)) {
                    $virtualCart[$attributeOptionId][$productId]['bundle_options'] =
                        $this->jsonHelper->jsonEncode($bundleOption, true);
                }
            }
            $this->setAttributeVirtualCart($virtualCart);
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_addQuoteToVirtualCart Exception : ".$e->getMessage()
            );
        }
    }
    
    /**
     * Function processBundleOption
     *
     * @param array $options
     * @return array
     */
    private function processBundleOption($options)
    {
        $bundleOption = [];
        $bundleOption['selected_configurable_option'] = $options['info_buyRequest'][
            'selected_configurable_option'
        ];
        if (array_key_exists('bundle_option', $options['info_buyRequest'])) {
            $bundleOption['bundle_option'] = $options['info_buyRequest']['bundle_option'];
        }
        if (array_key_exists('bundle_option_qty', $options['info_buyRequest'])) {
            $bundleOption['bundle_option_qty'] = $options['info_buyRequest']['bundle_option_qty'];
        }
        return $bundleOption;
    }

    /**
     * Function updateRequestData
     *
     * @param array $options
     * @return array
     */
    private function updateRequestData($options)
    {
        $attributesData = [];
        $attributesData = $options['info_buyRequest'];
        if (array_key_exists('qty', $attributesData)) {
            unset($attributesData['qty']);
        }
        if (array_key_exists('product', $attributesData)) {
            unset($attributesData['product']);
        }
        return $attributesData;
    }

    /**
     * Function updateVirtualCartItemData
     *
     * @param boolean $isGrouped
     * @param array $virtualCart
     * @param int $attributeOptionId
     * @param int $productId
     * @param object $item
     * @param array $attributesData
     * @return array
     */
    private function updateVirtualCartItemData(
        $isGrouped,
        $virtualCart,
        $attributeOptionId,
        $productId,
        $item,
        $attributesData
    ) {
        if ($isGrouped) {
            $virtualCart[$attributeOptionId]['grouped'][$productId]['qty'] = $item->getQty();
            $virtualCart[$attributeOptionId]['grouped'][$productId]['item_id'] = $item->getId();
            if (array_key_exists($productId, $virtualCart[$attributeOptionId])
                && array_key_exists('item_id', $virtualCart[$attributeOptionId][$productId])
                && $virtualCart[$attributeOptionId][$productId]['item_id'] == $item->getId()
            ) {
                unset($virtualCart[$attributeOptionId][$productId]);
            }
        } else {
            $virtualCart[$attributeOptionId][$productId]['qty'] = $item->getQty();
            $virtualCart[$attributeOptionId][$productId]['item_id'] = $item->getId();
            if ($item->getParentItem()) {
                $virtualCart[$attributeOptionId][$productId]['item_id'] = $item->getParentItem()->getId();
            }
        }
    
        if (!empty($attributesData)) {
            if ($isGrouped) {
                $virtualCart[$attributeOptionId]['grouped'][$productId]['child'] =
                    $this->jsonHelper->jsonEncode($attributesData, true);
            } else {
                $virtualCart[$attributeOptionId][$productId]['child'] =
                    $this->jsonHelper->jsonEncode($attributesData, true);
            }
        } else {
            if (array_key_exists('child', $virtualCart[$attributeOptionId][$productId])) {
                unset($virtualCart[$attributeOptionId][$productId]['child']);
            } elseif (array_key_exists('grouped', $virtualCart[$attributeOptionId])
                && array_key_exists($productId, $virtualCart[$attributeOptionId]['grouped'])
                && array_key_exists('child', $virtualCart[$attributeOptionId]['grouped'][$productId])
            ) {
                unset($virtualCart[$attributeOptionId]['grouped'][$productId]['child']);
            }
        }
        return $virtualCart;
    }
    
    /**
     * [setAttributeVirtualCart used to set virtual cart of user in customer session].
     *
     * @param string $virtualCart [contains virtual cart data]
     *
     * @return void
     */
    public function setAttributeVirtualCart($virtualCart)
    {
        try {
            if (!empty($virtualCart)) {
                $virtualCart = $this->validateVirtualCart($virtualCart);
            }
            $virtualCart = $this->jsonHelper->jsonEncode($virtualCart, true);
            
            if ($this->customerSession->isLoggedIn()) {
                $customerId  = $this->customerSession->getId();
                $customerData = [];
                $savedCustomerData = $this->customerRepository
                    ->getById($customerId);

                $customer = $this->customerDataFactory->create();
                //merge saved customer data with new values
                $customerData = array_merge(
                    $this->customerMapper->toFlatArray($savedCustomerData),
                    $customerData
                );

                $customerData['attr_virtual_cart'] = $virtualCart;
                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                //save customer
                $this->customerRepository->save($customer);
            } else {
                $this->guestCart->delete();
                $this->guestCart->set($virtualCart, 3600);
                $this->checkoutSession->setGuestVertualData($virtualCart);
            }
            
        } catch (\Exception $e) {
            $this->attrBaseLogger->info(
                "Helper_Data setAttributeVirtualCart : ".$e->getMessage()
            );
        }
    }

    /**
     * [getAttributeVirtualCart used to get virtual cart of user].
     *
     * @return string [returns attribute virtual cart data]
     */
    public function getAttributeVirtualCart()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getId();
                $customerData = [];
                $savedCustomerData = $this->customerRepository
                    ->getById($customerId);
                //merge saved customer data with new values
                
                $customerData = array_merge(
                    $this->customerMapper->toFlatArray($savedCustomerData),
                    $customerData
                );
                if (array_key_exists('attr_virtual_cart', $customerData)) {
                    $attrvirtualCart = $customerData['attr_virtual_cart'];
                } else {
                    $attrvirtualCart = "";
                }
            } else {
                $attrvirtualCart = $this->guestCart->get();
            }
           
        } catch (\Exception $e) {
            $this->attrBaseLogger->info("Helper_Data getAttributeVirtualCart : ".$e->getMessage());
        }

        return $attrvirtualCart;
    }

    /**
     * Function getCheckoutRemoveSession used to get a value from checkout session
     *
     * @return int
     */
    public function getCheckoutRemoveSession()
    {
        try {
            $removeItem =  $this->checkoutSession->getWkRemoveItem();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getCheckoutRemoveSession Exception : ".$e->getMessage()
            );
        }
        return $removeItem;
    }
    
    /**
     * Function getVirtualCart used to get virtual cart of user
     *
     * @return array [returns virtual cart data]
     */
    public function getVirtualCart()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getId();
                $customerData = [];
                $savedCustomerData = $this->customerRepository
                    ->getById($customerId);
                $customerData = array_merge(
                    $this->customerMapper->toFlatArray($savedCustomerData),
                    $customerData
                );
                if (array_key_exists('attr_virtual_cart', $customerData)) {
                    $virtualCart = $customerData['attr_virtual_cart'];
                    $virtualCart = $this->jsonHelper->jsonDecode($virtualCart, true);
                } else {
                    $virtualCart = '';
                }
            } else {
                $virtualCart = $this->guestCart->get();
                if (empty($virtualCart)) {
                    $virtualCart = $this->checkoutSession->getGuestVertualData($virtualCart);
                    $this->checkoutSession->setGuestVertualData(0);
                }

                $this->logDataInLogger("Helper_Data_getVirtualCart Data : ".json_encode($virtualCart));
                $virtualCart = $this->jsonHelper->jsonDecode($virtualCart, true);
            }
            
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getVirtualCart Exception : ".$e->getMessage()
            );
        }
        return $virtualCart;
    }

    /**
     * Function manageVirtualCart function
     *
     * @param array $virtualCart
     * @param int $attributeCode
     * @return mixed
     */
    public function manageVirtualCart($virtualCart, $attributeCode)
    {
        unset($virtualCart[$attributeCode]);
        return $virtualCart;
    }

    /**
     * Function checkEmptyVirtualCart checks array empty or not
     *
     * @param array $data [virtual cart]
     * @return boolean
     */
    public function checkEmptyVirtualCart($data)
    {
        try {
            if (is_array($data) && count($data) <= 0) {
                return true;
            } else {
                $flag =  false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_checkEmptyVirtualCart Exception : ".$e->getMessage()
            );
        }
        return $flag;
    }
    
    /**
     * Function validateVirtualCart
     *
     * @param array $virtualCart
     * @return array
     */
    public function validateVirtualCart($virtualCart)
    {
        try {
            foreach ($virtualCart as $attributeOptionId => $productArray) {
                foreach ($productArray as $productId => $itemInfo) {
                    if ($productId !== "grouped"
                        && array_key_exists('item_id', $itemInfo)
                        && $itemInfo['item_id'] == ""
                    ) {
                        unset($virtualCart[$attributeOptionId][$productId]);
                    } elseif ($productId == "grouped") {
                        $virtualCart = $this->updateVirtualCartForGroupedProduct(
                            $itemInfo,
                            $virtualCart,
                            $attributeOptionId
                        );
                    }
                    if (array_key_exists('grouped', $virtualCart[$attributeOptionId])
                        && empty($virtualCart[$attributeOptionId]['grouped'])
                    ) {
                        unset($virtualCart[$attributeOptionId]['grouped']);
                    }
                    $check = $this->checkEmptyVirtualCart($virtualCart[$attributeOptionId]);
                    if ($check) {
                        unset($virtualCart[$attributeOptionId]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_validateVirtualCart Exception : ".$e->getMessage()
            );
        }
        return $virtualCart;
    }

    /**
     * Function updateVirtualCartForGroupedProduct
     *
     * @param array $itemInfo
     * @param array $virtualCart
     * @param int $attributeOptionId
     * @return array
     */
    private function updateVirtualCartForGroupedProduct($itemInfo, $virtualCart, $attributeOptionId)
    {
        foreach ($itemInfo as $groupProId => $groupInner) {
            if (array_key_exists('item_id', $groupInner)
                && ($groupInner['item_id'] == "" || !array_key_exists('child', $groupInner))
            ) {
                unset($virtualCart[$attributeOptionId]['grouped'][$groupProId]);
            }
        }

        return $virtualCart;
    }

    /**
     * Function saveCart saves cart
     *
     * @return void
     */
    public function saveCart()
    {
        try {
            $this->cart->save();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_saveCart Exception : ".$e->getMessage()
            );
        }
    }
    
    /**
     * Function updateVirtualCart used to update virtual cart data
     *
     * @param int $attributeCode
     * @return void
     */
    public function updateVirtualCart($attributeCode)
    {
        try {
            $virtualCart = $this->getVirtualCart();
            if ($virtualCart
                && is_array($virtualCart)
                && $attributeCode !== null
                && $this->checkAttributesplitcartStatus()
            ) {
                $virtualCart = $this->manageVirtualCart($virtualCart, $attributeCode);
                $this->setAttributeVirtualCart($virtualCart);

                $quote   = $this->cart->getQuote();
                $itemIds = [];
                $proIds  = [];

                foreach ($quote->getAllVisibleItems() as $item) {
                    $itemIds[$item->getId()] = $item->getProductId();
                }

                if (!empty($virtualCart)
                    && is_array($virtualCart)
                    && $virtualCart !== ''
                    && $this->checkAttributesplitcartStatus()
                ) {
                    $addCart = $this->prepareDataForCart($virtualCart, $itemIds, $proIds);
                    if ($addCart) {
                        $this->saveCart();
                        $quote->setTotalsCollectedFlag(false)->collectTotals();
                        $quote->save();
                    }
                }
                $this->unsetCheckoutRemoveSession();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_updateVirtualCart Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Function unsetCheckoutRemoveSession used to unset value from checkout session
     *
     * @return void
     */
    public function unsetCheckoutRemoveSession()
    {
        try {
            $this->checkoutSession->unsWkRemoveItem();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_unsetCheckoutRemoveSession Exception : ".$e->getMessage()
            );
        }
    }
    
    /**
     * Function prepareDataForCart used to add product in cart
     *
     * @param array $virtualCart [contains virtual cart data of user]
     * @param array $itemIds     [contains item ids]
     * @param array $productIds  [contains product ids]
     * @return boolean
     */
    public function prepareDataForCart($virtualCart, $itemIds, $productIds)
    {
        try {
            $addCart = false;
            $this->groupParams = [];
            foreach ($virtualCart as $attributeOptionId => $productArray) {
                foreach ($productArray as $productId => $itemData) {
                    if ($productId !== "grouped") {
                        $addCart = $this->addProductToCart($itemData, $itemIds, $productId, $productIds);
                    } elseif ($productId == "grouped") {
                        foreach ($itemData as $groupProId => $groupInner) {
                            $addCart = $this->addProductToCart($groupInner, $itemIds, $groupProId, $productIds);
                        }
                    }
                }
            }
           
            if (!empty($this->groupParams)) {
                foreach ($this->groupParams as $proId => $params) {
                    $_product = $this->productRepository
                        ->getById($proId);
                    if ($_product) {
                        $this->logDataInLogger(
                            "params  : ",
                            $params
                        );
                        $this->cart->addProduct($_product, $params);
                        $addCart = true;
                    }
                }
            }
            return $addCart;
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_prepareDataForCart Exception : ".$e->getMessage()
            );
            return false;
        }
    }

    /**
     * Function addProductToCart
     *
     * @param array $itemData
     * @param array $itemIds
     * @param int $productId
     * @param array $productIds
     * @return boolean
     */
    public function addProductToCart($itemData, $itemIds, $productId, $productIds)
    {
        try {
            $flag = false;
            if ($itemData['item_id'] && (!array_key_exists($itemData['item_id'], $itemIds))
                && ((!in_array($productId, $itemIds)
                    || (in_array($productId, $itemIds) && array_search($productId, $itemIds)!==$itemData['item_id']))
                || array_key_exists('mpassignproduct_id', $itemData)
                || array_key_exists($productId, $productIds))
            ) {
                $params = [];
                $params['qty'] = $itemData['qty'];
                $params['product'] = $productId;
                if (array_key_exists('mpassignproduct_id', $itemData)) {
                    $params['mpassignproduct_id'] = $itemData[
                        'mpassignproduct_id'
                    ];
                }

                if (array_key_exists('child', $itemData) && $itemData['child']!=='') {
                    $attributes = $this->jsonHelper->jsonDecode($itemData['child'], true);
                    $params = array_merge($params, $attributes);
                }
                if (array_key_exists('bundle_options', $itemData) && $itemData['bundle_options']!=='') {
                    $bundleItemData = $this->jsonHelper->jsonDecode($itemData['bundle_options'], true);
                    $params = array_merge($params, $bundleItemData);
                }
                try {
                    if (array_key_exists("super_product_config", $params)
                        && array_key_exists("product_type", $params["super_product_config"])
                        && $params["super_product_config"]["product_type"] == "grouped"
                        && array_key_exists("product_id", $params["super_product_config"])
                        && $params["super_product_config"]["product_id"] !== $productId
                    ) {
                        $tempProId = $productId;
                        $tempQty = $params['qty'];
                        $params['super_group'][$tempProId] = $tempQty;
                        $productId = $params["super_product_config"]["product_id"];
                        $params['product'] = $productId;
                        unset($params['qty']);
                        unset($params['super_product_config']);

                        if (array_key_exists($productId, $this->groupParams)) {
                            $this->groupParams[$productId]['super_group'][$tempProId] = $tempQty;
                        } else {
                            $this->groupParams[$productId] = $params;
                        }
                    } else {
                        $storeId = $this->storeManager->getStore()->getId();
                        $_product = $this->product->create()->setStoreId($storeId)->load($productId);
                        if ($_product) {
                            $this->cart->addProduct($_product, $params);
                            $flag = true;
                        }
                    }
                } catch (\Exception $e) {
                    $this->logDataInLogger(
                        "Helper_Data_addProductToCart_inner Exception : ".$e->getMessage()
                    );
                }
            }
           
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_addProductToCart Exception : ".$e->getMessage()
            );
        }
        
        return $flag;
    }
    
    /**
     * Function removeCustomQuote
     *
     * @return void
     */
    public function removeCustomQuote()
    {
        try {
            $this->checkoutSession->unsWkCustomQuote();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_removeCustomQuote Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Function addVirtualCartToQuote used to add products in cart from virtual cart
     *
     * @return void
     */
    public function addVirtualCartToQuote()
    {
        try {
            $quote = $this->cart->getQuote();
            $virtualCart = $this->getVirtualCart();
            $itemIds = [];
            $proIds  = [];
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $itemIds[$item->getId()] = $item->getProductId();
                }
                try {
                    $this->cart->removeItem($item->getItemId())->save();
                } catch (\Exception $e) {
                    $this->logDataInLogger(
                        "Helper_Data_addVirtualCartToQuote_inner remove item Exception : ".$e->getMessage()
                    );
                }
            }
            
            if ($virtualCart
                && is_array($virtualCart)
                && $virtualCart !== ''
                && $this->checkAttributesplitcartStatus()
            ) {
                $addCart = $this->prepareDataForCart($virtualCart, $itemIds, $proIds);
                if ($addCart) {
                    $this->saveCart();
                    $this->logDataInLogger(
                        "get quotyes total after add cart : "
                        .json_encode($this->checkoutSession->getQuote()->getTotals())
                    );
                    $cartData = [];
                    $quote = $this->cart->getQuote();
                    $this->logDataInLogger(
                        "get quotyes total after add cart1 : ".json_encode($this->cart->getQuote())
                    );
                    foreach ($quote->getAllVisibleItems() as $item) {
                        $cartData[$item->getId()]['qty'] = $item->getQty();
                    }
                    
                    if (!empty($cartData)) {
                        $cartData = $this->cart->suggestItemsQty($cartData);
                        try {
                            $this->cart->updateItems($cartData)->save();
                        } catch (\Exception $e) {
                            $this->logDataInLogger(
                                "Helper_Data_addVirtualCartToQuote_inner Exception : ".$e->getMessage()
                            );
                        }
                    }
                }
            }
            $this->unsetCheckoutRemoveSession();
            $this->updateCart();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_addVirtualCartToQuote Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Function updateCart
     *
     * @return void
     */
    public function updateCart()
    {
        try {
            $quote = $this->cart->getQuote();
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->save();
            $this->setWkCartWasUpdated();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_updateCart Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Function setWkCartWasUpdated used to set cart was updated true
     *
     * @return void
     */
    public function setWkCartWasUpdated()
    {
        try {
            $this->checkoutSession->setCartWasUpdated(true);
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_setWkCartWasUpdated Exception : ".$e->getMessage()
            );
        }
    }
    
    /**
     * Function checkSplitCart
     *
     * @return array $attributeOptions
     */
    public function checkSplitCart()
    {
        try {
            $quote = $this->cart->getQuote();
            $attributeName = $this->getSelectedAttribute();
            $attributeOptions = [];

            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $product = $item->getProduct();
                    $attributeValue = $product->getData($attributeName);
                    $attributeOptions[] = $attributeValue;
                }
            }
            $attributeOptions = array_unique($attributeOptions);
            
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_checkSplitCart Exception : ".$e->getMessage()
            );
        }
        return $attributeOptions;
    }

     /**
      * Function logDataInLogger
      *
      * @param string $data
      * @return void
      */
    public function guestCartDelete()
    {
        $this->guestCart->delete();
        $this->guestCart->set(0, 3600);
    }
    
    /**
     * Function logDataInLogger
     *
     * @param string $data
     * @return void
     */
    public function logDataInLogger($data)
    {
        $this->attrBaseLogger->info($data);
    }
}
