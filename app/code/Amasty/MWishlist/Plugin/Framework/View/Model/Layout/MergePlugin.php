<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Framework\View\Model\Layout;

use Amasty\MWishlist\Model\ConfigProvider;
use Magento\Framework\View\Model\Layout\Merge;

class MergePlugin
{
    public const CUSTOM_DEFAULT_HANDLE = 'mwishlist_default';

    public const LAYOUT_MAP = [
        'wishlist_index_share' => ['mwishlist_wishlist_index_share'],
        'customer_account' => ['mwishlist_customer_account'],
        'wishlist_index_index' => ['mwishlist_wishlist_index']
    ];

    /**
     * @var array
     */
    private $handles = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param Merge $subject
     * @param mixed $result
     * @param $handle
     * @return mixed
     */
    public function afterValidateUpdate(Merge $subject, $result, $handle)
    {
        if ($this->configProvider->isEnabled()) {
            $this->addHandle(static::CUSTOM_DEFAULT_HANDLE);

            if (isset(static::LAYOUT_MAP[$handle])) {
                foreach (static::LAYOUT_MAP[$handle] as $updateHandle) {
                    $this->addHandle($updateHandle);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $handle
     */
    private function addHandle(string $handle): void
    {
        if (!in_array($handle, $this->handles)) {
            $this->handles[] = $handle;
        }
    }

    /**
     * @param Merge $subject
     */
    public function beforeAsString(Merge $subject): void
    {
        if (!empty($this->handles)) {
            foreach ($this->handles as $handle) {
                $layout = $subject->getFileLayoutUpdatesXml();
                foreach ($layout->xpath("*[self::handle or self::layout][@id='{$handle}']") as $updateXml) {
                    $updateInnerXml = $updateXml->innerXml();
                    $subject->addUpdate($updateInnerXml);
                }
            }
            $this->handles = [];
        }
    }
}
