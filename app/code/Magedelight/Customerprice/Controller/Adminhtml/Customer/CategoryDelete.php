<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;

class CategoryDelete extends Action
{
    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface
     */
    private $_model;

    /**
     * @param Action\Context $context
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
     */
    public function __construct(
        Action\Context $context,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
    ) {
        parent::__construct($context);
        $this->_model = $customerpriceCategory;
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
                $this->messageManager->addSuccess(__('Categoryprice Deleted Successfully.'));
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        }
        $this->messageManager->addError(__('Categoryprice does not exist'));
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
