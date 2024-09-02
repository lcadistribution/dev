<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Item;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\Action\Context;
use Amasty\MWishlist\Model\Wishlist\Item\Management as WishlistItemManagement;
use Amasty\MWishlist\Traits\ComponentProvider;
use Amasty\MWishlist\ViewModel\PostHelper;
use DomainException;
use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;

abstract class AbstractMove extends UpdateAction
{
    use ComponentProvider;

    public const MOVE_TO_PARAM = 'to_wishlist_id';

    /**
     * @var WishlistItemFactory
     */
    private $wishlistItemFactory;

    /**
     * @var WishlistItemManagement
     */
    private $itemManagement;

    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        WishlistItemManagement $itemManagement,
        WishlistRepositoryInterface $wishlistRepository,
        WishlistItemFactory $wishlistItemFactory,
        WishlistProviderInterface $wishlistProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->itemManagement = $itemManagement;
        $this->wishlistRepository = $wishlistRepository;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * @param WishlistItem $item
     * @param WishlistInterface $wishlist
     * @param int|null $qty
     * @return void
     * @throws InvalidArgumentException|DomainException|LocalizedException
     */
    abstract protected function moveAction(WishlistItem $item, WishlistInterface $wishlist, ?int $qty);

    /**
     * @return string
     */
    abstract protected function getNotAllowedMessage(): string;

    /**
     * @return string
     */
    abstract protected function getFailedMessage(): string;

    /**
     * @return string
     */
    abstract protected function getSuccessMessage(): string;

    /**
     * @return array
     */
    protected function action(): array
    {
        $result = [];

        try {
            $wishlist = $this->wishlistRepository->getById(
                $this->getContext()->getRequest()->getParam(self::MOVE_TO_PARAM)
            );
        } catch (NoSuchEntityException $e) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('Can\'t load wishlist.'));
            return $result;
        }

        $itemIds = $this->getContext()->getRequest()->getParam('selected', []);
        $moved = [];
        $failed = [];
        $notFound = [];
        $notAllowed = [];
        $alreadyPresent = [];
        if (count($itemIds)) {
            $qtys = $this->getContext()->getRequest()->getParam('qty', []);

            foreach ($itemIds as $id => $value) {
                try {
                    $wishlistItem = $this->wishlistItemFactory->create();
                    $wishlistItem->loadWithOptions($id);

                    $this->moveAction($wishlistItem, $wishlist, isset($qtys[$id]) ? (int) $qtys[$id] : null);
                    $moved[$id] = $wishlistItem;
                } catch (InvalidArgumentException $e) {
                    $notFound[] = $id;
                } catch (DomainException $e) {
                    if ($e->getCode() == 1) {
                        $alreadyPresent[$id] = $wishlistItem;
                    } else {
                        $notAllowed[$id] = $wishlistItem;
                    }
                } catch (Exception $e) {
                    $this->getContext()->getLogger()->critical($e);
                    $failed[] = $id;
                }
            }
        }

        $wishlistName = $this->getContext()->getEscaper()->escapeHtml($wishlist->getName());

        if (count($notFound)) {
            $this->getContext()->getMessageManager()->addErrorMessage(__('We can\'t find %1 items.', count($notFound)));
        }

        if (count($notAllowed)) {
            $names = $this->getContext()->getEscaper()->escapeHtml($this->joinProductNames($notAllowed));
            $this->getContext()->getMessageManager()->addErrorMessage(
                sprintf($this->getNotAllowedMessage(), count($notAllowed), $names)
            );
        }

        if (count($alreadyPresent)) {
            $names = $this->getContext()->getEscaper()->escapeHtml(
                $this->joinProductNames($alreadyPresent)
            );
            $this->getContext()->getMessageManager()->addErrorMessage(__(
                '%1 items are already present in %2: %3.',
                count($alreadyPresent),
                $wishlistName,
                $names
            ));
        }

        if (count($failed)) {
            $this->getContext()->getMessageManager()->addErrorMessage(
                sprintf($this->getFailedMessage(), count($failed))
            );
        }

        if (count($moved)) {
            $names = $this->getContext()->getEscaper()->escapeHtml($this->joinProductNames($moved));
            $this->getContext()->getMessageManager()->addComplexSuccessMessage(
                'messageWithUrlMWishlist',
                [
                    'message' => sprintf(
                        $this->getSuccessMessage(),
                        $names,
                        $this->getContext()->getUrlBuilder()->getUrl(PostHelper::VIEW_WISHLIST_ROUTE, [
                            'wishlist_id' => $wishlist->getWishlistId()
                        ]),
                        $wishlistName
                    )
                ]
            );
        }

        return array_merge(
            $result,
            ['components' => $this->getComponentData($this->wishlistProvider->getWishlist())]
        );
    }

    /**
     * Join item product names
     *
     * @param WishlistItem[] $items
     * @return string
     */
    protected function joinProductNames($items)
    {
        return join(
            ', ',
            array_map(
                function ($item) {
                    return '"' . $item->getProduct()->getName() . '"';
                },
                $items
            )
        );
    }

    /**
     * @return WishlistItemManagement
     */
    protected function getItemManagement(): WishlistItemManagement
    {
        return $this->itemManagement;
    }
}
