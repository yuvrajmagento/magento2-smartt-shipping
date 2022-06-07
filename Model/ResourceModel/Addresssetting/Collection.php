<?php
namespace Smarttshipping\Shipping\Model\ResourceModel\Addresssetting;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'smart_shipping_addresssetting';
    protected $_eventObject = 'smart_shipping_addresssetting';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\Addresssetting', 'Smarttshipping\Shipping\Model\ResourceModel\Addresssetting');
    }
}
