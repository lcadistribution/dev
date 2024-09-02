<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Unsubscribe;

use Amasty\MWishlist\Model\ResourceModel\UnsubscribePriceAlerts;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\Controller\ResultFactory;

class PriceAlerts extends UnsubscribeController
{
    /**
     * @var UnsubscribePriceAlerts
     */
    private $unsubscribePriceAlerts;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        UnsubscribePriceAlerts $unsubscribePriceAlerts
    ) {
        parent::__construct($context, $customerSession);
        $this->unsubscribePriceAlerts = $unsubscribePriceAlerts;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $this->unsubscribePriceAlerts->unsubscribeUser((int)$this->customerSession->getCustomerId());
            $this->messageManager->addSuccessMessage(__('You will no longer receive wishlist price alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Unable to update the wishlist price alert subscription.')
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('customer/account/');
    }
}
