<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel;

class GroupAttrOption extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_conf_group_attr_option', 'group_option_id');
    }

    /**
     * @param string $indexTable
     * @param array $groupedIndexData
     * @return void
     */
    public function updateGroupedOptionsIndex($indexTable, array $groupedIndexData = [])
    {
        if (empty($groupedIndexData)) {
            return;
        }

        $this->getConnection()->beginTransaction();
        $this->getConnection()->insertOnDuplicate($indexTable, $groupedIndexData, []);
        $this->getConnection()->commit();
    }
}
