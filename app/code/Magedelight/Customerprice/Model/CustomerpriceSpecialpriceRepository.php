<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\CustomerpriceSpecialpriceRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterfaceFactory;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceSearchResultsInterfaceFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice as ResourceCustomerpriceSpecialprice;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice\CollectionFactory as CustomerpriceSpecialpriceCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerpriceSpecialpriceRepository implements CustomerpriceSpecialpriceRepositoryInterface
{

    /**
     * @var CustomerpriceSpecialprice
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCustomerpriceSpecialprice
     */
    protected $resource;

    /**
     * @var CustomerpriceSpecialpriceInterfaceFactory
     */
    protected $customerpriceSpecialpriceFactory;

    /**
     * @var CustomerpriceSpecialpriceCollectionFactory
     */
    protected $customerpriceSpecialpriceCollectionFactory;


    /**
     * @param ResourceCustomerpriceSpecialprice $resource
     * @param CustomerpriceSpecialpriceInterfaceFactory $customerpriceSpecialpriceFactory
     * @param CustomerpriceSpecialpriceCollectionFactory $customerpriceSpecialpriceCollectionFactory
     * @param CustomerpriceSpecialpriceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomerpriceSpecialprice $resource,
        CustomerpriceSpecialpriceInterfaceFactory $customerpriceSpecialpriceFactory,
        CustomerpriceSpecialpriceCollectionFactory $customerpriceSpecialpriceCollectionFactory,
        CustomerpriceSpecialpriceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customerpriceSpecialpriceFactory = $customerpriceSpecialpriceFactory;
        $this->customerpriceSpecialpriceCollectionFactory = $customerpriceSpecialpriceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
    ) {
        try {
            $this->resource->save($customerpriceSpecialprice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerpriceSpecialprice: %1',
                $exception->getMessage()
            ));
        }
        return $customerpriceSpecialprice;
    }

    /**
     * @inheritDoc
     */
    public function get($customerpriceSpecialpriceId)
    {
        $customerpriceSpecialprice = $this->customerpriceSpecialpriceFactory->create();
        $this->resource->load($customerpriceSpecialprice, $customerpriceSpecialpriceId);
        if (!$customerpriceSpecialprice->getId()) {
            throw new NoSuchEntityException(__('CustomerpriceSpecialprice with id "%1" does not exist.', $customerpriceSpecialpriceId));
        }
        return $customerpriceSpecialprice;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerpriceSpecialpriceCollectionFactory->create();
        
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
        CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
    ) {
        try {
            $customerpriceSpecialpriceModel = $this->customerpriceSpecialpriceFactory->create();
            $this->resource->load($customerpriceSpecialpriceModel, $customerpriceSpecialprice->getCustomerpricespecialpriceId());
            $this->resource->delete($customerpriceSpecialpriceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomerpriceSpecialprice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customerpriceSpecialpriceId)
    {
        return $this->delete($this->get($customerpriceSpecialpriceId));
    }
}

