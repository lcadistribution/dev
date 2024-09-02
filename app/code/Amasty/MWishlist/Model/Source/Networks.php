<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Networks implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getArray() as $network) {
            $result[] = [
                'value' => $network['value'],
                'label' => $network['label']
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return [
            [
                'value' => 'twitter',
                'label' => __('Twitter'),
                'is_template' => false,
                'url' => 'https://twitter.com/intent/tweet?text={title}&url={url}',
                'style' => 'background-position:-343px -55px;',
            ],
            [
                'value' => 'facebook',
                'label' => __('Facebook'),
                'is_template' => false,
                'url' => 'http://www.facebook.com/share.php?u={url}',
                'style' => 'background-position:-343px -1px;',
            ],
            [
                'value' => 'pinterest',
                'label' => __('Pinterest'),
                'is_template' => false,
                'url' => 'http://pinterest.com/pin/create/button/?url={url}&media={image}&description={title}',
                'style' => 'background-position: -55px -90px;',
                'image' => true,
            ],
            [
                'value' => 'linkedin',
                'label' => __('LinkedIn'),
                'is_template' => false,
                'url' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
                'style' => 'background-position: -1px -37px;',
            ],
            [
                'value' => 'line',
                'label' => __('Line'),
                'is_template' => false,
                'url' => 'http://lineit.line.me/share/ui?url={url}',
                'style' => 'background-position: -1px -37px;',
            ],
            [
                'value' => 'telegram',
                'label' => __('Telegram'),
                'is_template' => false,
                'url' => 'http://t.me/share/url?url={url}&text={title}',
                'style' => 'background-position: -1px -37px;',
            ]
        ];
    }
}
