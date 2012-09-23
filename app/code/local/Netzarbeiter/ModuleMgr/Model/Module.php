<?php

/**
 * @method string getModuleName
 * @method string getRegistryFileState
 * @method string getConfigXmlExists
 * @method string getLoadedVia
 * @method string getCodePool
 * @method string getRegistryFile
 * @method string getModuleDir
 * @method string getVersion
 * @method string getResourceTableVersion
 * @method string getConfigVersion
 * @method string getModelClassGroup
 * @method string getResourceModelClassGroup
 * @method string getBlockClassGroup
 * @method string getHelperClassGroup
 * @method string getFrontendLayout
 * @method string getAdminhtmlLayout
 * @method string getFrontendTranslationFiles
 * @method string getAdminhtmlTranslationFiles
 * @method string getSetupResourceName
 * @method string getSetupResourceDefaultClass
 *
 * @Todo Delegate the loading of data instead of doing it all in the model
 */
class Netzarbeiter_ModuleMgr_Model_Module extends Varien_Object
{
    const LOADED_VIA_FILE_REGISTRY = 'registry_file';
    const LOADED_VIA_DB_REGISTRY = 'core_resource';

    /** Registry and config files exists, <active> is true */
    const STATE_INSTALLED_ACTIVE = 'installed_active';
    /** Registry and config files exists, <active> is false */
    const STATE_INSTALLED_INACTIVE = 'installed_inactive';
    /** Only config files exists */
    const STATE_UNINSTALLED_CONFIG = 'uninstalled_config';
    /** Only registry files exists, <activ> is true */
    const STATE_UNINSTALLED_REGISTRY_ACTIVE = 'uninstalled_registry_active';
    /** Only registry files exists, <activ> is false */
    const STATE_UNINSTALLED_REGISTRY_INACTIVE = 'uninstalled_registry_inactive';

    protected $_idFieldName = 'module_name';

    /**
     *
     * @param string $moduleName
     * @param SimpleXMLElement $moduleConfig
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function loadByRegistryConfig($moduleName, SimpleXMLElement $moduleConfig)
    {
        $this->setData(array());
        $this->_processRegistryConfig($moduleName, $moduleConfig);
        $this->_loadModuleConfig();
        $this->_loadAdditionalData();

        return $this;
    }

    /**
     * Load the first module defined in the specified module registry file
     *
     * @param string $file
     * @param string|null $moduleToLoad
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function loadByRegistryFile($file, $moduleToLoad = null)
    {
        $this->setData(array());

        $this->_loadRegistryFile($file, $moduleToLoad);
        $this->_loadModuleConfig();
        $this->_loadAdditionalData();

        return $this;
    }

    /**
     * @param $file
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function loadByModuleConfigFile($file)
    {
        $this->setData(array());

        $this->_loadModuleConfig($file);
        $this->_loadRegistryFile();
        $this->_loadAdditionalData();

        return $this;
    }

    /**
     * @param string $moduleName
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function loadByModuleName($moduleName)
    {
        $this->setData(array());
        $collection = $this->getCollection()->addModuleNameFilter($moduleName);
        if (count($collection)) {
            $this->setData($collection->getFirstItem()->getData());
        }
        return $this;
    }

    /**
     * @param string $file
     * @param string|null $moduleToLoad
     * @return Netzarbeiter_ModuleMgr_Model_Modul
     */
    protected function _loadRegistryFile($file = null, $moduleToLoad = null)
    {
        if (! isset($file)) {
            $file = Mage::getBaseDir('etc') . DS . $this->getRegistryFile();
        }
        $this->_validateFile($file);

        $xml = $this->_getXmlFileContents($file);
        foreach ($xml->modules->children() as $moduleName => $moduleConfig) {
            if (isset($moduleToLoad) && $moduleName != $moduleToLoad) {
                continue;
            }
            $this->_processRegistryConfig($moduleName, $moduleConfig);
            $this->setRegistryFile($file);
            break;
        }

        return $this;
    }

