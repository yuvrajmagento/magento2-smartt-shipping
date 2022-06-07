<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smarttshipping\Shipping\Helper;

/**
 * Configuration data of carrier
 *
 * @api
 * @since 100.0.2
 */
class Config
{
    protected $_shippingHelper;

    public function __construct(
        \Smarttshipping\Shipping\Helper\Data $shippingHelper
    ) {
        $this->_shippingHelper = $shippingHelper;
    }
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|string|false
     */
    public function getCode($type, $code = '')
    {
        $codes = $this->getCodes();
        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getCodes()
    {
        $options = [];
        
        $dataList = $this->_shippingHelper->getAvailableCarrierMethods();
        foreach ($dataList as $k => $code) {
            $options[$k] = __($code);
        }

        return ['method' => $options];
    }
}
