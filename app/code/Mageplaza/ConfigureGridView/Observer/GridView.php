<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Observer;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Main;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\ConfigureGridView\Helper\Data;
use Mageplaza\ConfigureGridView\Model\Config\Source\Active;

/**
 * Class GridView
 * @package Mageplaza\ConfigureGridView\Observer
 */
class GridView implements ObserverInterface
{
    /**
     * @var Active
     */
    protected $_active;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * GridView constructor.
     *
     * @param Active $active
     * @param Data $data
     */
    public function __construct(
        Active $active,
        Data $data
    ) {
        $this->_active = $active;
        $this->helper  = $data;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $configSource = $this->_active->toOptionArray();

        /** @var Main $form */
        if ($this->helper->isEnabled()) {
            $form     = $observer->getData('form');
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'mp_grid_view',
                'select',
                [
                    'name'   => 'mp_grid_view',
                    'label'  => __('Mageplaza Grid View'),
                    'title'  => __('Mageplaza Grid View'),
                    'note'   => __('Choose Active to allow the attribute to be shown on Configurable Products Grid View'),
                    'values' => $configSource,
                ]
            );
        }

        return $this;
    }
}
