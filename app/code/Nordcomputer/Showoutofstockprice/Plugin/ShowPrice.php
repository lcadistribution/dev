<?php
namespace Nordcomputer\Showoutofstockprice\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\ConfigurablePrice;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;


class ShowPrice
{

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    private $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    private $helperRule;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreManagerInterface
     */
    private $configurablePrice;

    protected $configurableProduct;

    protected $pricingHelper;


    public function __construct(
        Configurable $configurableProduct,
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        StoreManagerInterface $storeManager,
        ConfigurablePrice $configurablePrice,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
        $this->storeManager = $storeManager;
        $this->configurablePrice = $configurablePrice;
        $this->pricingHelper = $pricingHelper;
    }

    public function afterResolvePrice(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver $subject,
        $price,
        $product
    ) {
        $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
        $parentProductId = $product->getId();

        $priceText = $this->getPriceRange($product);


        if ($this->helperData->isEnabled()) {


            $ruleIds = $this->helperRule->getAppliedRules(null, false);
            if ($ruleIds) {

                $minCustomPriceRule = $this->configurablePrice->getMinPriceAlter($ruleIds, $parentProductId, $storeId);
                if ($minCustomPriceRule) {

                    if($priceText < $minCustomPriceRule &&  $priceText > 0){
                        $product->setSpecialPrice($priceText);
                        $product->setBssCustomPrice(true);
                        return $priceText;

                    } else {
                        $product->setSpecialPrice($minCustomPriceRule);
                        $product->setBssCustomPrice(true);
                        return $minCustomPriceRule;

                    }

                }
            }
        }

        $price = $price ?: $product->getData('price');

        return $price;


    }


    public function getPriceRange($product)

    {

        $childProductPrice = [];

        $childProducts = $this->configurableProduct->getUsedProducts($product);

        $childProductPrice = array();

        foreach($childProducts as $child) {


            $priceInfo = $child->getPriceInfo();
            $prices = $priceInfo->getPrice('final_price')->getAmount()->getValue();


            $price = $child->getPrice();

            $finalPrice = $child->getFinalPrice();

            if($price == $finalPrice) {

                $childProductPrice[] = $price;

            } else if($finalPrice < $price) {

                $childProductPrice[] = $finalPrice;

            }
            $childProductPrice[] =$prices;
        }



        if(!empty($childProductPrice)){

            $max = max($childProductPrice);

            $min = min($childProductPrice);

            if($min==$max){

                return $max;

            } else {

                return  $min;

            }

        }

    }




}
