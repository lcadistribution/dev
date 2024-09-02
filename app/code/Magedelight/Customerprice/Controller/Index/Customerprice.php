<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Customerprice\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Customerprice extends Action
{

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @var use Data
     */
    private $helper;

    /**
     * @var use ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var use \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var use \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var use CustomerpriceRepositoryInterface
     */
    private $customerPriceRepository;

    /**
     * @var use SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

     /**
      * @var PriceCurrencyInterface
      */
    private $priceCurrency;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        SessionFactory $customerSession,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerpriceRepositoryInterface $customerPriceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
        $this->_storeManager = $storeManager;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return array
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $response = [];
        if ($this->helper->isCustomerPriceAllow()) {
            $data = $this->getRequest()->getPostValue();
            $productId = isset($data['product_id']) ? $data['product_id'] : null;

            if ($productId) {
                try {
                    $mainProduct = $this->productRepository->getById($productId);

                    $date = $this->customerPriceRepository->getCustomerPriceValidDate($mainProduct->getId(), $this->helper->getUserId(), $this->helper->getCurrentWebsiteId());
            
                    $date = ($date) ? ' (valid till <span>'.$date.'</span>)' : "";

                    if ($mainProduct->getTypeId() == 'configurable') {
                        $finalPriceAmt = $mainProduct->getPriceInfo()->getPrice('final_price')->getValue();
                        $priceHtml = '<p><span class="price-container"><span class="price-label">'.__("As low as").'</span> <span class="price ">'.$this->priceCurrency->format($finalPriceAmt).'</span></span></p>';
                    } elseif ($mainProduct->getTypeId()=='grouped') {
                        $finalPriceAmt = $mainProduct->getPriceInfo()->getPrice('final_price')->getValue();
                        $priceHtml = '<p><span class="price-container"><span class="price-label">'.__("Starting at").'</span> <span class="price">'.$this->priceCurrency->format($finalPriceAmt).'</span></span></p>';
                    } elseif ($mainProduct->getTypeId()=='bundle') {
                        $minimalFinalPriceAmt = $mainProduct->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                        if ($mainProduct->getPriceView()) {
                            $priceHtml = '<p><span class="price-container"><span class="price-label">'.__("As low as").'</span> <span class="price ">'.$this->priceCurrency->format($minimalFinalPriceAmt).'</span></span></p>';
                        } else {
                            $maximalFinalPriceAmt = $mainProduct->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();
                            $priceHtml = '<p class="price-from"><span class="price-container"><span class="price-label">'.__("From").'</span> <span class="price-wrapper "><span class="price">'.$this->priceCurrency->format($minimalFinalPriceAmt).'</span></span></span></p>';
                            $priceHtml .= '<p class="price-to"><span class="price-container"><span class="price-label">'.__("To").'</span> <span class="price-wrapper "><span class="price">'.$this->priceCurrency->format($maximalFinalPriceAmt).'</span></span></span></p>';
                        }
                    } else {
                        $finalPriceAmt = $mainProduct->getPriceInfo()->getPrice('final_price')->getValue();
                        $tierPrice = $this->setNewTierPrice($mainProduct);
                        $priceHtml = '<span class="price-container"><span class="price-wrapper "><span class="price">'.$this->priceCurrency->format($finalPriceAmt).'</span></span></span>';
                        if ($tierPrice) {
                            $formattedPrice = null;
                            foreach ($tierPrice as $key => $value) {
                                if ($value['price_qty']>1) {
                                    $formattedPrice = $this->priceHelper->currency($value['price'], true, false);
                                }
                            }
                            if ($formattedPrice) {
                                $priceHtml .= '<p><span class="price-container"><span class="price-label">'.__("As low as").'</span> <span class="price-wrapper ">'.$formattedPrice.'</span></span></p>';
                            }
                        }
                    }
                    $priceHtml = $priceHtml.'<span>'.$date.'</span>';
                    $response = ['status' => 1, 'message' => $priceHtml];
                                        
                } catch (Exception $e) {
                    $response = ['status' => 0, 'message' => $e->getMessage()];
                }
            }
        }
        $result->setData($response);
        return $result;
    }


    /**
     * @param $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setNewTierPrice($product)
    {

        $res = [];
        $newArray = [];
        $customerId = $this->customerSession->create()->getCustomer()->getId();
        
        if ($customerId) {
            $websiteId = $this->getCurrentWebsiteId();
            $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
            $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
            $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
            $tierPriceCollection = $this->customerPriceRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            if ($tierPriceCollection) {
                $groupId = $this->customerSession->create()->getCustomerGroupId();
                foreach ($tierPriceCollection as $price) {
                    $tierPrice = $product->getData('final_price');
                    if (isset($tierPrice) && !is_array($tierPrice)) {
                        if ($price['qty']==1 && ((float)$price['new_price'] > (float)$tierPrice)) {
                            $price['new_price'] = $tierPrice;
                        }
                    }

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
                
                if (!empty($newArray)) {
                    $newTier = array_merge($newArray, $product->getTierPrice());
                    $price = array_column($newTier, 'price');
                    array_multisort($price, SORT_DESC, $newTier);
                    $finalTierPrice = $this->uniqueArray($newTier, 'price_qty');
                    //$product->setData('tier_price', $finalTierPrice);
                    return $finalTierPrice;
                }
            }
        }
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
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
}
