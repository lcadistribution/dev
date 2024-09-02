<?php

  namespace Artnetis\Customtinymce\Plugin;


  class Config
  {

    protected $activeEditor;

    public function __construct(\Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor)
    {
      $this->activeEditor = $activeEditor;
    }

    /**
     * Return WYSIWYG configuration
     *
     * @param \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetConfig(
      \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
      \Magento\Framework\DataObject $result
    ) {

      // Get current wysiwyg adapter's path
      $editor = $this->activeEditor->getWysiwygAdapterPath();

      // Is the current wysiwyg tinymce v4?


        if (($result->getDataByPath('settings/menubar')) || ($result->getDataByPath('settings/toolbar')) || ($result->getDataByPath('settings/plugins'))){
          // do not override ui_element config (unsure if this is needed)
          return $result;
        }

        $settings = $result->getData('settings');

        if (!is_array($settings)) {
          $settings = [];
        }

        // configure tinymce settings
        $settings['menubar'] = true;
        $settings['toolbar'] = 'undo redo | styleselect | casechange | fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | image |  media | magentowidget | code';
        $settings['plugins'] = 'textcolor image code media magentowidget casechange';

        $result->setData('settings', $settings);
        return $result;


    }
  }
