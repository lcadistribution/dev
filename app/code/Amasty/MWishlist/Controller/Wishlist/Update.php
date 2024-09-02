<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Model\Source\Type;
use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Update extends \Magento\Wishlist\Controller\AbstractIndex
{
    public const EDITABLE_COLUMNS = [
        WishlistInterface::NAME,
        WishlistInterface::TYPE
    ];

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->wishlistHelper = $wishlistHelper;
        $this->escaper = $escaper;
        $this->itemFactory = $itemFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        $post = $this->getRequest()->getPostValue();
        $needUpdateWishlist = false;
        if ($post && isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = $this->itemFactory->create()->load($itemId);
                if ($item->getWishlistId() != $wishlist->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string)$description;

                if ($description == $this->wishlistHelper->defaultCommentString()) {
                    $description = '';
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->quantityProcessor->process($post['qty'][$itemId]);
                }
                if ($qty === null) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t delete item from Wish List right now.'));
                    }
                }

                // Check that we need to save
                if ($item->getDescription() == $description && $item->getQty() == $qty) {
                    continue;
                }
                try {
                    $item->setDescription($description)->setQty($qty)->save();
                    $this->messageManager->addSuccessMessage(
                        __('%1 has been updated in your Wish List.', $item->getProduct()->getName())
                    );
                    $updatedItems++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Can\'t save description %1',
                            $this->escaper->escapeHtml($description)
                        )
                    );
                }
            }

            // save wishlist model for setting date of last update
            $needUpdateWishlist = (bool) $updatedItems;
        }

        $wishlistData = $post['wishlist'] ?? [];
        try {
            $newData = $this->cutEditableColumns($wishlistData);
            $currentData = $this->cutEditableColumns($wishlist->getData());
            if ($newData && $newData != $currentData) {
                $wishlist->addData($newData);
                switch ($wishlist->getType()) {
                    case Type::WISH:
                        $this->messageManager->addSuccessMessage(
                            __('Wish list has been updated.')
                        );
                        break;
                    case Type::REQUISITION:
                        $this->messageManager->addSuccessMessage(
                            __('Requisition list has been updated.')
                        );
                        break;
                }
                $needUpdateWishlist |= true;
            }
            if ($needUpdateWishlist) {
                $wishlist->save();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Can\'t update wish list'));
        }

        if (isset($post['save_and_share'])) {
            $resultRedirect->setPath('wishlist/index/share', ['wishlist_id' => $wishlist->getId()]);
        } else {
            $resultRedirect->setPath('*/*', ['wishlist_id' => $wishlist->getId()]);
        }

        return $resultRedirect;
    }

    /**
     * @param array $columns
     * @return array
     */
    private function cutEditableColumns(array $columns): array
    {
        foreach ($columns as $columnName => $columnValue) {
            if (!in_array($columnName, static::EDITABLE_COLUMNS)) {
                unset($columns[$columnName]);
            }
        }

        return $columns;
    }
}
