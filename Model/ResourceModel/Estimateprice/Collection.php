<?php
namespace Smarttshipping\Shipping\Model\ResourceModel\Estimateprice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'smart_shipping_estimateprice';
    protected $_eventObject = 'smart_shipping_estimateprice';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\Estimateprice', 'Smarttshipping\Shipping\Model\ResourceModel\Estimateprice');
    }
}
