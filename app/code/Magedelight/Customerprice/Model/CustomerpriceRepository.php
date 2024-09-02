<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceInterfaceFactory;
use Magedelight\Customerprice\Api\Data\CustomerpriceSearchResultsInterfaceFactory;
use Magedelight\Customerprice\Model\ResourceModel\Customerprice as ResourceCustomerprice;
use Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory as CustomerpriceCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Customerprice\Model\Calculation\Calculator\GlobalDiscountCalculator;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface as CategoryDiscountModel;

class CustomerpriceRepository implements CustomerpriceRepositoryInterface
{

    /**
     * @var CustomerpriceCollectionFactory
     */
    protected $customerpriceCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCustomerprice
     */
    protected $resource;

    /**
     * @var CustomerpriceInterfaceFactory
     */
    protected $customerpriceFactory;

    /**
     * @var CustomerpriceSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var GlobalDiscountCalculator
     */
    protected $catalogPriceCalculator;

    /**
     * @var CategoryDiscountModel
     */
    protected $categoryDiscountModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    private $catFlag = false;


    /**
     * @param ResourceCustomerprice $resource
     * @param CustomerpriceInterfaceFactory $customerpriceFactory
     * @param CustomerpriceCollectionFactory $customerpriceCollectionFactory
     * @param CustomerpriceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ProductRepositoryInterface $productRepository
     * @param GlobalDiscountCalculator $catalogPriceCalculator
     * @param CategoryDiscountModel $categoryDiscountModel
     */
    public function __construct(
        ResourceCustomerprice $resource,
        CustomerpriceInterfaceFactory $customerpriceFactory,
        CustomerpriceCollectionFactory $customerpriceCollectionFactory,
        CustomerpriceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magedelight\Customerprice\Helper\Data $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ProductRepositoryInterface $productRepository,
        GlobalDiscountCalculator $catalogPriceCalculator,
        CategoryDiscountModel $categoryDiscountModel,
        \Magento\Catalog\Model\ProductFactory $productModel
    ) {
        $this->resource = $resource;
        $this->customerpriceFactory = $customerpriceFactory;
        $this->customerpriceCollectionFactory = $customerpriceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->productRepository = $productRepository;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
        $this->categoryDiscountModel = $categoryDiscountModel;
        $this->productModel = $productModel;
    }

