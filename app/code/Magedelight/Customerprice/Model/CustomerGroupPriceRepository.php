<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\CustomerGroupPriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterfaceFactory;
use Magedelight\Customerprice\Api\Data\CustomerGroupPriceSearchResultsInterfaceFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerGroupPrice as ResourceCustomerGroupPrice;
use Magedelight\Customerprice\Model\ResourceModel\CustomerGroupPrice\CollectionFactory as CustomerGroupPriceCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerGroupPriceRepository implements CustomerGroupPriceRepositoryInterface
{

    /**
     * @var CustomerGroupPriceInterfaceFactory
     */
    protected $customerGroupPriceFactory;

    /**
     * @var CustomerGroupPrice
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCustomerGroupPrice
     */
    protected $resource;

    /**
     * @var CustomerGroupPriceCollectionFactory
     */
    protected $customerGroupPriceCollectionFactory;


    /**
     * @param ResourceCustomerGroupPrice $resource
     * @param CustomerGroupPriceInterfaceFactory $customerGroupPriceFactory
     * @param CustomerGroupPriceCollectionFactory $customerGroupPriceCollectionFactory
     * @param CustomerGroupPriceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomerGroupPrice $resource,
        CustomerGroupPriceInterfaceFactory $customerGroupPriceFactory,
        CustomerGroupPriceCollectionFactory $customerGroupPriceCollectionFactory,
        CustomerGroupPriceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customerGroupPriceFactory = $customerGroupPriceFactory;
        $this->customerGroupPriceCollectionFactory = $customerGroupPriceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        CustomerGroupPriceInterface $customerGroupPrice
    ) {
        try {
            $this->resource->save($customerGroupPrice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerGroupPrice: %1',
                $exception->getMessage()
            ));
        }
        return $customerGroupPrice;
    }

    /**
     * @inheritDoc
     */
    public function get($customerGroupPriceId)
    {
        $customerGroupPrice = $this->customerGroupPriceFactory->create();
        $this->resource->load($customerGroupPrice, $customerGroupPriceId);
        if (!$customerGroupPrice->getId()) {
            throw new NoSuchEntityException(__('CustomerGroupPrice with id "%1" does not exist.', $customerGroupPriceId));
        }
        return $customerGroupPrice;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerGroupPriceCollectionFactory->create();
        
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
        CustomerGroupPriceInterface $customerGroupPrice
    ) {
        try {
            $customerGroupPriceModel = $this->customerGroupPriceFactory->create();
            $this->resource->load($customerGroupPriceModel, $customerGroupPrice->getCustomergrouppriceId());
            $this->resource->delete($customerGroupPriceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomerGroupPrice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customerGroupPriceId)
    {
        return $this->delete($this->get($customerGroupPriceId));
    }
}

