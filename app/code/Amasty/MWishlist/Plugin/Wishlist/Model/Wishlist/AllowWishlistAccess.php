<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Wishlist\Model\Wishlist;

use Amasty\MWishlist\Model\Networks;
use Magento\Framework\App\RequestInterface;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;

class AllowWishlistAccess
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var WishlistResource
     */
    private $wishlistResourceModel;

    public function __construct(
        RequestInterface $request,
        WishlistResource $wishlistResourceModel
    ) {
        $this->request = $request;
        $this->wishlistResourceModel = $wishlistResourceModel;
    }

    public function aroundLoadByCode(Wishlist $subject, callable $proceed, string $code): Wishlist
    {
        if ($this->request->getParam(Networks::NETWORKS_URL_PARAMS)) {
            $this->wishlistResourceModel->load($subject, $code, 'sharing_code');
        } else {
            $subject = $proceed($code);
        }

        return $subject;
    }
}