    /**
     * @inheritDoc
     */
    public function save(CustomerpriceInterface $customerprice)
    {
        try {

            $newPrice = $customerprice->getNewPrice();
            $customerprice->setLogPrice($newPrice);
            $product = $this->productModel->create()->load($customerprice->getProductId());
            preg_match('/(.*)%/', $newPrice, $matches);
            if (is_array($matches) && count($matches) > 0) {
                $newPrice = $product->getPrice() - ($product->getPrice() * ($matches[1] / 100));
                $customerprice->setNewPrice($newPrice);
            }
            $this->resource->save($customerprice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerprice: %1',
                $exception->getMessage()
            ));
        }
        return $customerprice;
    }

    /**
     * @inheritDoc
     */
    public function get($customerpriceId)
    {
        $customerprice = $this->customerpriceFactory->create();
        $this->resource->load($customerprice, $customerpriceId);
        if (!$customerprice->getId()) {
            throw new NoSuchEntityException(__('Customerprice with id "%1" does not exist.', $customerpriceId));
        }
        return $customerprice;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerpriceCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CustomerpriceInterface $customerprice)
    {
        try {
            $customerpriceModel = $this->customerpriceFactory->create();
            $this->resource->load($customerpriceModel, $customerprice->getCustomerpriceId());
            $this->resource->delete($customerpriceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Customerprice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customerpriceId)
    {
        return $this->delete($this->get($customerpriceId));
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice($productId, $customerId, $websiteId)
    {
        $res = [];
        $newArray = [];
        $finalTierPrice = [];
        $flag = false;

        if ($this->helper->isEnabled()) {
            try {
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                $customerGroupId = $customer->getGroupId();
                $product = $this->productRepository->getById($productId);
                $oldFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $discount = $this->catalogPriceCalculator->calculate($product->getPrice(), $product, $customerId);
                $websiteId = 0;
                $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
                $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
                $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
                $tierPriceCollection = $this->getList($this->searchCriteriaBuilder->create())->getItems();

                if ($discount) {
                    $discount = min($oldFinalPrice, $discount);
                } else {
                    $discount = $oldFinalPrice;
                }
                if ($tierPriceCollection) {
                    foreach ($tierPriceCollection as $price) {
                        if ($discount) {
                            if ($price['qty']==1) {
                                $flag = true;
                                if ((float)$discount < (float)$price['new_price']) {
                                    $price['new_price'] = $discount;
                                }
                            }
                        }
                         
                        $res['website_id'] = $websiteId;
                        $res['all_groups'] = 0;
                        $res['cust_group'] = $customerGroupId;
                        $res['price'] = (float)$price['new_price'];
                        $res['price_qty'] = (float)$price['qty'] * 1;
                        $res['website_price'] = (float)$price['new_price'];
                        $res['value'] = (float)$price['new_price'];
                        $res['percentage_value'] = null;
                        $res['product_id'] = $product->getId();
                        $newArray[] = $res;
                    }

                    if (!$flag) {
                        $res['website_id'] = $websiteId;
                        $res['all_groups'] = 0;
                        $res['cust_group'] = $customerGroupId;
                        $res['price'] = $discount;
                        $res['price_qty'] = (float)1;
                        $res['website_price'] = $discount;
                        $res['value'] = $discount;
                        $res['percentage_value'] = null;
                        $res['product_id'] = $product->getId();
                        $newArray[] = $res;
                    }

                    $newTier = array_merge($newArray, $product->getData('tier_price'));
                    $price = array_column($newTier, 'price');
                    array_multisort($price, SORT_DESC, $newTier);
                    $finalTierPrice = $this->uniqueArray($newTier, 'price_qty', $customerGroupId);
                } else {
                    $res['website_id'] = $websiteId;
                    $res['all_groups'] = 0;
                    $res['cust_group'] = $customerGroupId;
                    $res['price'] = $discount;
                    $res['price_qty'] = (float)1;
                    $res['website_price'] = $discount;
                    $res['value'] = $discount;
                    $res['percentage_value'] = null;
                    $res['product_id'] = $product->getId();
                    $newArray[] = $res;
                    $newTier = array_merge($newArray, $product->getData('tier_price'));
                    $price = array_column($newTier, 'price');
                    array_multisort($price, SORT_DESC, $newTier);
                    $finalTierPrice = $this->uniqueArray($newTier, 'price_qty', $customerGroupId);
                }
            } catch (Exception $e) {
                $finalTierPrice = ['error'=>"Something went to wrong."];
            }
        }
        return $finalTierPrice;
    }

    /**
     * @param $arry
     * @param string
     * @return array
     */
    private function uniqueArray($array, $key, $customerGroupId = null)
    {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        foreach ($array as $val) {

            $priceCustomerGroupId = isset($val['cust_group']) ? $val['cust_group'] : '32000';

            if ($priceCustomerGroupId == '32000' || $priceCustomerGroupId == $customerGroupId) {
                if (!in_array($val[$key], $key_array)) {
                    $key_array[$i] = $val[$key];
                    $temp_array[$i] = $val;
                } else {
                    $k = array_search((int)$val[$key], $key_array);
                    if ($val['price'] < $temp_array[$k]['price']) {
                        //unset($temp_array[$k]);
                        $temp_array[$k] = $val;
                    }
                }
            }

            $i++;
        }

        return $temp_array;
    }

    public function getCustomerPriceValidDate($productId, $customerId, $websiteId){
        if ($this->helper->isCustomerPriceAllow()) {
            $dateArray = [];
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            $product = $this->productRepository->getById($productId);

            $oldFinalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
            $catArray = $this->getCategoryDiscount($customerId,$product->getCategoryIds(),$oldFinalPrice);

            if (is_array($catArray)) {
                //$discount = $catArray['catPrice'];
                $this->catFlag = true;
            }

            if ($product->getTypeId() == 'configurable') {
                foreach ($product->getTypeInstance()->getUsedProducts($product) as $child) {
                    $date = $this->getPriceValidDate($child,$customerId,$websiteId,$oldFinalPrice,$catArray);
                    $this->catFlag = false;
                    if ($date) {
                        return $date;
                    } 
                }
            }else{
                $date = $this->getPriceValidDate($product,$customerId,$websiteId,$oldFinalPrice,$catArray);
                $this->catFlag = false;
                return $date; 
            }
        }

        return 0;
    }

    public function getPriceValidDate($product,$customerId,$websiteId,$discount,$catArray){

            $cutomerPrice = [];
            $customerPriceValidDate = [];

            try{
                $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
                $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
                $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
                $tierPriceCollection = $this->getList($this->searchCriteriaBuilder->create())->getItems();

                if ($tierPriceCollection) {
                    foreach ($tierPriceCollection as $price) {
                        if ($discount) {
                            if ($price['qty']==1) {
                                if ((float)$discount >= (float)$price['new_price']) {
                                    if($price['expiry_date']!=""){
                                        $cutomerPrice[] = (float)$price['new_price'];
                                        $customerPriceValidDate[] = $price['expiry_date'];
                                    }
                                    $this->catFlag = false;  
                                }
                            }
                        }
                    }
                }

                if ($this->catFlag) {
                    //if (isset($catArray['validDate'])) {
                        return date("j M Y", strtotime($catArray['validDate']));
                    //}
                    
                }else{
                    if(!empty($cutomerPrice)){
                        return date("j M Y", strtotime($customerPriceValidDate[array_search(min($cutomerPrice), $cutomerPrice)]));
                    }
                }

            }catch (Exception $e) {
                return 0;
            }
        return 0;
    }

    public function getTierPriceDate($qty,$product,$tierPrice){

        if ($this->helper->isCustomerPriceAllow()) {
            if ($product->getTypeId() == 'configurable') {
                foreach ($product->getTypeInstance()->getUsedProducts($product) as $child) {
                    $date = $this->getTierPriceValidDate($qty,$child,$tierPrice);
                    if ($date) {
                        return $date;
                    } 
                }
            }else{
                return $this->getTierPriceValidDate($qty,$product,$tierPrice); 
            }
        }
        
        return 0;
    }

    private function getTierPriceValidDate($qty,$product,$tierPrice){

        try{
                $customerId = $this->helper->getUserId();
                $websiteId = 1;
                $cutomerPrice = [];
                $customerPriceValidDate = [];

                $this->searchCriteriaBuilder->addFilter('product_id', $product->getId(), 'eq');
                $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq');
                $this->searchCriteriaBuilder->addFilter('website_id', [0,$websiteId], 'in');
                $tierPriceCollection = $this->getList($this->searchCriteriaBuilder->create())->getItems();

                if ($tierPriceCollection) {
                    foreach ($tierPriceCollection as $price) {
                        if ($tierPrice) {
                            if ((int)$price['qty'] == $qty) {
                                if ((float)$tierPrice >= (float)$price['new_price']) {
                                    if($price['expiry_date']!=""){
                                        $cutomerPrice[] = (float)$price['new_price'];
                                        $customerPriceValidDate[] = $price['expiry_date'];
                                    } 
                                }
                            }
                        }
                    }
                }

                if(!empty($cutomerPrice)){
                    return date("j M Y", strtotime($customerPriceValidDate[array_search(min($cutomerPrice), $cutomerPrice)]));
                }

            }catch (Exception $e) {
                return 0;
            }    
    }

    private function getCategoryDiscount($customerId, $categoryIds,$price)
    {
        $discount = 0.00;
        $customerCategoryValidDate = [];

        $categoryDiscounts = $this->categoryDiscountModel
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('category_id', ['in' => $categoryIds])
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($categoryDiscounts as $categoryDiscount) {
            if($categoryDiscount->getExpiryDate()!=""){
                $discountArray[] = $categoryDiscount->getDiscount();
                $customerCategoryValidDate[] = $categoryDiscount->getExpiryDate();
            }
        }

        if(!empty($discountArray)){
            $discount  = max($discountArray);
            if ($discount > 0.00) {
                $resultPrice = $price - (($price * $discount)/100);
                if ((float)$price >= (float)$resultPrice) {
                    $date = $customerCategoryValidDate[array_search($discount, $discountArray)];
                    return['catPrice'=>$resultPrice,'validDate'=>$date];
                }
            }
        }else{
            return (int)0;
        }

        return (int)0;
    }
}

