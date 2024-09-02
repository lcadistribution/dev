<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\CustomerSpecialprice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\Result\JsonFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Approve extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Customerprice::CustomerpriceSpecialprice_approve';
    
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $customerPriceHelper;

    
    private $customerData;

    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     */
    private $customerPrice;

    /**
     * @param Context     $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->customerPriceHelper = $customerPriceHelper;
        $this->customerPrice = $customerPrice;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $done = 0;

            foreach ($collection as $item) {
                $cid = $this->getCustomerIdByEmail($item->getEmail());
                
                if ($cid) {
                    $customerpriceData = [
                        'customer_name' => $item->getName(),
                        'customer_email' => $item->getEmail(),
                        'qty' => $item->getData('quantity'),
                        'price' => $item->getActualPrice(),
                        'new_price' => $item->getSpecialPrice(),
                        'log_price' => $item->getSpecialPrice(),
                        'product_name' => $item->getPname(),
                        'product_id' => $item->getPid(),
                        'customer_id' => $cid,
                        'website_id' => $this->getCustomerWebsiteId(),
                        'expiry_date'=>$item->getExpiryDate(),
                    ];

                    $customerpriceId = $item->getCustomerpriceId();

                    if ($customerpriceId) {
                        $customerpriceData['customerprice_id'] = $customerpriceId;
                        $customerpriceModel = $this->customerPrice->load($customerpriceId);
                    } else {
                        $customerpriceModel = $this->customerPrice->setData($customerpriceData);
                    }
                    $customerpriceModel->setData($customerpriceData)->save();
                    
                    $item->setCustomerpriceId($customerpriceModel->getCustomerpriceId());
                    $item->setApprove(1)->save();
                    $this->customerPriceHelper->sendMail($customerpriceData, $item->getCreatedAt());
                }

                ++$done;
            }

            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were modified.', $done));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }


    /**
     * @param string $email
     * @return int|null
     */
    public function getCustomerIdByEmail(string $email)
    {
        $customerId = null;
        try {
            $this->customerData = $this->customerRepository->get($email);
            $customerId = (int)$this->customerData->getId();
        } catch (NoSuchEntityException $noSuchEntityException) {
        }
        return $customerId;
    }

    /**
     * @return int|null
     */
    public function getCustomerWebsiteId()
    {
        return ($this->customerData->getWebsiteId()) ? $this->customerData->getWebsiteId() : 0;
    }
}
