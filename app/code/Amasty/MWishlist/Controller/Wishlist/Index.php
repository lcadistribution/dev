<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Wishlist;

use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;

class Index extends WishlistAbstractIndex implements AbstractIndexInterface
{
    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        Context $context,
        WishlistProviderInterface $wishlistProvider
    ) {
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->wishlistProvider->getWishlist()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
