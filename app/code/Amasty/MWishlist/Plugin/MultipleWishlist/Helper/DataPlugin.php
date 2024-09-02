<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\MultipleWishlist\Helper;

use Amasty\MWishlist\Model\ConfigProvider;
use Magento\MultipleWishlist\Helper\Data;

class DataPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMultipleEnabled(Data $subject, bool $result): bool
    {
        if ($this->configProvider->isEnabled()) {
            $result = false;
        }

        return $result;
    }
}
