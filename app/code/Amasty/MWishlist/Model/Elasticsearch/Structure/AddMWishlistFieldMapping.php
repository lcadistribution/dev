<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Elasticsearch\Structure;

use Amasty\MWishlist\Plugin\Elasticsearch\Model\Adapter\Index\Builder\CreateNewAnalyzer;
use Amasty\MWishlist\Setup\Patch\Data\AddAmastySkuAttribute;

class AddMWishlistFieldMapping
{
    public function execute(array $fieldsMapping): array
    {
        $fieldsMapping[AddAmastySkuAttribute::ATTRIBUTE_NAME] = [
            'type' => 'text',
            'fielddata' => true,
            'analyzer' => CreateNewAnalyzer::ANALYZER_CODE
        ];

        return $fieldsMapping;
    }
}
