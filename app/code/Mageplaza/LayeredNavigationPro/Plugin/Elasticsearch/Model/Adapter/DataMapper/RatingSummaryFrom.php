<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;

/**
 * Class RatingSummaryFrom
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class RatingSummaryFrom implements DataMapperInterface
{
    const FIELD_NAME     = 'rating_summary';
    const INDEX_DOCUMENT = 'document';
    const SCOPE_CODE     = 'layered_navigation/filter/rating/rating_enable';
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * IsNew constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $localeDate
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig              = $scopeConfig;
        $this->localeDate               = $localeDate;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     *
     * @return array|int[]
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        return [self::FIELD_NAME => $this->getRatingSummary($entityId, $storeId)];
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag(self::SCOPE_CODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $entityId
     * @param $storeId
     *
     * @return array|mixed|null
     */
    public function getRatingSummary($entityId, $storeId)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->getSelect()
            ->joinLeft(
                ['rt' => $productCollection->getTable('review_entity_summary')],
                "e.entity_id = rt.entity_pk_value AND rt.store_id = " . $storeId,
                ['rating_summary']
            );
        $productCollection->getSelect()->where('e.entity_id = ' . $entityId);
        $rating  = [80, 60, 40, 20];
        $ratings = [];
        foreach ($rating as $key => $step) {
            $productCollectionClone = clone $productCollection;
            if ($this->isHasRatingSummary($productCollectionClone, $step)) {
                $ratings[] = $step;
            }
        }

        return $ratings;
    }

    /**
     * @param Collection $productCollection
     * @param int $step
     *
     * @return bool
     */
    private function isHasRatingSummary(
        Collection $productCollection,
        int $step
    ) {
        $productCollection->getSelect()->where('rt.rating_summary >= ' . $step);
        if ($productCollection->getSize()) {
            return true;
        }

        return false;
    }
}
