<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    public const ENABLED_PATH = 'general/enabled';
    public const SEND_PRICE_ALERT = 'customer_notifications/enabled';
    public const EMAIL_SENDER = 'customer_notifications/email_sender';
    public const EMAIL_TEMPLATE = 'customer_notifications/email_template';
    public const NETWORKS = 'general/networks';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_mwishlist/';

    /**
     * @return int
     */
    public function getSearchLimitResults(): int
    {
        return 10;
    }

    /**
     * @return bool
     */
    public function isMysqlEngine(): bool
    {
        return $this->scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH) === 'mysql';
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag(static::ENABLED_PATH);
    }

    /**
     * @return mixed
     */
    public function getPaginationFrame()
    {
        return $this->scopeConfig->getValue(
            'design/pagination/pagination_frame_skip',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPaginationFrameSkip()
    {
        return $this->scopeConfig->getValue(
            'design/pagination/pagination_frame_skip',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isPriceAlertsEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            $this->pathPrefix . self::SEND_PRICE_ALERT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getEmailSender(): string
    {
        return $this->scopeConfig->getValue(
            $this->pathPrefix . self::EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getEmailTemplate(): string
    {
        return $this->scopeConfig->getValue(
            $this->pathPrefix . self::EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSocialNetworks(): array
    {
        $value = $this->scopeConfig->getValue(
            $this->pathPrefix . self::NETWORKS,
            ScopeInterface::SCOPE_STORE
        );
        return $value ? explode(',', $value) : [];
    }
}
