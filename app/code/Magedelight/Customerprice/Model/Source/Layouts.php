<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Source;

class Layouts
{
    /**
    * @var Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
    */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     */
    public function __construct(
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    public function toOptionArray()
    {
        $result = [
            [
                'value' => '',
                'label' => '-- Please Select --'
            ],
            [
                'value' => 'empty',
                'label' => 'Empty'
            ],
            [
                'value' => '1column',
                'label' => '1 column'
            ],
            [
                'value' => '2columns-left',
                'label' => '2 columns with left bar'
            ],
            [
                'value' => '2columns-right',
                'label' => '2 columns with right bar'
            ],
            [
                'value' => '3columns',
                'label' => '3 columns'
            ]
        ];

        //$result = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(true);

        return $result;
    }
}
