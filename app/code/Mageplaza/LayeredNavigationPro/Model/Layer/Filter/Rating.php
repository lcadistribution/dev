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

namespace Mageplaza\LayeredNavigationPro\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
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
 * @package Mageplaza\LayeredNavigationPro\Model\Layer\Filter
 */
class Rating extends AbstractFilter
{
    const FIED_NAME = 'rating_summary';

    /** @var Data */
    protected $_moduleHelper;

    /** Filter Value */
    protected $filterValue;

    /**
     * @var Visibility
     */
    protected $productVisibility;


    /**
     * Rating constructor.
     *
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param Data $moduleHelper
     * @param Visibility $productVisibility
     * @param RequestInterface $request
     * @param array $data
     *
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Data $moduleHelper,
        Visibility $productVisibility,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);

        $this->_moduleHelper     = $moduleHelper;
        $this->_requestVar       = Data::FILTER_TYPE_RATING;
        $this->productVisibility = $productVisibility;
        $this->request           = $request;

        $this->setData('filter_type', Data::FILTER_TYPE_RATING);
        $this->setData('multiple_mode', false);
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this|AbstractFilter
     * @throws NoSuchEntityException
     */
    public function apply(RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            return $this;
        }

        $attributeValue    = explode(',', $attributeValue);
        $rating            = min($attributeValue);
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            $rating = 1;
        }
        $this->filterValue = $rating;
        $this->getLayer()->getProductCollection()->addFieldToFilter(self::FIED_NAME, [
            'from' => $rating * 20,
            'to'   => null
        ]);

        $this->getLayer()->getState()->addFilter($this->_createItem($this->getOptionText($rating), $rating));

        // set items to disable show filtering
        // $this->setItems([]);

        return $this;
    }

    /**
     * @param int $optionId
     *
     * @return Phrase
     */
    protected function getOptionText($optionId)
    {
        if ($optionId == 1) {
            return __('%1 star & up', $optionId);
        }

        return __('%1 stars & up', $optionId);
    }

    /**
     * Get filter name
     *
     * @return Phrase
     */
    public function getName()
    {
        return $this->_moduleHelper->getFilterConfig('rating/label') ?: __('Rating');
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
                ->removeAttributeSearch(self::FIED_NAME . '.from');
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
