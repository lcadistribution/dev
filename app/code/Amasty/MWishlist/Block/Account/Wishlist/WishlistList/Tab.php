<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist\WishlistList;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Block\Pager;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection as ItemCollection;
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\Model\Wishlist;
use Amasty\MWishlist\Model\Wishlist\Management as WishlistManagement;
use Amasty\MWishlist\ViewModel\Pagination;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\Element\Template\Context;

class Tab extends AbstractPostBlock
{
    public const IMAGES_SHOW = 4;
    public const IMAGE_ID = 'mwishlist_item_preview_image';
    public const AVAILABLE_LIMIT = [8 => 8, 16 => 16, 40 => 40];

    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/list/tab.phtml';

    /**
     * @var WishlistManagement
     */
    private $wishlistManagement;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var int|null
     */
    private $listType;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var bool
     */
    private $isActiveTab = false;

    public function __construct(
        WishlistManagement $wishlistManagement,
        UrlHelper $urlHelper,
        ImageHelper $imageHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wishlistManagement = $wishlistManagement;
        $this->urlHelper = $urlHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param int $listType
     * @return $this
     */
    public function setListType(int $listType): Tab
    {
        $this->listType = $listType;
        $this->collection = null;
        $this->initPager();

        return $this;
    }

    /**
     * @return int
     */
    public function getListType(): int
    {
        return $this->listType ?? Type::WISH;
    }

    public function getWishlists(): Collection
    {
        if ($this->collection === null) {
            switch ($this->getListType()) {
                case Type::WISH:
                    $collection = $this->wishlistManagement->getWishlistList();
                    break;
                case Type::REQUISITION:
                    $collection = $this->wishlistManagement->getRequisitionList();
                    break;
                default:
                    $collection = $this->wishlistManagement->getCustomerWishlists();
            }
            $this->collection = $collection;
        }

        return $this->collection;
    }

    public function initPager(): void
    {
        /** @var Pager $pager */
        if ($pager = $this->getPager()) {
            $pager->clearCollection();
            $pager->setPageVarName($this->getPaginationHelper()->getPageVarName($this->getListType()));
            $pager->setLimitVarName($this->getPaginationHelper()->getLimitVarName($this->getListType()));
            $pager->setAvailableLimit(static::AVAILABLE_LIMIT);
            $pager->setCollection($this->getWishlists());
        }
    }

    public function getPaginationHelper(): Pagination
    {
        return $this->_data['pagination_helper'];
    }

    public function getPager(): ?Pager
    {
        return $this->getLayout()->getBlock('mwishlist.pager') ?: null;
    }

    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager', false);
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function renderName($name): string
    {
        return $name ?: __('Wish List')->render();
    }

    /**
     * @param int $wishlistId
     * @return string
     */
    public function getAddToCartData(int $wishlistId)
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::IN_CART_ITEMS_ROUTE), [
            'wishlist_id' => $wishlistId,
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl(
                $this->getUrl(PostHelper::LIST_WISHLIST_ROUTE, [
                    '_query' => [
                        $this->getPager()->getPageVarName() => $this->getPager()->getCurrentPage(),
                        $this->getPager()->getLimitVarName() => $this->getPager()->getLimit()
                    ]
                ])
            )
        ]);
    }

    public function isDeleteable(int $wishlistId): bool
    {
        return !$this->wishlistManagement->isWishlistDefault($wishlistId);
    }

    /**
     * @param int $wishlistId
     * @return string
     */
    public function getDeleteData(int $wishlistId): string
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::DELETE_WISHLIST_ROUTE), [
            'wishlist_id' => $wishlistId,
            UpdateAction::BLOCK_PARAM => 'mwishlist.list.contrainer',
            $this->getPager()->getPageVarName() => $this->getPager()->getCurrentPage()
        ]);
    }

    /**
     * @param int $wishlistId
     * @return string
     */
    public function getViewUrl(int $wishlistId): string
    {
        return $this->getUrl(PostHelper::VIEW_WISHLIST_ROUTE, ['wishlist_id' => $wishlistId]);
    }

    /**
     * @param WishlistInterface|Wishlist $wishlist
     * @return array
     */
    public function getItemImages(WishlistInterface $wishlist): array
    {
        $images = [];
        try {
            /** @var ItemCollection $itemCollection */
            $itemCollection = $wishlist->getItemCollection();
            $itemCollection->limitAndOrderByDate(static::IMAGES_SHOW);
            foreach ($itemCollection as $item) {
                $images[] = $this->imageHelper->init($item->getProduct(), static::IMAGE_ID)->getUrl();
            }
        } catch (NoSuchEntityException $e) {
            $images = [];
        }

        return $images;
    }

    public function isActiveTab(): bool
    {
        return $this->isActiveTab;
    }

    public function setIsActiveTab(bool $isActiveTab): Tab
    {
        $this->isActiveTab = $isActiveTab;
        return $this;
    }

    public function getEmptyMessage(): string
    {
        switch ($this->getListType()) {
            case Type::WISH:
                $message = __('You have no Wish Lists.');
                break;
            case Type::REQUISITION:
                $message = __('You have no Requisition Lists.');
                break;
            default:
                $message = '';
        }

        return (string) $message;
    }
}
