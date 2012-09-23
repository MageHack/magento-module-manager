<?php

/**
 * @Todo Implement filtering and sorting of collection
 */
class Netzarbeiter_ModuleMgr_Block_Adminhtml_Modulemgr_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('netzarbeiter_modulemgr');
        $this->setDefaultSort('id');
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/view', array('module' => $item->getId()));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('netzarbeiter_modulemgr/module_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('Name'),
            'sortable' => false,
            'filter' => false,
            'index' => 'module_name'
        ));
        $this->addColumn('version', array(
            'header' => $this->__('Config Version'),
            'index' => 'version',
            'frame_callback' => array($this, 'applyVersionCssCallback'),
            'width' => '100px',
            'sortable' => false,
            'filter' => false,
        ));
        $this->addColumn('core_resource_version', array(
            'header' => $this->__('Core Resource Version'),
            'getter' => 'getCoreResourceVersion',
            'frame_callback' => array($this, 'applyVersionCssCallback'),
            'width' => '100px',
            'sortable' => false,
            'filter' => false,
        ));
        $this->addColumn('code_pool', array(
            'header' => $this->__('Code Pool'),
            'index' => 'code_pool',
            'width' => '100px',
            'sortable' => false,
            'filter' => false,
        ));
        $this->addColumn('state', array(
            'header' => $this->__('State'),
            'index' => 'state',
            'type' => 'options',
            'options' => Netzarbeiter_ModuleMgr_Model_Module::getStateOptions(),
            'frame_callback' => array($this, 'applyStateCssCallback'),
            'sortable' => false,
            'filter' => false,
        ));
        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'module',
                ),
            ),
            'filter' => false,
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }

    public function applyStateCssCallback($renderedValue, $item, $column)
    {
        $colors = $this->getStateOptionColors();
        $state = $item->getState();
        if (isset($colors[$state])) {
            $renderedValue = sprintf('<div style="background-color: %s;">%s</div>', $colors[$state], $renderedValue);
        }
        return $renderedValue;
    }

    public function applyVersionCssCallback($renderedValue, $item, $column)
    {
        if ($item->getCoreResourceVersion()) {
            if ($item->getCoreResourceVersion() != $item->getVersion()) {
                $color = 'khaki';
                $renderedValue = sprintf('<div style="background-color: %s;">%s</div>', $color, $renderedValue);
            }
        }
        return $renderedValue;
    }

    public function getStateOptionColors()
    {
        return array(
            Netzarbeiter_ModuleMgr_Model_Module::STATE_INSTALLED_ACTIVE => 'honeydew',
            Netzarbeiter_ModuleMgr_Model_Module::STATE_INSTALLED_INACTIVE => 'lavenderblush',
            Netzarbeiter_ModuleMgr_Model_Module::STATE_UNINSTALLED_CONFIG => 'khaki',
            Netzarbeiter_ModuleMgr_Model_Module::STATE_UNINSTALLED_REGISTRY_ACTIVE => 'khaki',
            Netzarbeiter_ModuleMgr_Model_Module::STATE_UNINSTALLED_REGISTRY_INACTIVE => 'khaki',
        );
    }
}
