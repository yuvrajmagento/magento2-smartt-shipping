<?php
namespace Smarttshipping\Shipping\Block;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Smarttshipping\Shipping\Helper\Data $helperdata,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
         $this->_registry = $registry;
        $this->_helperData = $helperdata;
        $this->_pricehelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getProductEstimate($product = null)
    {
        $_product = $this->getCurrentProduct();
        if ($_product->getAttributeText('shipping_type_drop')=="Smart Shipping" && $_product->getShippingLength() && $_product->getShippingWidth() && $_product->getShippingHeight()) {
            $estimate = $this->_helperData->getProductEstimate($this->getCurrentProduct());
            if (isset($estimate['name']) && isset($estimate['price'])) {
                return ["name"=>$estimate['name'], "price"=> $estimate['price']];
            }
        }
    }

    public function formatPrice($price)
    {
        return $this->_pricehelper->currency($price, true, false);
    }
}
