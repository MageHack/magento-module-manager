<?php

class Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
    extends Varien_Data_Collection
{
    /**
     * If set, only load the module with the specified module names
     *
     * @var array
     */
    protected $_moduleNameFilter = array();

    public function __construct()
    {
        $this->_itemObjectClass = Mage::getConfig()->getModelClassName('netzarbeiter_modulemgr/module');
        parent::__construct();
    }

    /**
     * @param string|array $moduleName
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     */
    public function addModuleNameFilter($moduleName)
    {
        if (! is_array($moduleName)) {
            $moduleName = array($moduleName);
        }
        $this->_moduleNameFilter = array_merge($this->_moduleNameFilter, $moduleName);

        return $this;
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (! $this->isLoaded()) {
            $this->clear();
            $this->_loadModulesFromRegistry();
            $this->_loadModulesFromCoreResource();
            $this->_loadModulesFromAttributeModels();
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     */
    protected function _loadModulesFromRegistry()
    {
        $files = $this->_getModuleRegistryXmlFiles();
        foreach ($files as $file) {
            foreach ($this->_getModulesFromRegistryFile($file) as $moduleName => $moduleConfig) {

                // Mage_Core isn't a regular module
                if ('Mage_Core' === $moduleName) continue;

                if ($this->_moduleNameFilter && ! in_array($moduleName, $this->_moduleNameFilter)) {
                    continue;
                }

                $module = $this->getNewEmptyItem()->loadByRegistryConfig($moduleName, $moduleConfig);
                $module->setRegistryFile($file);
                $this->addItem($module);
            }
        }
        return $this;
    }

    /**
     * @param $file
     * @return array
     */
    protected function _getModulesFromRegistryFile($file)
    {
        $this->_validateFile($file);
        $xml = simplexml_load_file($file);
        return $xml->modules->children();
    }

    /**
     * Check the specified file exists, isn't a directory and is readable
     *
     * @param string $file
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _validateFile($file) {
        return $this->helper()->validateFile($file);
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('netzarbeiter_modulemgr');
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     * @todo: implement
     */
    protected function _loadModulesFromCoreResource()
    {
        return $this;
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     * @todo: implement
     */
    protected function _loadModulesFromAttributeModels()
    {
        return $this;
    }

    /**
     * Return all module registry files in the order they will be merged by Magento.
     *
     * @return array
     */
    protected function _getModuleRegistryXmlFiles()
    {
        $registryDir = $this->_getRegistryDir();
        $files = array($registryDir . DS . 'Mage_All.xml');
        $magefiles = array_filter(glob($registryDir . DS . 'Mage_*.xml'), array($this, '_removeMageAllFileCallback'));
        $nonMageFiles = array_filter(glob($registryDir . DS . '*.xml'), array($this, '_removeMageFilesCallback'));
        return array_merge(
            $files,
            $magefiles,
            $nonMageFiles
        );
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function _removeMageAllFileCallback($file)
    {
        return basename($file) !== 'Mage_All.xml';
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function _removeMageFilesCallback($file)
    {
        return strpos(basename($file), 'Mage_') !== 0;
    }

    /**
     * @return string
     */
    protected function _getRegistryDir()
    {
        $dir = Mage::getConfig()->getOptions()->getEtcDir() . DS . 'modules';
        return $dir;
    }

    /**
     * Override parent method to provide path hint
     *
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function getNewEmptyItem()
    {
        return parent::getNewEmptyItem();
    }
}
