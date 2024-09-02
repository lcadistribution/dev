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

class Disapprove extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Customerprice::CustomerpriceSpecialprice_disapprove';
    
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $customerPriceHelper;

    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     */
    private $customerPrice;

    /**
     * @param Context     $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magedelight\Customerprice\Helper\Data $customerPriceHelper
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magedelight\Customerprice\Helper\Data $customerPriceHelper,
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
                if ($item->getCustomerpriceId()) {
                    $customerpriceModel = $this->customerPrice->load($item->getCustomerpriceId());
                    $customerpriceModel->delete();
                    $item->setApprove(0);
                    $item->setCustomerpriceId("");
                    $item->save();
                    $this->customerPriceHelper->sendDisapproveMail($item);
                    ++$done;
                }
            }
            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were modified.', $done));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
