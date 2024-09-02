<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Script extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/script.phtml';

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    public function __construct(
        Context $context,
        HttpContext $httpContext,
        CustomerUrl $customerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->customerUrl = $customerUrl;
    }

    /**
     * @return string
     */
    public function getAddItemUrl(): string
    {
        return $this->getUrl(PostHelper::ADD_ITEM_ROUTE);
    }

    /**
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->customerUrl->getLoginUrl();
    }

    /**
     * @return bool
     */
    public function isLoggedCustomer(): bool
    {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
