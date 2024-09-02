<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapperProxy;

use Amasty\MWishlist\Model\Elasticsearch\Structure\AddMWishlistFieldMapping;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapperProxy;

class AddAnalyzerForMWishlistSku
{
    /**
     * @var AddMWishlistFieldMapping
     */
    private $addMWishlistFieldMapping;

    public function __construct(
        AddMWishlistFieldMapping $addMWishlistFieldMapping
    ) {
        $this->addMWishlistFieldMapping = $addMWishlistFieldMapping;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ProductFieldMapperProxy $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllAttributesTypes(ProductFieldMapperProxy $subject, array $result): array
    {
        return $this->addMWishlistFieldMapping->execute($result);
    }
}
