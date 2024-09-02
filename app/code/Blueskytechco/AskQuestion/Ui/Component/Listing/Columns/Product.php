<?php

namespace Blueskytechco\AskQuestion\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CommentContent
 * @package Careshop\CommunityIdea\Ui\Component\Listing\Columns
 */
class Product extends Column
{

    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    protected $_productloader;

    /**
     * Actions constructor.
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $components = [],
        array $data = []
    ) {
        $this->_productloader = $_productloader;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $product_id = $item['product_id'];
                    $product = $this->_productloader->create()->load($product_id);
                    $item[$this->getData('name')] = '<a target="_blank" href="'.$this->urlBuilder->getUrl('catalog/product/edit/id/'.$product_id.'').'">'.$product->getName().'</a>';
                }
            }
        }

        return $dataSource;
    }
}
