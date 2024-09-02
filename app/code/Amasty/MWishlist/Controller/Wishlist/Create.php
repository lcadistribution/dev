<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\Action\Context;
use Amasty\MWishlist\Model\Wishlist\Editor as WishlistEditor;
use Amasty\MWishlist\ViewModel\PostHelper;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;

class Create extends UpdateAction
{
    /**
     * @var WishlistEditor
     */
    private $wishlistEditor;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        WishlistEditor $wishlistEditor,
        CustomerSession $customerSession,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistEditor = $wishlistEditor;
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    protected function action(): array
    {
        $customerId = $this->customerSession->getCustomerId();
        $wishlistData = $this->getContext()->getRequest()->getParam('wishlist');
        $wishlistName = $wishlistData[WishlistInterface::NAME] ?? null;
        $wishlistId = $wishlistData[WishlistInterface::WISHLIST_ID] ?? null;
        $wishlist = null;
        try {
            $wishlist = $this->wishlistEditor->edit($customerId, $wishlistName, $wishlistId, $wishlistData);

            $this->getContext()->getMessageManager()->addComplexSuccessMessage(
                'createListMWishlist',
                [
                    'wishlist_url' => $this->getContext()->getUrlBuilder()->getUrl(
                        PostHelper::VIEW_WISHLIST_ROUTE,
                        [
                            'wishlist_id' => $wishlist->getWishlistId()
                        ]
                    ),
                    'wishlist_name' => $wishlist->getName()
                ]
            );

            return [];
        } catch (LocalizedException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage($e->getMessage());
            return [];
        } catch (Exception $e) {
            $this->getContext()->getLogger()->error($e->getMessage());
            $this->getContext()->getMessageManager()->addErrorMessage(__('Something wrong'));
            return [];
        }
    }
}
