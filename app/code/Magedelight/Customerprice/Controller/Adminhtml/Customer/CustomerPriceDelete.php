<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;

class CustomerPriceDelete extends Action
{
    /**
     * @var  \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     */
    private $_model;

    /**
     * @param Action\Context $context
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
     */
    public function __construct(
        Action\Context $context,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
    ) {
        parent::__construct($context);
        $this->_model = $customerprice;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_model;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Customerprice Deleted Successfully.'));
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        }
        $this->messageManager->addError(__('Customerprice does not exist'));
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
