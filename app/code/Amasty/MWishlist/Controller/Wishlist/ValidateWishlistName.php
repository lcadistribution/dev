<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Controller\Wishlist;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Amasty\MWishlist\Model\Wishlist\Management as WishlistManagement;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAbstractIndex;

class ValidateWishlistName extends WishlistAbstractIndex implements AbstractIndexInterface
{
    public const CUSTOM_TYPE_PARAM = 'custom';

    /**
     * @var WishlistManagement
     */
    private $wishlistManagement;

    public function __construct(
        WishlistManagement $wishlistManagement,
        Context $context
    ) {
        parent::__construct($context);
        $this->wishlistManagement = $wishlistManagement;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $wishlistData = $this->getRequest()->getParam('wishlist');
        $name = $wishlistData[WishlistInterface::NAME] ?? null;

        if ($name && !$this->wishlistManagement->isWishlistExist($name)) {
            $resultJson->setData(true);
        } else {
            // used for magento remote validation
            $result = __('A list with the same name already exist.');

            if ($this->getRequest()->getParam(static::CUSTOM_TYPE_PARAM)) {
                // used for amasty remote validation
                $result = ['errors' => [$result]];
            }

            $resultJson->setData($result);
        }

        return $resultJson;
    }
}
