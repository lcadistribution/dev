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
namespace Webkul\AttrBaseSplitcart\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Checkout\Model\Cart;
use Webkul\AttrBaseSplitcart\Helper\Data as AttrBaseSplitcartHelper;

/**
 * AttrBaseSplitcart Block
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartModel;

    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data as AttrBaseSplitcartHelper
     */
    protected $helper;

    /**
     * @var \Webkul\AttrBaseSplitcart\Helper\Data as AttrBaseSplitcartHelper
     */
    protected $priceCurrency;
    /**
     * @var \Magento\Directory\Block\Data
     */
    protected $directoryBlock;
     /**
      * @var \Magento\Shipping\Model\Config
      */
    protected $shippingmodelconfig;
     /**
      * @var \Magento\Framework\App\Config\ScopeConfigInterface
      */
    protected $scopeConfig;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $http;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeConfig;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $priceHelper
     * @param ProductRepository $productRepository
     * @param ProductFactory $productFactory
     * @param ItemFactory $quoteItemFactory
     * @param Cart $cart
     * @param AttrBaseSplitcartHelper $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeConfig
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Block\Data $directoryBlock
     * @param \Magento\Shipping\Model\Config $shippingmodelconfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Response\Http $http
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $priceHelper,
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        ItemFactory $quoteItemFactory,
        Cart $cart,
        AttrBaseSplitcartHelper $helper,
        \Magento\Store\Model\StoreManagerInterface $storeConfig,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Shipping\Model\Config $shippingmodelconfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Response\Http $http,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceHelper = $priceHelper;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->cartModel = $cart;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->directoryBlock = $directoryBlock;
        $this->shippingmodelconfig = $shippingmodelconfig;
        $this->scopeConfig = $scopeConfig;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->urlInterface = $urlInterface;
        $this->http = $http;
        $this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
    }

    /**
     * GetCartDataByAttribute show items at shopping cart accr. to attribute
     *
     * @return [array]
     */
    public function getCartDataByAttribute()
    {
        try {
            $cartArray = [];
            $count = 0;
            $cart = $this->cartModel->getQuote();
            $cart->collectTotals();
            $attrCode = $this->helper->getSelectedAttribute();
            foreach ($cart->getAllItems() as $item) {
                $productType= $item->getProduct()->getTypeId();
                if (!$item->hasParentItemId()) {
                    if ($item->getRowTotal() == null) {
                        $this->getRedirect();
                    }
                    $product = $item->getProduct();
                    $productTaxClassId = $product->getTaxClassId();
                    $count += $item->getQty();
                    if ($productType =='configurable') {
                        $itemId =  $item->getItemId();
                        $quoteId =  $item->getQuoteId();
                        $productId = $item->getProduct()->getEntityId();
                        $quoteItem = $this->quoteItemFactory->create()->getCollection()
                        ->addFieldToFilter('quote_id', ['eq'=>$quoteId])
                        ->addFieldToFilter('parent_item_id', ['eq'=>$itemId])
                        ->getFirstItem();
                        $productSku = $this->productFactory->create()->load($quoteItem->getProductId())->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attrValueId = $product->getData($attrCode);
                    } else {
                        $productId= $item->getProduct()->getEntityId();
                        $productSku = $this->productFactory->create()->load($productId)->getSku();
                        $product = $this->productRepository->get($productSku);
                        $attrValueId = $product->getData($attrCode);
                    }

                    if ($attrValueId===0) {
                        $attrValueId = 0;
                    }
                    if ($attrValueId=="") {
                        $attrValueId = -1;
                    }
                
                    $price =  $item->getRowTotal();
                    $discount = $item->getDiscountAmount();
                    $quoteId =  $item->getQuoteId();
                    if ($this->helper->getCatalogPriceIncludingTax()) {
                        $price = $item->getRowTotalInclTax();
                    }
                    if (!isset($cartArray[$attrValueId]['attr_detail'])) {
                        $cartArray[$attrValueId]['item_count'] = $item->getQty();
                    } else {
                        $cartArray[$attrValueId]['item_count'] = $cartArray[$attrValueId]['item_count']+$item->getQty();
                    }
                    $formattedPrice = $this->priceCurrency->format($price, true, 2);
                    if (!isset($cartArray[$attrValueId]['attr_detail'])) {
                        $attrDetail = $this->helper->getAttributeValue($attrCode, $attrValueId, $productType);
                        $cartArray[$attrValueId]['attr_detail'] = $attrDetail;
                    }
                    $cartArray[$attrValueId][$item->getId()] = $formattedPrice;

                    if (!isset($cartArray[$attrValueId]['total'])
                        || $cartArray[$attrValueId]['total']==null
                    ) {
                        $cartArray[$attrValueId]['total'] = $price;
                    } else {
                        $cartArray[$attrValueId]['total'] += $price;
                    }
                    $cartArray[$attrValueId]['row_total'] = $this->priceCurrency
                        ->format($cartArray[$attrValueId]['total'], true, 2);
                    $totalWithoutFormat = $cartArray[$attrValueId]['total'];
                    if (isset($discount)) {
                        if ($discount > 0) {
                            $formattedPrice = $this->priceCurrency
                                ->format($cartArray[$attrValueId]['total'] - $discount, true, 2);
                            $totalWithoutFormat = $cartArray[$attrValueId]['total'] - $discount;
                            $cartArray[$attrValueId]['dis_amount'] = $this->priceCurrency->format($discount);
                        } else {
                            $formattedPrice = $this->priceCurrency->format($cartArray[$attrValueId]['total'], true, 2);
                            $totalWithoutFormat = $cartArray[$attrValueId]['total'];
                        }
                    }
                    $cartArray[$attrValueId]['discount_amount'] = $this->priceCurrency->format(-$discount, true, 2);
                    $cartArray[$attrValueId]['formatted_total'] = $formattedPrice;
                    $cartArray[$attrValueId]['totalWithoutFormat'] = $totalWithoutFormat;
                    $cartArray[$attrValueId]['productTaxClassId'] = $productTaxClassId;
                }
            }
            $cartArray['totalitemscount'] = $count;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Index getCartDataByAttribute : ".$e->getMessage());
        }
        return $cartArray;
    }

    /**
     * [getMpsplitcartEnable get splitcart is enable or not]
     *
     * @return void
     */
    public function getAttributesplitcartEnable()
    {
        try {
            return $this->helper->checkAttributesplitcartStatus();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Index getMpsplitcartEnable : " . $e->getMessage()
            );
        }
    }

    /**
     * Get MultiShipping Checkout URL
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl("multishipping/checkout", ["_secure" => true]);
    }

    /**
     * [getSelectedAttribute get selected attribute]
     *
     * @return void
     */
    public function getSelectedAttribute()
    {
        try {
            return $this->helper->getSelectedAttribute();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Index getMpsplitcartEnable : " . $e->getMessage()
            );
        }
    }

    /**
     * Get Countries function
     *
     * @return array
     */
    public function getCountries()
    {
        $country = $this->directoryBlock->getCountryHtmlSelect();
        return $country;
    }

    /**
     * Get Region function
     *
     * @return array
     */
    public function getRegion()
    {
        $region = $this->directoryBlock->getRegionHtmlSelect();
        return $region;
    }

    /**
     * GetActive Shipping Method
     *
     * @return array
     */
    public function getActiveShippingMethod()
    {
        $shippings = $this->shippingmodelconfig->getActiveCarriers();
        $methods = [];
        foreach ($shippings as $shippingCode => $shippingModel) {
            if ($carrierMethods = $shippingModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $shippingCode . "_" . $methodCode;
                    $carrierTitle = $this->scopeConfig->getValue(
                        "carriers/" . $shippingCode . "/title"
                    );
                    $methods[] = ["value" => $code, "label" => $carrierTitle];
                }
            }
        }
        return $methods;
    }

    /**
     * Get Country Action function
     *
     * @return string
     */
    public function getCountryAction()
    {
        return $this->getUrl("attrbasesplitcart/cartover/country", [
            "_secure" => true,
        ]);
    }

    /**
     * Get Address Data function
     *
     * @return object
     */
    public function getAddressData()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return -1;
        }
        $customer = $this->customerRepository->getById($customerId);
        $shippingAddressId = $customer->getDefaultShipping();
        try {
            $shippingAddress = $this->addressRepository->getById($shippingAddressId);
        } catch (\Throwable $th) {
            return 0;
        }
        return $shippingAddress;
    }

    /**
     * Get Flate Rate Type
     *
     * @return string
     */
    public function getFlateRateType()
    {
        $type = $this->scopeConfig->getValue(
            'carriers/flatrate/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $type;
    }

    /**
     * Get Redirect
     */
    public function getRedirect()
    {
        $this->http->setRedirect($this->urlInterface->getUrl('checkout/cart'));
    }

    /**
     * Get Symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }

    /**
     * Get Current Currency Rate
     *
     * @return string
     */
    public function getCurrentCurrencyRate()
    {
        return $this->storeConfig->getStore()->getCurrentCurrencyRate();
    }
}
