<?php

namespace Magedelight\Customerprice\Plugin\WebapiRest;

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface as DiscountModel;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface as CategoryDiscountModel;

class ChangeTierPrice
{
    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var CustomerpriceRepositoryInterface
     */
    private $customerPriceRepository;

     /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DiscountModel
     */
    protected $discountModel;

    /**
     * @var CategoryDiscountModel
     */
    protected $categoryDiscountModel;

    

    /**
     * ChangeTierPrice constructor.
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerpriceRepositoryInterface $customerPriceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CalculatorInterface $categoryDiscountModel
     * @param DiscountModel $discountModel
     */
    public function __construct(
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerpriceRepositoryInterface $customerPriceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryDiscountModel $categoryDiscountModel,
        DiscountModel $discountModel
    ) {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->discountModel = $discountModel;
        $this->categoryDiscountModel = $categoryDiscountModel;
    }

    /**
     * @param $subject
     * @param null $qty
     */
    public function beforeGetFinalPrice($subject, $qty = null)
    {   
       
        if ($this->helper->isEnabled()){
            // Retrieve the authorization header from the HTTP request
            if ($subject->getTypeId() == 'configurable'){
                foreach ($subject->getTypeInstance()->getUsedProducts($subject) as $child){
                    $this->setNewTierPrice($child);
                }
            }else{
                $this->setNewTierPrice($subject);
            }
        }
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentCustomer()
    {
        $customerArray = [];
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $baseUrl.'rest/V1/customers/me',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: '.$authorizationHeader.''
        ),
      ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response,true);

        if (isset($response['id']) && $response['id']) {
            $customerArray = ['cid' => $response['id'], 'group_id'=>$response['group_id'],'website_id'=>$response['website_id']];

        }

        return $customerArray;
    }

    /**
     * @param $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setNewTierPrice($product)
    {
        $res = [];
        $newArray = [];
        $customerArray = $this->getCurrentCustomer();
        $customerId = isset($customerArray['cid']) ? $customerArray['cid'] : NULL;
        $resultPrice = 0.00;
        $discount = 0.00;
        $tierPrice = 0.00;
        if ($customerId) {

            $discount = $this->calculateMaximumDiscount($customerId, $product->getCategoryIds());
            foreach ($product->getTierPrice() as $price) {
                if ($price['price_qty'] == 1.0000) {
                    $tierPrice = $price['price'];
                } 
            }

            if ($this->helper->isAdvanced()) {
                if ($discount > 0.00 && $tierPrice == 0.00) {
                    $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
                    $resultPrice = $price - (($price * $discount)/100);
                    $product->setPrice($resultPrice);
                }elseif ($tierPrice > 0.00) {
                    $price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
                    $resultPrice = $price - (($price * $discount)/100);
                    $product->setPrice($resultPrice);
                }
            }else{
                if ($discount > 0.00 && $tierPrice == 0.00) {
                    $price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
                    $resultPrice = $price - (($price * $discount)/100);
                    $product->setPrice($resultPrice);
                }elseif ($tierPrice > 0.00) {
                    $price = $product->getPriceInfo()->getPrice('regular_price')->getValue();
                    $resultPrice = $price - (($price * $discount)/100);
                    $product->setPrice($resultPrice);
                }
            }
            
            $websiteId = $customerArray['website_id'];
            // Tier Price Collection For Customer Price
            $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
            $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
            $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
            $tierPriceCollection = $this->customerPriceRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            // Create an array of customer tier price
            if ($tierPriceCollection) {
                $groupId = $customerArray['group_id'];
                foreach ($tierPriceCollection as $price) {
                    $res['website_id'] = $price['website_id'];
                    $res['all_groups'] = 0;
                    $res['cust_group'] = $groupId;
                    $res['price'] = (float)$price['new_price'];
                    $res['price_qty'] = (float)$price['qty'] * 1;
                    $res['website_price'] = (float)$price['new_price'];
                    $res['value'] = (float)$price['new_price'];
                    $res['percentage_value'] = null;
                    $res['product_id'] = $product->getId();
                    $newArray[] = $res;
                }

                // Merge Group Price Or Customer Price
                $newTier = array_merge($newArray, $product->getTierPrice());
                $price = array_column($newTier, 'price');
                array_multisort($price, SORT_DESC, $newTier);

                // Create unique array for group and customer price
                $finalTierPrice = $this->uniqueArray($newTier, 'price_qty');
                // Set New Tier Price
                $product->setData('tier_price', $finalTierPrice);
            }
        }
    }

    /**
     * @param $arry
     * @param string
     * @return array
     */
    private function uniqueArray($array, $key)
    {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            } else {
                $k = array_search($val[$key], $key_array);
                if ($val['price'] < $temp_array[$k]['price']) {
                    //unset($temp_array[$k]);
                    $temp_array[$k] = $val;
                }
            }

            $i++;
        }

        $temp_array1 = [];
        $j = 0;
        $l = 0;
        
        foreach ($temp_array as $val) {
            if (empty($temp_array1)) {
                $temp_array1[$j]=$val;
            } elseif ($val['price'] < $temp_array1[$l]['price'] && $val['price_qty'] < $temp_array1[$l]['price_qty']) {
                unset($temp_array1[$l]);
                $temp_array1[$l] = $val;
            } else {
                $l++;
                $temp_array1[$l] = $val;
                
            }
            $j++;
        }

        return $temp_array1;
    }

    private function calculateMaximumDiscount($customerId, $categoryIds)
    {
        $categoryDiscount = $this->getCategoryDiscount($customerId, $categoryIds);
        $globalDiscount = $this->getDiscountByCustomerId($customerId);
        return max($categoryDiscount, $globalDiscount);
    }

    private function getCategoryDiscount($customerId, $categoryIds)
    {
        $categoryDiscounts = $this->categoryDiscountModel
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('category_id', ['in' => $categoryIds])
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($categoryDiscounts as $categoryDiscount) {
            $discountArray[] = $categoryDiscount->getDiscount();
        }
        if (!empty($discountArray)) {
            $maxCategoryDiscount = max($discountArray);
            return $maxCategoryDiscount;
        } else {
            return (int)0;
        }
    }

    private function getDiscountByCustomerId($customerId)
    {
        $discount = $this->discountModel->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->getFirstItem();
        if (!empty($discount)) {
            return $discount->getValue();
        } else {
            return (int)0;
        }
    }
}
