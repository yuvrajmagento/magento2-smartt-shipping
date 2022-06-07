<?php

namespace Smarttshipping\Shipping\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Syncaddress extends Field
{
     /**
      * @var string
      */
    protected $_template = 'Smart_Shipping::system/config/syncaddress.phtml';

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('smartshipping/system_config/connect');
    }

    public function getButtonText()
    {
        return __('Sync address');
    }

    public function getButtonErrorText()
    {
        return __('Failed');
    }

    public function getButtonSuccessText()
    {
        return __('Sync Successful');
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        /*$button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'connect_button',
                'label' => $this->getButtonText(),
            ]
        );
        return $button->toHtml();*/
        return false;
    }
}
