<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Elasticsearch\Model\Adapter\Index\Builder;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;

class CreateNewAnalyzer
{
    public const ANALYZER_CODE = 'amasty_mwishlist_sku';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Builder $subject
     * @param array $settings
     * @return array
     */
    public function afterBuild(Builder $subject, array $settings): array
    {
        if (!empty($settings['analysis']['analyzer']['default']['tokenizer'])) {
            $settings['analysis']['analyzer'][self::ANALYZER_CODE] = $settings['analysis']['analyzer']['default'];
            $settings['analysis']['analyzer'][self::ANALYZER_CODE]['tokenizer'] = 'whitespace';
        }

        return $settings;
    }
}
