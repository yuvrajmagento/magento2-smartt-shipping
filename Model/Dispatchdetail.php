<?php
namespace Smarttshipping\Shipping\Model;

class Dispatchdetail extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'smart_shipping_dispatchdetail';

    protected $_cacheTag = 'smart_shipping_dispatchdetail';

    protected $_eventPrefix = 'smart_shipping_dispatchdetail';

    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\ResourceModel\Dispatchdetail');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
