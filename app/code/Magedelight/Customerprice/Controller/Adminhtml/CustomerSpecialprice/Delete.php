<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
 
declare(strict_types=1);

namespace Magedelight\Customerprice\Controller\Adminhtml\CustomerSpecialprice;

class Delete extends \Magedelight\Customerprice\Controller\Adminhtml\CustomerSpecialprice
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Customerprice::CustomerpriceSpecialprice_delete';
    
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('customerspecialprice_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Magedelight\Customerprice\Model\CustomerpriceSpecialprice::class);
                $model->load($id);

                if($model->getCustomerpriceId()!=""){
                    $customerPriceModel = $this->_objectManager->create(\Magedelight\Customerprice\Model\Customerprice::class);
                    $customerPriceModel->load($model->getCustomerpriceId());
                    $customerPriceModel->delete();
                }

                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Customer Specialprice.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['customerspecialprice_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Customer Specialprice to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
