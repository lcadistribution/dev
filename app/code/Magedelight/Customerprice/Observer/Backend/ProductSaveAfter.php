<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManagerInterface;

    /**
     * @var \Magento\Customer\Model\CustomerFactory 
     */
    protected $customerRepository;

    /**
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Customerprice\Helper\Data $helper
     * @param \Magedelight\Customerprice\Model\CustomerpriceFactory $customerpriceInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerRepository
     * @param \\Magento\Framework\Message\ManagerInterface $messageManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magedelight\Customerprice\Model\CustomerpriceFactory $customerpriceInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Message\ManagerInterface $messageManagerInterface
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->customerprice = $customerpriceInterface;
        $this->customerRepository = $customerRepository;
        $this->messageManagerInterface = $messageManagerInterface;
        
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     * @codingStandardsIgnoreStart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $options = $this->request->getPostValue();
        if ($this->helper->isEnabled()) {
            $product = $observer->getProduct();
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

                            preg_match('/(.*)%/', $newPrice, $matches);
                            if (is_array($matches) && count($matches) > 0) {
                                $newPrice = $product->getPrice() - ($product->getPrice() * ($matches[1] / 100));
                            }
                            $customer = $this->customerRepository->getById(trim($value['cid']));
                            $date = $value['date'];
                            $timestamp = strtotime($date);
                            if (is_int($timestamp) || is_null($timestamp)) {
                                $date = date('Y-m-d', $timestamp);
                            } else {
                                $date = NULL;
                            }

                            $priceCustomer->setCustomerId(trim($value['cid']))
                                    ->setCustomerName(trim($customer->getFirstName()." ".$customer->getLastName()))
                                    ->setCustomerEmail($customer->getEmail())
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
        } 
    }
}
