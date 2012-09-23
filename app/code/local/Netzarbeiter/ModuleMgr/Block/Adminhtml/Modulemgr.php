<?php

class Netzarbeiter_ModuleMgr_Block_Adminhtml_Modulemgr extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'netzarbeiter_modulemgr';
        $this->_controller = 'adminhtml_modulemgr';
        $this->_headerText = $this->__('Manage Modules');

        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        parent::_prepareLayout();
        return $this;
    }
}
