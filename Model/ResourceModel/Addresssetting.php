<?php
namespace Smarttshipping\Shipping\Model\ResourceModel;

class Addresssetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('smart_shipping_customer_settings', 'id');
    }
}