    /**
     * @param string $moduleName
     * @param SimpleXMLElement $moduleConfig
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    protected function _processRegistryConfig($moduleName, SimpleXMLElement $moduleConfig)
    {
        $moduleDir = Mage::getBaseDir('code') . DS. $moduleConfig->codePool . DS . uc_words($moduleName, DS);

        $this->addData(array(
            'module_name'         => $moduleName,
            'loaded_via'          => self::LOADED_VIA_FILE_REGISTRY,
            'code_pool'           => (string) $moduleConfig->codePool,
            'registry_file_state' => (string) $moduleConfig->active,
            'module_dir'          => $moduleDir,
        ));

        return $this;
    }

    /**
     * @param string $file
     * @return Netzarbeiter_ModuleMgr_Model_Module
     * @todo This method is to long, delegate and split up
     */
    protected function _loadModuleConfig($file = null)
    {
        if (! isset($file)) {
            $file = $this->getModuleDir() . DS . 'etc' . DS . 'config.xml';
        }
        try {
            $this->_validateFile($file);

            $this->setConfigXmlExists(true);

            $xml = $this->_getXmlFileContents($file);

            if (! ($moduleName = $this->getModuleName())) {
                foreach ($xml->modules->children() as $moduleName => $moduleConfig) break;
                $this->setModuleName($moduleName);
            }

            $versionConfig = $xml->xpath("modules/$moduleName/version");
            $version = count($versionConfig) > 0 ? (string) ($versionConfig[0]) : null;

            $modelClassGroup = null;
            $resourceModelClassGroup = null;
            $blockClassGroup = null;
            $helperClassGroup = null;

            if ($xml->global->models) {
                foreach ($xml->global->models->children()  as $modelClassGroup  => $modelConfig) break;
                if ($modelConfig && $modelConfig->resourceModel) $resourceModelClassGroup = (string) $modelConfig->resourceModel;
            }

            if ($xml->global->blocks) {
                foreach ($xml->global->blocks->children()  as $blockClassGroup  => $blockConfig) break;
            }

            if ($xml->global->helpers) {
                foreach ($xml->global->helpers->children() as $helperClassGroup => $helperConfig) break;
            }

            $frontendLayoutConfig = $xml->xpath('frontend/layout/updates/*/file');
            $frontendLayout       = count($frontendLayoutConfig) > 0 ? (string) $frontendLayoutConfig[0] : null;
            $adminLayoutConfig    = $xml->xpath('adminhtml/layout/updates/*/file');
            $adminLayout          = count($adminLayoutConfig) > 0 ? (string) $adminLayoutConfig[0] : null;


            $frontendTranslations = $xml->xpath("frontend/translate/modules/$moduleName/files");
            $adminhtmlTranslations = $xml->xpath("adminhtml/translate/modules/$moduleName/files");

            /** @var SimpleXmlElement $setupResourceConfig */
            $setupResourceName = null;
            $setupClass = null;
            foreach ($xml->xpath('global/resources/*/setup/..') as $setupResourceConfig) {
                $setupResourceName = $setupResourceConfig->getName();
                $setupClass = (string) $setupResourceConfig->setup->class;
                $setupClass = $setupClass ? $setupClass : 'Mage_Core_Model_Resource_Setup';
            }

            $this->addData(array(
                'version'                      => false === $version ? null : $version,
                'model_class_group'            => $modelClassGroup,
                'resource_model_class_group'   => $resourceModelClassGroup,
                'block_class_group'            => $blockClassGroup,
                'helper_class_group'           => $helperClassGroup,
                'frontend_layout'              => $frontendLayout,
                'adminhtml_layout'             => $adminLayout,
                'frontend_translation_files'   => $frontendTranslations ? $frontendTranslations : null,
                'adminhtml_translation_files'  => $adminhtmlTranslations ? $adminhtmlTranslations : null,
                'setup_resource_name'          => $setupResourceName,
                'setup_resource_default_class' => $setupClass,
            ));

        } catch(Mage_Core_Exception $e) {
            $this->setErrorProcessingConfigXml(true);
        }
        return $this;
    }

