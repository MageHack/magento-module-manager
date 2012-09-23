<?php

class Netzarbeiter_ModuleMgr_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check the specified file exists, isn't a directory and is readable
     *
     * @param string $file
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function validateFile($file)
    {
        if (! file_exists(($file)))  {
            Mage::throwException($this->__('File "%s" not found', $file));
        }
        if (is_dir($file)) {
            Mage::throwException($this->__('File "%s" is a directory', $file));
        }
        if (! is_readable($file)) {
            Mage::throwException($this->__('File "%s" mot readable', $file));
        }
        return true;
    }
}
