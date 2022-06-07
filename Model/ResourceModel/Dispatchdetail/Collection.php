<?php
namespace Smarttshipping\Shipping\Model\ResourceModel\Dispatchdetail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'smart_shipping_dispatchdetail';
    protected $_eventObject = 'smart_shipping_dispatchdetail';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\Dispatchdetail', 'Smarttshipping\Shipping\Model\ResourceModel\Dispatchdetail');
    }
}
