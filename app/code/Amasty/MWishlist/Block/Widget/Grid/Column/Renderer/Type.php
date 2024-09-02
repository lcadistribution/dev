<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Widget\Grid\Column\Renderer;

use Amasty\MWishlist\Model\Source\Type as TypeSource;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Type extends AbstractRenderer
{
    /**
     * @var TypeSource
     */
    private $typeSource;

    /**
     * @var array|null
     */
    private $types;

    public function __construct(
        TypeSource $typeSource,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->typeSource = $typeSource;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        return $this->escapeHtml($this->getTypeLabel((int) $row->getData($this->getColumn()->getIndex())));
    }

    /**
     * @param int $typeId
     * @return string
     */
    private function getTypeLabel(int $typeId): string
    {
        if ($this->types === null) {
            $this->types = $this->typeSource->toArray();
        }

        return (string) $this->types[$typeId];
    }
}
