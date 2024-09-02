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
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Controller\Catalog;

use Magento\Catalog\Controller\Product\View as ProductView;
use Magento\Catalog\Helper\Product\View;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\Blog\Controller\Post
 */
class ProductOptions extends ProductView
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * ProductOptions constructor.
     *
     * @param Context $context
     * @param View $viewHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $registry,
        Data $helperData
    ) {
        $this->_helper  = $helperData;
        $this->registry = $registry;

        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
    }

    /**
     * @return Forward|Redirect|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $categoryId     = (int) $this->getRequest()->getParam('category', false);
        $productId      = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        $params = new DataObject();
        $params->setCategoryId($categoryId)
            ->setSpecifyOptions($specifyOptions);
        $params->setAfterHandles(['catalog_product_view']);
        $page = $this->resultPageFactory->create();
        $this->viewHelper->prepareAndRender($page, $productId, $this, $params);

        $product = $this->registry->registry('current_product');

        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            if ($this->_helper->isMpcpgv() === '0') {
                $layout->unsetElement('mpcpgv.product');
            } elseif ($this->_helper->isMpcpgv() === '1') {
                $layout->getUpdate()->removeHandle('default');
                $layout->unsetElement('mp.table.category.view.product.info.options');
            }
        }

        $this->getResponse()->setBody($layout->renderElement('main.content'));
    }
}
