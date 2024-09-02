<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\CustomerpriceDiscountRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterfaceFactory;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountSearchResultsInterfaceFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceDiscount as ResourceCustomerpriceDiscount;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceDiscount\CollectionFactory as CustomerpriceDiscountCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerpriceDiscountRepository implements CustomerpriceDiscountRepositoryInterface
{

    /**
     * @var CustomerpriceDiscountInterfaceFactory
     */
    protected $customerpriceDiscountFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCustomerpriceDiscount
     */
    protected $resource;

    /**
     * @var CustomerpriceDiscount
     */
    protected $searchResultsFactory;

    /**
     * @var CustomerpriceDiscountCollectionFactory
     */
    protected $customerpriceDiscountCollectionFactory;


    /**
     * @param ResourceCustomerpriceDiscount $resource
     * @param CustomerpriceDiscountInterfaceFactory $customerpriceDiscountFactory
     * @param CustomerpriceDiscountCollectionFactory $customerpriceDiscountCollectionFactory
     * @param CustomerpriceDiscountSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomerpriceDiscount $resource,
        CustomerpriceDiscountInterfaceFactory $customerpriceDiscountFactory,
        CustomerpriceDiscountCollectionFactory $customerpriceDiscountCollectionFactory,
        CustomerpriceDiscountSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customerpriceDiscountFactory = $customerpriceDiscountFactory;
        $this->customerpriceDiscountCollectionFactory = $customerpriceDiscountCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        CustomerpriceDiscountInterface $customerpriceDiscount
    ) {
        try {
            $this->resource->save($customerpriceDiscount);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerpriceDiscount: %1',
                $exception->getMessage()
            ));
        }
        return $customerpriceDiscount;
    }

    /**
     * @inheritDoc
     */
    public function get($customerpriceDiscountId)
    {
        $customerpriceDiscount = $this->customerpriceDiscountFactory->create();
        $this->resource->load($customerpriceDiscount, $customerpriceDiscountId);
        if (!$customerpriceDiscount->getId()) {
            throw new NoSuchEntityException(__('CustomerpriceDiscount with id "%1" does not exist.', $customerpriceDiscountId));
        }
        return $customerpriceDiscount;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerpriceDiscountCollectionFactory->create();
        
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
    public function delete(
        CustomerpriceDiscountInterface $customerpriceDiscount
    ) {
        try {
            $customerpriceDiscountModel = $this->customerpriceDiscountFactory->create();
            $this->resource->load($customerpriceDiscountModel, $customerpriceDiscount->getCustomerpricediscountId());
            $this->resource->delete($customerpriceDiscountModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomerpriceDiscount: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customerpriceDiscountId)
    {
        return $this->delete($this->get($customerpriceDiscountId));
    }
}

