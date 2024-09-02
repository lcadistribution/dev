<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Index;

use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Magento\Catalog\Helper\Product\View as ViewHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;

class Configure extends WishlistAbstractIndex implements AbstractIndexInterface
{
    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ViewHelper
     */
    private $viewHelper;

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        WishlistItemFactory $wishlistItemFactory,
        Registry $coreRegistry,
        ViewHelper $viewHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->coreRegistry = $coreRegistry;
        $this->viewHelper = $viewHelper;
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $item = $this->wishlistItemFactory->create();
            $item->loadWithOptions($id);
            if (!$item->getId()) {
                throw new LocalizedException(
                    __("The Wish List item can't load at this time. Please try again later.")
                );
            }
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                throw new NotFoundException(__('Page not found.'));
            }

            $this->coreRegistry->register('wishlist_item', $item);

            $params = new DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);

            $buyRequest = $item->getBuyRequest();

            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
            }
            $params->setBuyRequest($buyRequest);

            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->viewHelper->prepareAndRender(
                $resultPage,
                $item->getProductId(),
                $this,
                $params
            );

            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t configure the product right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $resultRedirect->setPath('*');
            return $resultRedirect;
        }
    }
}
