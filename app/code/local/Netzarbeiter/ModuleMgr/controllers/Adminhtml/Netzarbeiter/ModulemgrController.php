<?php

class Netzarbeiter_ModuleMgr_Adminhtml_Netzarbeiter_ModulemgrController
            extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                ->isAllowed('system/tools/netzarbeiter_modulemgr');
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
                ->_title($this->__('Tools'))
                ->_title($this->__('Module Manager'));

        $this->loadLayout();
        $this->_setActiveMenu('system/tools/netzarbeiter_modulemgr');
        $this->renderLayout();
    }

    public function viewAction()
    {
        try {
            $this->_initModule();

            $this->_title($this->__('System'))
                    ->_title($this->__('Tools'))
                    ->_title($this->__('Module Manager'));

            $this->loadLayout();
            $this->_setActiveMenu('system/tools/netzarbeiter_modulemgr');
            $this->renderLayout();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/index');
        }
    }


    protected function _initModule()
    {
        $moduleName = $this->getRequest()->getParam('module', false);
        if (false === $moduleName) {
            Mage::throwException($this->__('No module specified'));
        }
        /** @var $module Netzarbeiter_ModuleMgr_Model_Module */
        $module = Mage::getModel('netzarbeiter_modulemgr/module');
        $module->loadByModuleName($moduleName);
        Mage::register('current_module', $module);
        return $this;
    }
}
