<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Plugin\ConfigurableProduct\Pricing;

use Magento\Store\Model\StoreManagerInterface;
use Bss\CustomPricing\Model\ResourceModel\Indexer\Resolver\ConfigurablePrice;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;


/**
 * Class ConfigurablePriceResolver
 *
 * @package Bss\CustomPricing\Plugin\ConfigurableProduct\Pricing
 */
class ConfigurablePriceResolver
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

    /**
     * Modify data if customer apply rule. customer_group, customer_logged_in, rule_id
     *
     * @param \Magento\Framework\App\Http\Context $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolvePrice(
        $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $product
    ) {
        $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
        $parentProductId = $product->getId();

        $priceText = $this->getPriceRange($product);


        if ($this->helperData->isEnabled()) {
            $ruleIds = $this->helperRule->getAppliedRules(null, false);
            if ($ruleIds) {
                $minCustomPriceRule = $this->configurablePrice->getMinPrice($ruleIds, $parentProductId, $storeId);
                if ($minCustomPriceRule) {

                    if($priceText < $minCustomPriceRule &&  $priceText > 0){
                        return $priceText;
                    } else {
                        return $minCustomPriceRule;
                    }

                }
            }
        }

        return $result;
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
