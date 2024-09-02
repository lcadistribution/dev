<?php
namespace Magebees\Wholesaleconfigurable\Block;
use Magento\Framework\View\Element\Template;
class Form extends Template
{    
	protected $_isScopePrivate;
	protected $product;
	public function __construct(Template\Context $context, 
		\Magento\Catalog\Model\Product $product,
		array $data = [])
    {

        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
		$this->product = $product;
    }

	public function getProduct()
    {
		$productId=($this->getRequest()->getParam('productid'))? $this->getRequest()->getParam('productid') : null;
		if($productId){
			$product = $this->product->load($productId);
			return $product;
		}else{
			return false;
		}
    }
	
}
