<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Base\Block\Adminhtml\System\Config\Developer;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * @since 2.3.0
 */
class MagentoInfo extends Field
{

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * MagentoInfo constructor.
     *
     * @param \Magento\Backend\Block\Template\Context         $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime     $dateTime
     * @param array                                           $data
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryList = $directoryList;
        $this->dateTime = $dateTime;
    }

    /**
     * Render fieldset html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) : string
    {
        $field = (string) $element->getData('field_config/id');
        $element->setValue($this->getMagentoData($field));
        return parent::render($element);
    }

    /**
     * @param string $field
     * @return string
     */
    private function getMagentoData(string $field)
    {
        switch ($field) {
            case 'magento_mode':
                return $this->_appState->getMode();
            case 'magento_path':
                return $this->directoryList->getRoot();
            case 'time':
                return $this->dateTime->date();
            default:
                return '';
        }
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element) : string
    {
        return '';
    }
}
