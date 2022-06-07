<?php
namespace Smarttshipping\Shipping\Block\Create;

class Form extends \Magento\Shipping\Block\Adminhtml\Create\Form
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
    }
}
