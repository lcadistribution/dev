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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class Rating
 * @package Mageplaza\LayeredNavigationUltimate\Model\Layer\Filter
 */
class Rating extends \Mageplaza\LayeredNavigationPro\Model\Layer\Filter\Rating
{
    /**
     * @var null
     */
    protected $_filterVal = null;

    /**
     * @var bool
     */
    protected $filterValue = false;

    /**
     * Rating constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param Data $moduleHelper
     * @param Visibility $productVisibility
     * @param RequestInterface $request
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Data $moduleHelper,
        Visibility $productVisibility,
        RequestInterface
        $request,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $moduleHelper,
            $productVisibility,
            $request,
            $data
        );

        if ($this->_moduleHelper->getFilterConfig('rating/show_as_slider')) {
            $this->setData('range_mode', true);
        }
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        $showRatingSlider = $this->getData('range_mode');
        if ($showRatingSlider) {
            $attributeValue = $request->getParam($this->_requestVar);
            if (empty($attributeValue)) {
                return $this;
            }

            $filtered = $request->getParam($this->_requestVar);
            if ($filtered && !is_array($filtered)) {
                $filterParams = explode(',', $filtered);
                $filter       = $this->validateFilter($filterParams[0]);
                if ($filter) {
                    $this->_filterVal = $filter;
                }
            }

            $attributeValue = explode('-', $attributeValue);
            if (count($attributeValue) != 2 || $attributeValue[0] > $attributeValue[1]
                || ($attributeValue[0] < 1 || $attributeValue[1] > 5)
            ) {
                return $this;
            }

            $ratingDown = min($attributeValue);
            $ratingUp   = max($attributeValue);
            $this->getLayer()->getProductCollection()->addFieldToFilter('rating_summary_range', [
                'from' => $ratingDown * 20,
                'to'   => $ratingUp * 20
            ]);
            $this->filterValue = true;
            $this->getLayer()->getState()->addFilter($this->_createItem(
                $this->getRatingOptionText($attributeValue),
                $ratingDown
            ));

            return $this;
        }

        return parent::apply($request);
    }

    /**
     * Rating Slider Configuration
     *
     * @return array
     */
    public function getSliderConfig()
    {
        if (count($this->getItems()) <= 0) {
            return [];
        }
        $ratingsSlider = $this->getRatingItems($this->getItems());
        $min = min($ratingsSlider);
        $max = max($ratingsSlider);
        list($from, $to) = $this->_filterVal ?: [$min, $max];
        $from = ($from < $min) ? $min : $from;
        $to   = ($to > $max) ? $max : $to;

        $itemUrl = (isset($this->getItems()[0])) ? $this->getItems()[0]->getUrl() : '';

        return [
            "selectedFrom" => $from,
            "selectedTo"   => $to,
            "minValue"     => $min,
            "maxValue"     => $max,
            "orientation"  => "vertical",
            "ajaxUrl"      => $itemUrl,
            "ratingCode"   => Data::FILTER_TYPE_RATING
        ];
    }

    /**
     * add 5 star if no 5 star in rating items
     *
     * @param $items
     *
     * @return array
     */
    public function getRatingItems($items)
    {
        $result = [];
        if (is_array($items) && count($items)) {
            foreach ($items as $item) {
                $result[] = $item->getValue();
            }

            if (max($result) * 20 < 100) {
                array_unshift($result, 5);
            }
        }

        return $result;
    }

    /**
     * Validate and parse filter request param
     *
     * @param string $filter
     *
     * @return array|bool
     */
    public function validateFilter($filter)
    {
        $filter = explode('-', $filter);
        if (count($filter) != 2) {
            return false;
        }
        foreach ($filter as $v) {
            if ($v !== '' && $v !== '0' && (double)$v <= 0 || is_infinite((double)$v)) {
                return false;
            }
        }

        return $filter;
    }

    /**
     * get rating option text
     *
     * @param array $value
     *
     * @return Phrase
     */
    public function getRatingOptionText(array $value)
    {
        if ($value[0] == $value[1]) {
            return $value[0] == 1 ? __('%1 star', $value[0]) : __('%1 stars', $value[0]);
        }

        return $value[0] == 1 ? __('%1 star to %2 stars', $value[0], $value[1])
            : __('%1 stars to %2 stars', $value[0], $value[1]);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function _getItemsData()
    {
        $ratingStep = [80, 60, 40, 20];

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->filterValue) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch(['rating_summary_range.from', 'rating_summary_range.to']);
        }

        $optionsFacetedData = $productCollection->getFacetedData(self::FIED_NAME);
        foreach ($ratingStep as $step) {
            $count = isset($optionsFacetedData[$step]) ? $optionsFacetedData[$step]['count'] : 0;
            if ($count) {
                $this->itemDataBuilder->addItemData(
                    $step / 20 . ' Star',
                    $step / 20,
                    $count
                );
            }
        }

        return $this->itemDataBuilder->build();
    }
}
