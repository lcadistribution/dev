<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Plugin;

use Magento\Sales\Model\AdminOrder\Create as OrderCreate;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ChangeTierPriceInfo
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
     * @var use \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var use \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var use ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ChangeTierPriceInfo constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OrderCreate $orderCreate
     * @param \Magento\Framework\App\State $state
     * @param CustomerpriceRepositoryInterface $customerPriceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderCreate $orderCreate,
        \Magento\Framework\App\State $state,
        CustomerpriceRepositoryInterface $customerPriceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $httpContext,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->orderCreate = $orderCreate;
        $this->state = $state;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request = $request;
        $this->httpContext = $httpContext;
        $this->productRepository = $productRepository;
    }

    public function beforeGetValue($subject)
    {
        
        if ($this->helper->isCustomerPriceAllow()) {
            $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
            if ($isLoggedIn) {
                if ((in_array($this->request->getFullActionName(), $this->helper->getActionArray())) && $this->helper->getConfig('customerprice/general/hide_price')) {
                } elseif ((in_array($this->request->getFullActionName(), $this->helper->getActionArray())) && $this->helper->getConfig('customerprice/general/ajax_price') && !$this->helper->getConfig('customerprice/general/hide_price')) {
                } else {
                    $product = $subject->getProduct();
                    if ($product->getTypeId() == 'configurable') {
                        foreach ($product->getTypeInstance()->getUsedProducts($product) as $child) {
                            $this->setNewTierPrice($child);
                        }
                    } else {
                        $this->setNewTierPrice($product);
                    }
                }
            }
        }
    }

    /**
     * @param $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setNewTierPrice($product)
    {
        $productRepo = $this->productRepository->getById($product->getId());
        $res = [];
        $newArray = [];
        if ($this->state->getAreaCode() == 'adminhtml') {
            $customerId = $this->orderCreate->getQuote()->getCustomer()->getId();
        } else {
            $customerId = $this->customerSession->create()->getCustomer()->getId();
        }
        
        if ($customerId) {
            $websiteId = $this->getCurrentWebsiteId();
            $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
            $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
            $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
            $tierPriceCollection = $this->customerPriceRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            if ($tierPriceCollection) {
                if ($this->state->getAreaCode() == 'adminhtml') {
                    $groupId = $this->orderCreate->getQuote()->getCustomer()->getGroupId();
                } else {
                    $groupId = $this->customerSession->create()->getCustomerGroupId();
                }
                
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
                    $newTier = array_merge($newArray, $productRepo->getTierPrice());
                    $price = array_column($newTier, 'price');
                    array_multisort($price, SORT_DESC, $newTier);
                    $finalTierPrice = $this->uniqueArray($newTier, 'price_qty');
                    $product->setData('tier_price', $finalTierPrice);
                }
            }
        }
    }

    /**
     * @return int
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
