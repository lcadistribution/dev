<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Ui\DataProvider\Listing\Purchased;

use Magento\Framework\Api\Search\SearchResultInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var string[]
     */
    private $filterMap = [
        'sku' => 'product_entity.sku',
    ];

    /**
     * @var string[]
     */
    private $havingColumns = [
        'placed_from_list' => 'COUNT(order_item.product_id)',
        'qty' => 'SUM(order_item.qty_ordered)'
    ];

    /**
     * @var array
     */
    private $havingFilters = [];

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return $this|mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (in_array($filter->getField(), array_keys($this->havingColumns))) {
            $this->havingFilters[] = $filter;
            return $this;
        }

        if (in_array($filter->getField(), array_keys($this->filterMap))) {
            $filter->setField(new \Zend_Db_Expr($this->filterMap[$filter->getField()]));
        }

        return parent::addFilter($filter);
    }

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $operations = [
            'gteq' => '>=',
            'lteq' => '<=',
            'like' => 'like'
        ];

        foreach ($this->havingFilters as $filter) {
            $fieldExpr = $this->havingColumns[$filter->getField()];
            $searchResult->getSelect()->having(
                sprintf('%s %s "%s"', $fieldExpr, $operations[$filter->getConditionType()], $filter->getValue())
            );
        }

        return parent::searchResultToOutput($searchResult);
    }
}