    /**
     * Load additional information
     *
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    protected function _loadAdditionalData()
    {
        $state = null;
        if ($this->getRegistryFileState() === 'true' && $this->getConfigXmlExists()) {
            $state = self::STATE_INSTALLED_ACTIVE;

        } elseif ($this->getRegistryFileState() !== 'true' && $this->getConfigXmlExists()) {
            $state = self::STATE_INSTALLED_INACTIVE;

        } elseif ($this->getRegistryFileState() === 'true' && ! $this->getConfigXmlExists()) {
            $state = self::STATE_UNINSTALLED_REGISTRY_ACTIVE;

        } elseif ($this->getRegistryFileState() !== 'true' && ! $this->getConfigXmlExists()) {
            $state = self::STATE_UNINSTALLED_REGISTRY_INACTIVE;

        } elseif (! $this->getRegistryFile() && $this->getConfigXmlExists()) {
            $state = self::STATE_UNINSTALLED_CONFIG;

        }
        $this->setState($state);
        return $this;
    }

    public function getAttributeModels()
    {
        $data = $this->_getData('attribute_models');
        if (is_null($data)) {
            $data = $this->_getAttributeModels();
            $this->setAttributeModels($data);
        }
        return $data;
    }

    public function getDependentModules()
    {
        $data = $this->_getData('dependent_modules');
        if (is_null($data)) {
            $data = $this->_getDependentActiveModules();
            $this->setDependentModules($data);
        }
        return $data;
    }

    public function getCoreResourceVersion()
    {
        $data = $this->_getData('core_resource_version');
        if (is_null($data)) {
            $data = $this->getResource()->getCoreResourceVersion($this);
            $this->setCoreResourceVersion($data);
        }
        return $data;
    }

    /**
     * Find attribute models that belong to this module
     *
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    protected function _getAttributeModels()
    {
        $this->getResource()->getAllAttributeModels($this);
        return $this;
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
     * Returns a SimpleXmlElement for the specified XML file
     *
     * @param string $file
     * @return SimpleXMLElement
     */
    protected function _getXmlFileContents($file)
    {
        return simplexml_load_file($file);
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('netzarbeiter_modulemgr');
    }

    /**
     * Return array of modules dependent on this one
     *
     * @return array
     */
    public function _getDependentActiveModules()
    {
        $dependentModules = array();
        if ($this->getModuleName()) {
            $xpath = "modules/*[depends/{$this->getModuleName()}]";
            if ($moduleConfig = Mage::getConfig()->getNode()->xpath($xpath)) {
                foreach ($moduleConfig as $node) {
                    $dependentModules[] = $node->getName();
                }
            }
        }
        return $dependentModules;
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('netzarbeiter_modulemgr/module');
    }

    /**
     * @return Netzarbeiter_ModuleMgr_Model_Resource_Module_Collection
     */
    public function getCollection()
    {
        return Mage::getResourceModel('netzarbeiter_modulemgr/module_collection');
    }

    public static function getStateOptions()
    {
        $helper = Mage::helper('netzarbeiter_modulemgr');
        return array(
            self::STATE_INSTALLED_ACTIVE => $helper->__('Installed and active'),
            self::STATE_INSTALLED_INACTIVE => $helper->__('Installed but inactive'),
            self::STATE_UNINSTALLED_CONFIG => $helper->__('No registry file found but module config.xml exists'),
            self::STATE_UNINSTALLED_REGISTRY_ACTIVE => $helper->__('Active in registry, no module config.xml'),
            self::STATE_UNINSTALLED_REGISTRY_INACTIVE => $helper->__('Inactive in registry, no module config.xml'),
        );
    }
}
