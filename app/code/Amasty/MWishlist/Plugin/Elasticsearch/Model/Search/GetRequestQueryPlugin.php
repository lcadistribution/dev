<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Elasticsearch\Model\Search;

use Amasty\ElasticSearch\Model\Search\GetRequestQuery;
use Amasty\MWishlist\Plugin\Elasticsearch\AbstractQueryPlugin;
use Magento\Framework\Search\RequestInterface;

class GetRequestQueryPlugin extends AbstractQueryPlugin
{
    /**
     * @phpstan-ignore-next-line
     *
     * @param GetRequestQuery $subject
     * @param array $searchQuery
     * @param RequestInterface $request
     * @return array
     */
    public function afterExecute(GetRequestQuery $subject, array $searchQuery, RequestInterface $request): array
    {
        if ($this->isMWishlistContainer($request, $searchQuery)) {
            $searchQuery = $this->processShouldQuery($searchQuery);
        }

        return $searchQuery;
    }

    /**
     * @param array $query
     * @return array
     */
    protected function modifyQuery(array $query): array
    {
        if (isset($query['query_string']['query'])) {
            $query['query_string']['query'] = $this->wrapWildcard($query['query_string']['query']);
            $query['query_string']['boost'] = $this->getBoostByCode($query['query_string']['default_field']);
        }

        return $query;
    }
}
