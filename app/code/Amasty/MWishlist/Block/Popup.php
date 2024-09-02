<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block;

use Amasty\MWishlist\Controller\Wishlist\ValidateWishlistName;
use Amasty\MWishlist\Model\Source\ListType;
use Amasty\MWishlist\Model\Source\Type;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Popup extends Template
{
    /**
     * @var Type
     */
    private $type;

    public function __construct(
        ListType $type,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->type = $type;
    }

    public function getJsLayout()
    {
        $this->jsLayout = $this->updateActions($this->jsLayout);
        $this->jsLayout = $this->updateSourceMap($this->jsLayout);

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateActions(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampopup']['children']['amwishlist']['config'])) {
            $jsLayout['components']['ampopup']['children']['amwishlist']['config']['actions']['addNewList']
                = $this->getUrl(PostHelper::CREATE_WISHLIST_ROUTE);
            $jsLayout['components']['ampopup']['children']['amwishlist']['config']['actions']['validateNewName']
                = $this->getUrl(PostHelper::VALIDATE_WISHLIST_NAME_ROUTE, [
                    ValidateWishlistName::CUSTOM_TYPE_PARAM => 1
                ]);
        }

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateSourceMap(array $jsLayout): array
    {
        if (isset($jsLayout['components']['ampopup']['children']['amwishlist']['config'])) {
            $jsLayout['components']['ampopup']['children']['amwishlist']['config']['typesMap'] = $this->type->toArray();
        }

        return $jsLayout;
    }
}
