<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Block\Adminhtml\Report\Filter;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
   

    /**
     * Prepare the form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/customerprice');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );

        $htmlIdPrefix = 'customerprice_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);

        $fieldset->addField(
            'from',
            'date',
            [
                'name' => 'from',
                'date_format' => $dateFormat,
                'label' => __('From'),
                'title' => __('From'),
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField(
            'to',
            'date',
            [
                'name' => 'to',
                'date_format' => $dateFormat,
                'label' => __('To'),
                'title' => __('To'),
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();
        /*foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }*/
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

}
