<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\CustomerpriceCategoryRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategorySearchResultsInterfaceFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory as ResourceCustomerpriceCategory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory as CustomerpriceCategoryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerpriceCategoryRepository implements CustomerpriceCategoryRepositoryInterface
{

    /**
     * @var CustomerpriceCategoryCollectionFactory
     */
    protected $customerpriceCategoryCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceCustomerpriceCategory
     */
    protected $resource;

    /**
     * @var CustomerpriceCategoryInterfaceFactory
     */
    protected $customerpriceCategoryFactory;

    /**
     * @var CustomerpriceCategory
     */
    protected $searchResultsFactory;


    /**
     * @param ResourceCustomerpriceCategory $resource
     * @param CustomerpriceCategoryInterfaceFactory $customerpriceCategoryFactory
     * @param CustomerpriceCategoryCollectionFactory $customerpriceCategoryCollectionFactory
     * @param CustomerpriceCategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomerpriceCategory $resource,
        CustomerpriceCategoryInterfaceFactory $customerpriceCategoryFactory,
        CustomerpriceCategoryCollectionFactory $customerpriceCategoryCollectionFactory,
        CustomerpriceCategorySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customerpriceCategoryFactory = $customerpriceCategoryFactory;
        $this->customerpriceCategoryCollectionFactory = $customerpriceCategoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        CustomerpriceCategoryInterface $customerpriceCategory
    ) {
        try {
            $this->resource->save($customerpriceCategory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerpriceCategory: %1',
                $exception->getMessage()
            ));
        }
        return $customerpriceCategory;
    }

    /**
     * @inheritDoc
     */
    public function get($customerpriceCategoryId)
    {
        $customerpriceCategory = $this->customerpriceCategoryFactory->create();
        $this->resource->load($customerpriceCategory, $customerpriceCategoryId);
        if (!$customerpriceCategory->getId()) {
            throw new NoSuchEntityException(__('CustomerpriceCategory with id "%1" does not exist.', $customerpriceCategoryId));
        }
        return $customerpriceCategory;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerpriceCategoryCollectionFactory->create();
        
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
        CustomerpriceCategoryInterface $customerpriceCategory
    ) {
        try {
            $customerpriceCategoryModel = $this->customerpriceCategoryFactory->create();
            $this->resource->load($customerpriceCategoryModel, $customerpriceCategory->getCustomerpricecategoryId());
            $this->resource->delete($customerpriceCategoryModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomerpriceCategory: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customerpriceCategoryId)
    {
        return $this->delete($this->get($customerpriceCategoryId));
    }
}

