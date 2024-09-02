<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\ViewModel;

use Amasty\MWishlist\Model\Source\ListType;
use Amasty\MWishlist\Model\Source\Type;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Tabs implements ArgumentInterface
{
    public const CURRENT_TAB_PARAM = 'current_tab';

    /**
     * @var ListType
     */
    private $listType;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ListType $listType,
        RequestInterface $request
    ) {
        $this->listType = $listType;
        $this->request = $request;
    }

    public function resolveActiveTabId(): int
    {
        $activeTabId = (int) $this->request->getParam(self::CURRENT_TAB_PARAM, Type::WISH);

        foreach ($this->listType->toArray() as $tabId => $tabName) {
            $tabName = (string) $tabName;
            $pagerSuffix = strtolower($tabName[0]);
            if ($this->request->getParam(Pagination::PAGE_VAR_NAME . $pagerSuffix)
                || $this->request->getParam(Pagination::LIMIT_VAR_NAME . $pagerSuffix)
            ) {
                $activeTabId = $tabId;
                break;
            }
        }

        return $activeTabId;
    }

    public function getTabs(): array
    {
        return $this->listType->toArray();
    }
}
