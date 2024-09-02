<?php

namespace Magedelight\Customerprice\Plugin;

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

use Magento\Sales\Model\AdminOrder\Create  as OrderCreate;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product;

class CartTierPrice
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var OrderCreate
     */
    private $orderCreate;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var CustomerpriceRepositoryInterface
     */
    private $customerPriceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    /**
     * ChangeTierPrice constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OrderCreate $orderCreate
     * @param \Magento\Framework\App\State $state
     * @param CustomerpriceRepositoryInterface $customerPriceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderCreate $orderCreate,
        \Magento\Framework\App\State $state,
        CustomerpriceRepositoryInterface $customerPriceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->orderCreate = $orderCreate;
        $this->state = $state;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $subject
     * @param float|null $qty
     * @param Product $product
     */
    public function beforeGetFinalPrice(\Magento\Catalog\Model\Product\Type\Price $subject, $qty, $product)
    {
        
        if ($this->helper->isCustomerPriceAllow()) {
            if ($product->getTypeId() == 'configurable') {
                if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
                    /** @var Product $simpleProduct */
                    $simpleProduct = $product->getCustomOption('simple_product')->getProduct();
                    $simpleProduct->setCustomerGroupId($product->getCustomerGroupId());
                    $this->setNewTierPrice($simpleProduct);
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
     * @param $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setNewTierPrice($product)
    {
        $res = [];
        $newArray = [];
        if ($this->state->getAreaCode() == 'adminhtml') {
            $customerId = $this->orderCreate->getQuote()->getCustomer()->getId();
        } else {
            $customerId = $this->customerSession->create()->getCustomer()->getId();
        }
        if ($customerId) {
            $websiteId = $this->getCurrentWebsiteId();
            // Tier Price Collection For Customer Price
            $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
            $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
            $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
            $tierPriceCollection = $this->customerPriceRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            // Create an array of customer tier price
            if ($tierPriceCollection) {
                if ($this->state->getAreaCode() == 'adminhtml') {
                    $groupId = $this->orderCreate->getQuote()->getCustomer()->getGroupId();
                } else {
                    $groupId = $this->customerSession->create()->getCustomerGroupId();
                }
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
}
