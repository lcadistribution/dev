<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
 
declare(strict_types=1);

namespace Magedelight\Customerprice\Controller\Adminhtml\CustomerSpecialprice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $customerPriceHelper;

    protected $customerData;
   

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        CustomerRepositoryInterface $customerRepository,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->customerRepository = $customerRepository;
        $this->customerPriceHelper = $customerPriceHelper;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('customerspecialprice_id');
        
            $model = $this->_objectManager->create(\Magedelight\Customerprice\Model\CustomerpriceSpecialprice::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Customer Specialprice no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            try {

                if (isset($data['approve']) && $data['approve']==1) {
                    $cid = $this->getCustomerIdByEmail($data['email']);

                    if ($cid) {
                        $customerpriceData = [
                            'customer_name' => $data['name'],
                            'customer_email' => $data['email'],
                            'qty' => $data['quantity'],
                            'price' => $data['actual_price'],
                            'new_price' => $data['special_price'],
                            'log_price' => $data['special_price'],
                            'product_name' => $data['pname'],
                            'product_id' => $data['pid'],
                            'customer_id' => $cid,
                            'website_id' => $this->getCustomerWebsiteId(),
                            'expiry_date'=>$data['expiry_date'],
                        ];

                        if ($model->getCustomerpriceId()) {
                            $customerpriceId = $model->getCustomerpriceId();
                            $customerpriceData['customerprice_id'] = $customerpriceId;
                            $customerpriceModel = $this->_objectManager->create(\Magedelight\Customerprice\Api\Data\CustomerpriceInterface::class)->load($customerpriceId);
                            $customerpriceModel->setData($customerpriceData)->save();
                        } else {
                            $customerpriceModel = $this->_objectManager->create(\Magedelight\Customerprice\Api\Data\CustomerpriceInterface::class)->setData($customerpriceData)->save();
                            $data['customerprice_id'] = $customerpriceModel->getCustomerpriceId();
                        }

                        try {
                            $this->customerPriceHelper->sendMail($customerpriceData, $model->getCreateAt());
                        } catch (\Exception $e) {
                            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
                            return $resultRedirect->setPath('*/*/edit', ['customerspecialprice_id' => $model->getId()]);
                        }
                    }

                }

                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the Customer Specialprice.'));
                $this->dataPersistor->clear('md_customerprice_customerspecialprice');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['customerspecialprice_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Customer Specialprice.'));
            }
        
            $this->dataPersistor->set('md_customerprice_customerspecialprice', $data);
            return $resultRedirect->setPath('*/*/edit', ['customerspecialprice_id' => $this->getRequest()->getParam('customerspecialprice_id')]);
        }
        return $resultRedirect->setPath('*/*/');
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
