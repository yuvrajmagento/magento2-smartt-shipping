<?php
namespace Smarttshipping\Shipping\Model;

class Addresssetting extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'smart_shipping_addresssetting';

    protected $_cacheTag = 'smart_shipping_addresssetting';

    protected $_eventPrefix = 'smart_shipping_addresssetting';

    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\ResourceModel\Addresssetting');
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
