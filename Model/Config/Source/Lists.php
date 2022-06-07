<?php
namespace Smarttshipping\Shipping\Model\Config\Source;

class Lists extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_shippingHelper;

    public function __construct(
        \Smarttshipping\Shipping\Helper\Data $shippingHelper
    ) {
        $this->_shippingHelper = $shippingHelper;
    }
    /**
     *
     * @var array
     */
    protected $dataList;

    /**
     *
     * @return array
     */
    public function getList()
    {
        return $this->dataList;
    }
    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        if (!$this->dataList) {
            $this->dataList = $this->_shippingHelper->getAvailableCarrierMethods();
        }

        foreach ($this->dataList as $k => $code) {
            $options[] = ['value' => (string) $k, 'label' => ucwords($code)];
        }

        return $options;
    }

      /**
       *
       * @return array
       */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
