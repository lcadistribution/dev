<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Purchased extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_MWishlist::wishlist_most_purchased_items';

    public function execute(): void
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->initAction($resultPage)->getConfig()->getTitle()->prepend(__('Most Purchased Items from Lists'));
        $resultPage->renderResult($this->getResponse());
    }

    private function initAction(ResultInterface $resultPage): ResultInterface
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Most Purchased Items from Lists'), __('Most Purchased Items from Lists'));

        return $resultPage;
    }
}
