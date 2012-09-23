<?php

class Netzarbeiter_ModuleMgr_Block_Adminhtml_Modulemgr_View
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'netzarbeiter_modulemgr';
        $this->_controller = 'adminhtml_modulemgr';
        $this->_mode = 'view';
        $this->_headerText = $this->__('View Module %s');
    }

    protected function _prepareLayout()
    {
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');

        //$this->_formScripts[] = "";
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $module = Mage::registry('current_module');
        if ($module) {
            return $module->getModuleName();
        }
        return $this->__('Unknown Module');
    }
}
