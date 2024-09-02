<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magedelight\Customerprice\Model\CustomerpriceFactory
     */
    protected $customerprice;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManagerInterface;

    /**
     * @var \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory
     */
    protected $customerpriceCategory;

    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface
     */
    protected $customerpriceDiscount;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory; 

    /**
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magedelight\Customerprice\Model\CustomerpriceFactory $customerprice
     * @param \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory $customerpriceCategory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param \Magento\Framework\Message\ManagerInterface $messageManagerInterface
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magedelight\Customerprice\Model\CustomerpriceFactory $customerprice,
        \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory $customerpriceCategory,
        \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Framework\Message\ManagerInterface $messageManagerInterface,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->customerprice = $customerprice;
        $this->productModel = $productModel;
        $this->messageManagerInterface = $messageManagerInterface;
        $this->customerpriceCategory = $customerpriceCategory;
        $this->customerpriceDiscount = $customerpriceDiscount;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return bool
     * @codingStandardsIgnoreStart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $customer = $observer->getCustomer();
            $name = $customer->getFirstname()." ".$customer->getLastname();
            $options = $this->request->getPostValue();

            /*echo "<pre>";
            print_r($options);
            exit();*/
            // saved customer price
            if (isset($options['option'])) {
                foreach ($options['option'] as $key => $_options) {
                    foreach ($_options as $k => $value) {
                        if ($key == 'value') {

                            //update/add customer price records
                            $priceCustomer = $this->customerprice->create();

                            if (is_int($k)) {
                                $priceCustomer->setId($k);
                            }
                            $newPrice = $value['newprice'];

                            $product = $this->productModel->create()->load(trim($value['pid']));

                            preg_match('/(.*)%/', $newPrice, $matches);
                            if (is_array($matches) && count($matches) > 0) {
                                $newPrice = $product->getPrice() - ($product->getPrice() * ($matches[1] / 100));
                            }

                            $date = $value['date'];
                            $timestamp = strtotime($date);
                            if (is_int($timestamp) || is_null($timestamp)) {
                                $date = date('Y-m-d', $timestamp);
                            } else {
                                $date = NULL;
                            }
                            $priceCustomer->setCustomerId($customer->getId())
                                    ->setCustomerName(trim($name))
                                    ->setCustomerEmail(trim($customer->getEmail()))
                                    ->setProductId($value['pid'])
                                    ->setProductName(trim($product->getName()))
                                    ->setNewPrice($newPrice)
                                    ->setLogPrice($value['newprice'])
                                    ->setPrice($product->getPrice())
                                    ->setQty($value['qty'])
                                    ->setExpiryDate($date);

                            if (isset($value['website'])) {
                                $priceCustomer->setWebsiteId($value['website']);
                            }
                            try{
                                $priceCustomer->save();
                            } catch (LocalizedException $e) {
                                $this->messageManagerInterface->addErrorMessage($e->getMessage());
                            }
                        }
                    }
                }
            }

            // saved customer all product discount
            if (isset($options['product'])){

                $discountValue = $options['product'];

                //update/add customer price records
                $priceDiscount = $this->customerpriceDiscount;

                if($discountValue['discount']){
                    $customer_id = $options['customer']['customer_id'];
                    
                    if (isset($discountValue['customerpricediscount_id'])) {
                        $priceDiscount->load($discountValue['customerpricediscount_id']);
                    }

                    $priceDiscount->setCustomerId($customer_id);
                    $priceDiscount->setValue($discountValue['discount']);

                    try {
                        $priceDiscount->save();
                    } catch (\Exception $e) {
                        $this->messageManagerInterface->addError($e->getMessage());
                    }
                }else{
                    if (isset($discountValue['customerpricediscount_id'])) {
                        $priceDiscount->load($discountValue['customerpricediscount_id'])->delete();
                    }
                }
            }

            //saved customer category price
            if (isset($options['categoryoption'])) {
                foreach ($options['categoryoption'] as $key => $_options) {
                    foreach ($_options as $k => $value) {
                        if ($key == 'value') {
                            //update/add customer category price records
                            $priceCustomerCategory = $this->customerpriceCategory->create();
                            if (is_int($k)) {
                                $priceCustomerCategory->setId($k);
                            }

                            $category = $this->categoryFactory->create()->load($value['pid']);
                            
                            $date = $value['date'];
                            $timestamp = strtotime($date);
                            if (is_int($timestamp) || is_null($timestamp)) {
                                $date = date('Y-m-d', $timestamp);
                            } else {
                                $date = NULL;
                            }
                            
                            $priceCustomerCategory->setCustomerId($customer->getId())
                                    ->setCustomerName(trim($name))
                                    ->setCustomerEmail(trim($customer->getEmail()))
                                    ->setCategoryId($value['pid'])
                                    ->setCategoryName(trim($category->getName()))
                                    ->setExpiryDate($date)
                                    ->setDiscount($value['discount']);
                            try{
                                $priceCustomerCategory->save();
                            } catch (LocalizedException $e) {
                                $this->messageManagerInterface->addErrorMessage($e->getMessage());
                            }
                        }
                    }
                }
            }
        }
      
    }  
}
