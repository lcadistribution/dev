<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model;

class Networks
{
    public const NETWORKS_URL_PARAMS = 'mwishlist_networks';

    /**
     * @var array
     */
    private $networks;

    /**
     * @var \Amasty\MWishlist\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    public function __construct(
        \Amasty\MWishlist\Model\ConfigProvider $configProvider,
        \Amasty\MWishlist\Model\Source\Networks $networksSource,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->configProvider = $configProvider;
        $this->objectFactory = $objectFactory;
        $this->networks = $networksSource->getArray();
    }

    private function isEnabled(string $network): bool
    {
        if (in_array($network, $this->configProvider->getSocialNetworks())) {
            return true;
        }

        return false;
    }

    /**
     * Enabled Networks
     *
     * @return array
     */
    public function getNetworks(): array
    {
        $networks = [];
        foreach ($this->networks as $data) {
            if ($this->isEnabled($data['value'])) {
                $networks[] = $this->objectFactory->create(['data' => $data]);
            }
        }

        return $networks;
    }
}
