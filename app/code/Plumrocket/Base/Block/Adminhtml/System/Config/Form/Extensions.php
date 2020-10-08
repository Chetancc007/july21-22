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

namespace Plumrocket\Base\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Phrase;
use Plumrocket\Base\ViewModel\GetExtensionInformationWithUpdates;

/**
 * @since 2.3.0
 */
class Extensions extends Template implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Plumrocket_Base::system/config/extensions.phtml';

    /**
     * @var string
     */
    private $htmlId = '';

    /**
     * @var \Plumrocket\Base\ViewModel\GetExtensionInformationWithUpdates
     */
    private $extensionInformationWithUpdates;

    /**
     * Extensions constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                       $context
     * @param \Plumrocket\Base\ViewModel\GetExtensionInformationWithUpdates $extensionInformationWithUpdates
     * @param array                                                         $data
     */
    public function __construct(
        Context $context,
        GetExtensionInformationWithUpdates $extensionInformationWithUpdates,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->extensionInformationWithUpdates = $extensionInformationWithUpdates;
    }

    /**
     * Render fieldset html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $this->htmlId = $element->getId();
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensionInformationWithUpdates->execute();
    }

    /**
     * @param array $extension
     * @return string
     */
    public function getLabel(array $extension): string
    {
        return "{$extension['name']} {$extension['installedVersion']}";
    }

    /**
     * @param array $extension
     * @return \Magento\Framework\Phrase
     */
    public function getNewUpdateLabel(array $extension): Phrase
    {
        $count = count($extension['updates']);
        return $count === 1 ? __('New Update Available') : __('%1 New Updates Available', $count);
    }

    /**
     * @return string
     */
    public function getHtmlId(): string
    {
        return $this->htmlId;
    }
}
