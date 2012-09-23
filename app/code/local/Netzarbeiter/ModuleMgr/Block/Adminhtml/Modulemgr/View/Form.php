<?php

class Netzarbeiter_ModuleMgr_Block_Adminhtml_Modulemgr_View_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Netzarbeiter_ModuleMgr_Model_Module
     */
    public function getModule()
    {
        return Mage::registry('current_module');
    }
}
