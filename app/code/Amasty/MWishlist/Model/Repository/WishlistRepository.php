<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Repository;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection;
use Amasty\MWishlist\Model\Wishlist\ValidateWishlistAccess;
use Amasty\MWishlist\Model\WishlistFactory;
use Amasty\MWishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Wishlist\Model\Wishlist\AddProductsToWishlist as AddProductsToWishlistModel;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistRepository implements WishlistRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistResource
     */
    private $wishlistResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $wishlists;

    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;

    /**
     * @var AddProductsToWishlistModel
     */
    private $addProductsToWishlist;

    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var ValidateWishlistAccess
     */
    private $validateWishlistAccess;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        WishlistFactory $wishlistFactory,
        WishlistResource $wishlistResource,
        CollectionFactory $wishlistCollectionFactory,
        AddProductsToWishlistModel $addProductsToWishlist,
        WishlistItemFactory $wishlistItemFactory,
        ValidateWishlistAccess $validateWishlistAccess
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistResource = $wishlistResource;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->addProductsToWishlist = $addProductsToWishlist;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->validateWishlistAccess = $validateWishlistAccess;
    }

    /**
     * @inheritdoc
     */
    public function save(WishlistInterface $wishlist, ?int $customerId = null)
    {
        try {
            if ($wishlist->getWishlistId()) {
                $loadedWishlist = $this->getById($wishlist->getWishlistId(), $customerId);
                if ($loadedWishlist->getCustomerId() != $wishlist->getCustomerId()
                    && null !== $wishlist->getCustomerId()
                ) {
                    throw new LocalizedException(__('You can\'t change owner of your wishlist'));
                }
                $loadedWishlist->addData($wishlist->getData());
                $this->wishlistResource->save($loadedWishlist);

                return $loadedWishlist;
            }

            $this->wishlistResource->save($wishlist);
            unset($this->wishlists[$wishlist->getWishlistId()]);
        } catch (\Exception $e) {
            if ($wishlist->getWishlistId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save wishlist with ID %1. Error: %2',
                        [$wishlist->getWishlistId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new wishlist. Error: %1', $e->getMessage()));
        }

        return $wishlist;
    }

    /**
     * @throws LocalizedException
     */
    public function getById($id, ?int $customerId = null)
    {
        if (!isset($this->wishlists[$id])) {
            /** @var \Amasty\MWishlist\Model\Wishlist $wishlist */
            $wishlist = $this->wishlistFactory->create();
            $this->wishlistResource->load($wishlist, $id);
            $this->validateWishlistAccess->execute($wishlist, $customerId);
            if (!$wishlist->getWishlistId()) {
                throw new NoSuchEntityException(__('Wishlist with specified ID "%1" not found.', $id));
            }
            $this->wishlists[$id] = $wishlist;
        }

        return $this->wishlists[$id];
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId($customerId)
    {
        /** @var \Amasty\MWishlist\Model\Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($customerId);
        if (!$wishlist->getWishlistId()) {
            throw new NoSuchEntityException(__('Wishlist with specified Customer ID "%1" not found.', $customerId));
        }
        $this->wishlists[$wishlist->getId()] = $wishlist;

        return $this->wishlists[$wishlist->getId()];
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        return $this->wishlistFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(WishlistInterface $wishlist)
    {
        try {
            $this->wishlistResource->delete($wishlist);
            unset($this->wishlists[$wishlist->getWishlistId()]);
        } catch (\Exception $e) {
            if ($wishlist->getWishlistId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove wishlist with ID %1. Error: %2',
                        [$wishlist->getWishlistId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove wishlist. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @throws LocalizedException
     */
    public function deleteById(int $wishlistId, ?int $customerId = null): bool
    {
        $wishlistModel = $this->getById($wishlistId, $customerId);
        $this->delete($wishlistModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $wishlistCollection */
        $wishlistCollection = $this->wishlistCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $wishlistCollection);
        }

        $searchResults->setTotalCount($wishlistCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $wishlistCollection);
        }

        $wishlistCollection->setCurPage($searchCriteria->getCurrentPage());
        $wishlistCollection->setPageSize($searchCriteria->getPageSize());

        $wishlists = [];
        /** @var WishlistInterface $wishlist */
        foreach ($wishlistCollection->getItems() as $wishlist) {
            $wishlists[] = $this->getById($wishlist->getWishlistId());
        }

        $searchResults->setItems($wishlists);

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getWishlistsByCustomerId(int $customerId, $type = null)
    {
        /** @var Collection $wishlistCollection */
        $wishlistCollection = $this->wishlistCollectionFactory->create();
        $wishlistCollection->filterByCustomerId($customerId);
        if ($type !== null) {
            $wishlistCollection->filterByType($type);
        }
        $wishlistCollection->orderByDate();

        return $wishlistCollection;
    }

    /**
     * @inheritdoc
     */
    public function isWishlistExist(int $customerId, string $wishlistName)
    {
        return (bool) $this->getWishlistsByCustomerId($customerId)->filterByName($wishlistName)->getSize();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $wishlistCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $wishlistCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $wishlistCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $wishlistCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $wishlistCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $wishlistCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException|LocalizedException
     */
    public function addProductToWishlist(int $wishlistId, array $wishlistItems, int $customerId): bool
    {
        $wishlist = $this->getById($wishlistId, $customerId);

        $items = [];
        foreach ($wishlistItems as $wishlistItem) {
            $items[] = $this->wishlistItemFactory->create($wishlistItem->getData());
        }

        $output = $this->addProductsToWishlist->execute($wishlist, $items);
        $errors = [];
        if ($output->getErrors()) {
            foreach ($output->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
            throw new LocalizedException(__('Error while adding items to wishlist: %1', $errors));
        }

        return true;
    }
}
