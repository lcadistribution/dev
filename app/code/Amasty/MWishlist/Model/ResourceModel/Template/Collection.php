<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Template;

class Collection extends \Magento\Email\Model\ResourceModel\Template\Collection
{
    public function toOptionArray(): array
    {
        $this->filterByTemplateCode(['eq' => 'mwishlist_price_alert_notifications_template']);

        return $this->_toOptionArray('template_id', 'template_code');
    }

    public function filterByTemplateCode(array $condition): void
    {
        $this->addFieldToFilter('orig_template_code', $condition);
    }
}
