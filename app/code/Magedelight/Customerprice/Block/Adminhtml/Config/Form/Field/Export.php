<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Config\Form\Field;

/**
 * Export CSV button for customer price.
 *
 */
class Export extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory           $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper                             $escaper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getParent()
            ->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);

        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];

        $urlsPass = $this->_backendUrl->getUrl('md_customerprice/config/export', $params);
        $data = [
            'label' => __('Export CSV'),
            'onclick' => "setLocation('".$urlsPass."' )",
            'class' => '',
        ];

        $htmlToPass = $buttonBlock->setData($data)->toHtml();

        return $htmlToPass;
    }
}
