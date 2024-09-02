<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\ViewModel;

use Amasty\MWishlist\Model\ConfigProvider;
use Amasty\MWishlist\Model\Source\ListType as ListTypeSource;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Pagination implements ArgumentInterface
{
    public const PAGE_VAR_NAME = 'p';
    public const LIMIT_VAR_NAME = 'limit';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ListTypeSource
     */
    private $listTypeSource;

    public function __construct(
        ConfigProvider $configProvider,
        ListTypeSource $listTypeSource
    ) {
        $this->configProvider = $configProvider;
        $this->listTypeSource = $listTypeSource;
    }

    /**
     * @return mixed
     */
    public function getPaginationFrame()
    {
        return $this->configProvider->getPaginationFrame();
    }

    /**
     * @return mixed
     */
    public function getPaginationFrameSkip()
    {
        return $this->configProvider->getPaginationFrameSkip();
    }

    /**
     * Retrieve first letter from type name.
     *
     * @param int $type
     * @return string
     */
    public function getPagerSuffix(int $type): string
    {
        $typeName = (string) $this->listTypeSource->toArray()[$type];

        return strtolower(mb_substr($typeName, 0, 1, "UTF-8"));
    }

    public function getPageVarName(int $type): string
    {
        return static::PAGE_VAR_NAME . $this->getPagerSuffix($type);
    }

    public function getLimitVarName(int $type): string
    {
        return static::LIMIT_VAR_NAME . $this->getPagerSuffix($type);
    }
}
