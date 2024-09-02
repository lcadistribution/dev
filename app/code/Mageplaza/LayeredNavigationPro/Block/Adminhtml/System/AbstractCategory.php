<?php
namespace Mageplaza\LayeredNavigationPro\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class AbstractCategory
 * @package Mageplaza\LayeredNavigationPro\Block\Adminhtml\System
 */
abstract class AbstractCategory extends Field implements BlockInterface
{
    const CONFIG_DATA_STATE  = 'layered_navigation/filter/state/categories';
    const CONFIG_DATA_RATING = 'layered_navigation/filter/rating/categories';

    /**
     * @var array
     */
    protected $categoriesTree;
    /**
     * @var CategoryCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * AbstractCategory constructor.
     *
     * @param Context $context
     * @param CategoryCollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        CategoryCollectionFactory $collectionFactory,
        RequestInterface $request,
        Data $helperData,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request           = $request;
        $this->_helperData       = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return array|mixed
     * @throws LocalizedException
     */
    protected function getOptions()
    {
        return $this->getCategoriesTree();
    }

    /**
     * get Active Category
     * @return array|mixed
     * @throws LocalizedException
     */
    protected function getCategoriesTree()
    {
        if ($this->categoriesTree === null) {
            $storeId                 = $this->request->getParam('store');
            $matchingNamesCollection = $this->collectionFactory->create();

            $matchingNamesCollection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->setStoreId($storeId);

            $shownCategoriesIds = [];

            /** @var CategoryModel $category */
            foreach ($matchingNamesCollection as $category) {
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }

            $collection = $this->collectionFactory->create();

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
                ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
                ->setStoreId($storeId);

            $categoryById = [
                CategoryModel::TREE_ROOT_ID => [
                    'value' => CategoryModel::TREE_ROOT_ID
                ],
            ];

            foreach ($collection as $category) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId];
                    }
                }
                if ($category->getIsActive()) {
                    $categoryById[$category->getId()]['is_active']        = $category->getIsActive();
                    $categoryById[$category->getId()]['label']            = $category->getName();
                    $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
                }

            }

            $this->categoriesTree = $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
        }

        return $this->categoriesTree;
    }

    /**
     * @param $typeCategory
     *
     * @return array
     */
    public function getValues($typeCategory)
    {
        $values = $this->getValuesConfig($typeCategory);
        if (empty($values)) {
            return [];
        }

        $options    = [];
        $collection = $this->collectionFactory->create()->addIdFilter($values);
        foreach ($collection as $category) {
            /** @var Collection $category */
            $options[] = $category->getId();
        }

        return $options;
    }

    /**
     * @param $typeCategory
     *
     * @return false|mixed|string[]
     */
    public function getValuesConfig($typeCategory)
    {
        $values = [];
        if ($typeCategory === 'rating') {
            $values = $this->getConfigData(self::CONFIG_DATA_RATING);
            if (!$values) {
                $values = $this->_helperData->getCategoryRating();
            }
        } elseif ($typeCategory === 'state') {
            $values = $this->getConfigData(self::CONFIG_DATA_STATE);
            if (!$values) {
                $values = $this->_helperData->getCategoryState();
            }
        }

        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        return $values;
    }

    /**
     * @return string
     */
    public function getScripHtmlAddDisable()
    {
        $script = <<<SCRIPT
        <script type="text/javascript">
            require([
                'jquery'
            ], function ($) {
               if ($('#layered_navigation_filter_state_categories_inherit').attr('checked') === 'checked'){
                   let elState =  $('#layered_navigation_state_categories');
                    elState.parent().addClass('mp_custom_disabled_cursor');
                    elState.addClass('mp_custom_disabled');
               }
               if ($('#layered_navigation_filter_rating_categories_inherit').attr('checked') === 'checked'){
                   let elState =  $('#layered_navigation_rating_categories').parent();
                     elState.parent().addClass('mp_custom_disabled_cursor');
                     elState.addClass('mp_custom_disabled');
               }
                $('#layered_navigation_filter .use-default').each(function() {
                  $(this).click(function() {
                      let el = $(this).parent();
                      if (!el.find(".admin__control-text.admin__action-multiselect-search").hasClass('disabled')){
                         el.find('.value .mp_custom_disabled_cursor').removeClass('mp_custom_disabled_cursor');
                         el.find('.value .mp_custom_disabled').removeClass('mp_custom_disabled');
                      }else {
                         el.find('.value .admin__field-control').addClass('mp_custom_disabled_cursor');
                         el.find('.value .admin__field').addClass('mp_custom_disabled');
                      }
                  })
                })

            });
        </script>
SCRIPT;

        return $script . $this->getHtmlStyle();
    }

    public function getHtmlStyle()
    {
        return <<<EOF
        <style>
             #layered_navigation_filter .mp_custom_disabled{
                    opacity: 0.5;
                    pointer-events: none;
             }
               #layered_navigation_filter .mp_custom_disabled_cursor{
                    cursor: not-allowed;
             }
        </style>
EOF;
    }
}
