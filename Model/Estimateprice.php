<?php
namespace Smarttshipping\Shipping\Model;

class Estimateprice extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'smart_shipping_estimateprice';

    protected $_cacheTag = 'smart_shipping_estimateprice';

    protected $_eventPrefix = 'smart_shipping_estimateprice';

    protected function _construct()
    {
        $this->_init('Smarttshipping\Shipping\Model\ResourceModel\Estimateprice');
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
