<?php
/*
 * @Author:    Nathan Chick (nathan.chick@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_BuilderIO
 */

declare(strict_types=1);

namespace DeployEcommerce\BuilderIO\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Connect
 *
 * This class provides the functionality for the "Connect" button in the system configuration.
 * It extends the Magento Form Field and uses a custom template to render the button.
 *
 */
class Connect extends Field
{
    /**
     * @var string
     */
    protected $_template = 'DeployEcommerce_BuilderIO::system/config/connect.phtml';

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for connect button
     *
     * @return string
     */
    public function getAjaxConnectUrl()
    {
        return $this->getUrl('builderio/config/connectionajax', ['store' => $this->getRequest()->getParam('store', 0)]);
    }

    /**
     * Generate Connect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'connect_button',
                'label' => __('Connect to Builder.io Workspace'),
            ]
        );

        return $button->toHtml();
    }
}
