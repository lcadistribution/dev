<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Elasticsearch\Elasticsearch5\SearchAdapter;

use Amasty\MWishlist\Plugin\Elasticsearch\AbstractQueryPlugin;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper;
use Magento\Framework\Search\RequestInterface;

class MapperPlugin extends AbstractQueryPlugin
{
    /**
     * @param Mapper $subject
     * @param array $searchQuery
     * @param RequestInterface $request
     * @return array
     */
    public function afterBuildQuery(Mapper $subject, array $searchQuery, RequestInterface $request): array
    {
        if ($this->isMWishlistContainer($request, $searchQuery)) {
            $searchQuery = $this->processShouldQuery($searchQuery);
        }

        return $searchQuery;
    }

    /**
     * @param array $query
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function modifyQuery(array $query): array
    {
        if (isset($query['match'])) {
            $matchQuery = reset($query['match']);
            $attribute = key($query['match']);
            $query['query_string'] = [
                'default_field' =>  $attribute,
                'query' => $this->prepareQuery($matchQuery['query']),
                'boost' => $this->getBoostByCode($attribute)
            ];
            unset($query['match']);
        }

        return $query;
    }

    /**
     * @param string $query
     * @return string
     */
    protected function prepareQuery(string $query): string
    {
        $queryWords = array_filter(explode(' ', $query));
        foreach ($queryWords as &$word) {
            $word = $this->wrapWildcard($word);
        }

        return implode(' AND ', $queryWords);
    }
}
