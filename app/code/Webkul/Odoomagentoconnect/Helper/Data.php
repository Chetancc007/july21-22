<?php
/**
 * Webkul Odoomagentoconnect Data Helper
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Odoomagentoconnect\Helper;

class Data extends \Magento\Search\Helper\Data
{
    public function __construct(
        \Magento\Backend\Model\Session $session
    ) {
    
        $this->_session = $session;
    }

    public function setToSession($erpCateg, $erpLang, $erpWarehouse, $erpInstance)
    {
        $this->_session->setErpCateg($erpCateg);
        $this->_session->setErpLang($erpLang);
        $this->_session->setErpWarehouse($erpWarehouse);
        $this->_session->setErpInstance($erpInstance);
    }
}
