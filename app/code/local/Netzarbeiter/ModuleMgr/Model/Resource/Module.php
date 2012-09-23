<?php

class Netzarbeiter_ModuleMgr_Model_Resource_Module extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eav/attribute', 'attribute_id');
    }

    /**
     * Return all attribute models for the specified module
     *
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getAllAttributeModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        $attributeModels = array_merge(
            $this->getAttributeBackendModels($module),
            $this->getAttributeSourceModels($module),
            $this->getAttributeFrontendModels($module),
            $this->getAttributeModels($module),
            $this->getIncrementModels($module)
        );
        return $attributeModels;
    }

    /**
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getAttributeBackendModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        return $this->_getAttributeModelsData($module, 'backend_model');
    }

    /**
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getAttributeSourceModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        return $this->_getAttributeModelsData($module, 'source_model');
    }

    /**
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getAttributeFrontendModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        return $this->_getAttributeModelsData($module, 'frontend_model');
    }

    /**
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getAttributeModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        return $this->_getAttributeModelsData($module, 'attribute_model');
    }

    /**
     * Fetch increment models associated with the specified module
     * from the eav/entity_type table and build a key => value array
     * out of the result set.
     *
     * Don't use _getAttributeModelsData because of different table.
     *
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @return array
     */
    public function getIncrementModels(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        $models = array();
        $result = $this->_fetchAttributeModels('increment_model', array(
                    $module->getModelClassGroup().'/', $module->getModuleName() . '_'
                ), $this->getTable('eav/entity_type'));

        foreach($result as $attribute) {
            $key = $attribute['entity_type_code'];
            $models[$key] = $attribute['increment_model'];
        }
        return $models;
    }

    /**
     * Fetch a specific attribute model type from the eav/attribute table
     * and build key => value array from the result set.
     *
     *
     * @param Netzarbeiter_ModuleMgr_Model_Module $module
     * @param string $column
     * @return array
     */
    protected function _getAttributeModelsData(Netzarbeiter_ModuleMgr_Model_Module $module, $column)
    {
        $models = array();
        $result = $this->_fetchAttributeModels($column, array(
            $module->getModelClassGroup().'/', $module->getModuleName() . '_'
        ));
        foreach($result as $attribute) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType($attribute['entity_type_id']);
            $key = $column . ' ' . $entityType->getEntityTypeCode() . ' ' . $attribute['attribute_code'];
            $models[$key] = $attribute[$column];
        }
        return $models;
    }

    /**
     * @param string $column
     * @param string|array $prefixes
     * @param null|string $table
     * @return array
     */
    protected function _fetchAttributeModels($column, $prefixes, $table = null)
    {
        if (! is_array($prefixes)) {
            $prefixes = array($prefixes);
        }
        if (is_null($table)) {
            $table = $this->getMainTable();
        }
        $column = $this->_getReadAdapter()->quoteIdentifier($column);
        $select = $this->_getReadAdapter()->select()
                ->from($table);
        foreach ($prefixes as $prefix) {
            $select->orWhere(
                 "{$column} LIKE ?", addcslashes($prefix, '%_') . '%'
            );
        }
        return (array) $this->_getReadAdapter()->fetchAll($select);
    }

    public function getCoreResourceVersion(Netzarbeiter_ModuleMgr_Model_Module $module)
    {
        $version = null;
        if ($module->getSetupResourceName()) {
            $select = $this->_getReadAdapter()->select()
                    ->from($this->getTable('core/resource'), 'version')
                    ->where('code=?', $module->getSetupResourceName());
            $version = $this->_getReadAdapter()->fetchOne($select);
        }
        return $version;
    }
}
