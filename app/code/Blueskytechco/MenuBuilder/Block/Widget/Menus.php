<?php
namespace Blueskytechco\MenuBuilder\Block\Widget;

use Blueskytechco\MenuBuilder\Model\MenuBuilderFactory;
use Blueskytechco\MenuBuilder\ViewModel\MenuBuilder;

class Menus extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/custom_menus.phtml';
    
    /**
     * @var MenuBuilderFactory
     */
    protected $menuBuilderFactory;
    protected $viewModel;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        MenuBuilderFactory $menuBuilderFactory,
        MenuBuilder $viewModel,
        array $data = []
    ){
        $this->menuBuilderFactory = $menuBuilderFactory;
        $this->viewModel = $viewModel;
        parent::__construct($context, $data);
    }
    
    public function _toHtml()
    {
        $id_menu = $this->getData('menus_select');
        $main_menu = $this->getData('main_menu');
        $menu_resource = $this->menuBuilderFactory->create()->getResource();
        $data_menu = $menu_resource->checkMenuBuilderWidget($id_menu);
        
        if ($main_menu && isset($data_menu['type']) && $data_menu['type'] == 1) {
            $this->setData('view_model', $this->viewModel)->setTemplate('widget/horizontal.phtml');
        } elseif ($main_menu && isset($data_menu['type']) && $data_menu['type'] == 2) {
            $this->setData('view_model', $this->viewModel)->setTemplate('widget/vertical.phtml');
        } else {
            $this->setData('view_model', $this->viewModel)->setTemplate('widget/custom_menus.phtml');
        }
        return parent::_toHtml();
    }
    
}

