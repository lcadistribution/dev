<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Elasticsearch;

use Amasty\MWishlist\Model\Product\Search;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Search\RequestInterface;

abstract class AbstractQueryPlugin
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(EavConfig $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param RequestInterface $request
     * @param array $shouldQuery
     * @return bool
     */
    protected function isMWishlistContainer(RequestInterface $request, array $shouldQuery): bool
    {
        return $request->getName() == Search::CONTAINER_NAME
            && isset($shouldQuery['body']['query']['bool']['should']);
    }

    /**
     * @param string $searchTerm
     * @return string
     */
    protected function wrapWildcard(string $searchTerm)
    {
        return sprintf('*%s*', trim($searchTerm, '*'));
    }

    /**
     * @param array $shouldQuery
     * @return array
     */
    protected function processShouldQuery(array $shouldQuery): array
    {
        $queryList = $shouldQuery['body']['query']['bool']['should'];
        foreach ($queryList as $index => $query) {
            $queryList[$index] = $this->modifyQuery($query);
        }
        $shouldQuery['body']['query']['bool']['should'] = $queryList;

        return $shouldQuery;
    }

    /**
     * @param string $attrCode
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getBoostByCode(string $attrCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attrCode)->getSearchWeight();
    }

    /**
     * @param array $query
     * @return array
     */
    abstract protected function modifyQuery(array $query): array;
}
