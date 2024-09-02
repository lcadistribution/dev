<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Product;

use Amasty\MWishlist\Model\ConfigProvider;
use InvalidArgumentException;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchInterface as MagentoSearch;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Helper\Data as SearchHelper;

class Search
{
    public const CONTAINER_NAME = 'mwishlist_search_container';

    /**
     * @var Image
     */
    private $imageModel;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var MagentoSearch
     */
    private $search;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    public function __construct(
        MagentoSearch $search,
        SearchHelper $searchHelper,
        StringUtils $stringUtils,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Visibility $visibility,
        PageFactory $resultPageFactory,
        Image $imageModel,
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory
    ) {
        $this->imageModel = $imageModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visibility = $visibility;
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->search = $search;
        $this->searchHelper = $searchHelper;
        $this->stringUtils = $stringUtils;
    }

    /**
     * @param string $searchTerm
     * @return array
     * @throw InvalidArgumentException
     */
    public function search(string $searchTerm)
    {
        $this->prepareSearchTerm($searchTerm);
        $this->prepareVisibility();
        $this->setLimit();

        return $this->generateSearchResult();
    }

    /**
     * @return array
     */
    private function generateSearchResult(): array
    {
        $searchResult = [];

        /** @var Product $product */
        foreach ($this->getProductCollection()->getItems() as $product) {
            $searchResult[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'image' => $this->imageModel->getUrl($product),
                'price' => $this->getPriceHtml($product)
            ];
        }

        return $searchResult;
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    protected function getPriceHtml(Product $product)
    {
        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';

        if ($priceRender) {
            $priceRender->setCacheLifetime(false);
            $priceType = $this->getPriceTypeCode($product->getTypeId());
            $arguments['zone'] = Render::ZONE_ITEM_LIST;
            $price = $priceRender->render($priceType, $product, $arguments);
        }

        return $price;
    }

    /**
     * @return Collection
     */
    private function getProductCollection()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        if ($productIds = $this->searchProductIds()) {
            if ($this->configProvider->isMysqlEngine()) {
                $collection->setVisibility($this->visibility->getVisibleInCatalogIds());
            }

            $collection->addIdFilter($productIds)
                ->addPriceData()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image');
            $orderList = join(',', $productIds);
            $collection->getSelect()->order(
                sprintf('FIELD(e.entity_id, %s)', $orderList)
            );
        } else {
            $collection->getSelect()->where('null');
        }

        return $collection;
    }

    /**
     * @return array
     */
    private function searchProductIds()
    {
        $productIds = [];
        foreach ($this->search->search($this->getSearchCriteria())->getItems() as $item) {
            $productIds[] = $item->getId();
        }

        return $productIds;
    }

    /**
     * @return SearchCriteriaInterface
     */
    private function getSearchCriteria()
    {
        return $this->searchCriteriaBuilder
            ->addSortOrder('relevance', 'desc')
            ->create()
            ->setRequestName(static::CONTAINER_NAME);
    }

    /**
     * @param string $searchTerm
     * @throws InvalidArgumentException
     */
    private function prepareSearchTerm(string $searchTerm)
    {
        $searchTermLength = $this->stringUtils->strlen($searchTerm);

        if ($searchTermLength > $this->searchHelper->getMaxQueryLength()) {
            throw new InvalidArgumentException(
                __('Maximum Search query length is %1', $this->searchHelper->getMaxQueryLength())->__toString()
            );

        } elseif ($searchTermLength < $this->searchHelper->getMinQueryLength()) {
            throw new InvalidArgumentException(
                __('Minimum Search query length is %1', $this->searchHelper->getMinQueryLength())->__toString()
            );
        } else {
            $this->addFilterToSearchCriteria('search_term', $searchTerm);
        }
    }

    private function prepareVisibility()
    {
        $this->addFilterToSearchCriteria('visibility', $this->visibility->getVisibleInCatalogIds());
    }

    private function setLimit()
    {
        $this->searchCriteriaBuilder->setPageSize($this->configProvider->getSearchLimitResults());
    }

    /**
     * @param string $field
     * @param string|array $value
     */
    private function addFilterToSearchCriteria(string $field, $value)
    {
        $this->filterBuilder->setField($field);
        $this->filterBuilder->setValue($value);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }

    /**
     * @param string $typeId
     * @return string
     */
    protected function getPriceTypeCode(string $typeId): string
    {
        switch ($typeId) {
            case Bundle::TYPE_CODE:
                $priceCode = ConfiguredPrice::CONFIGURED_PRICE_CODE;
                break;
            default:
                $priceCode = FinalPrice::PRICE_CODE;
        }

        return $priceCode;
    }

    /**
     * @return LayoutInterface
     */
    public function getLayout(): LayoutInterface
    {
        if (!$this->layout) {
            $page = $this->resultPageFactory->create(false, ['isIsolated' => false]);
            $page->addHandle('catalog_category_view');
            $this->layout = $page->getLayout();
        }

        return $this->layout;
    }
}
