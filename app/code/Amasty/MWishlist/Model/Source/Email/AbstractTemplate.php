<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Source\Email;

use Amasty\MWishlist\Model\ResourceModel\Template\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayInterface;

class AbstractTemplate extends DataObject implements ArrayInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var string
     */
    private $origTemplateCode;

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(
        \Magento\Email\Model\Template\Config $emailConfig,
        Collection $collection,
        $origTemplateCode = '',
        array $data = []
    ) {
        parent::__construct($data);
        $this->emailConfig = $emailConfig;
        $this->origTemplateCode = $origTemplateCode;
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->collection->addFieldToFilter('orig_template_code', ['eq' => $this->origTemplateCode])->load();

        $options = $this->collection->toOptionArray();
        array_unshift($options, $this->getDefaultTemplate());

        return $options;
    }

    /**
     * @return array
     */
    private function getDefaultTemplate(): array
    {
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);

        return ['value' => $templateId, 'label' => $templateLabel];
    }
}
