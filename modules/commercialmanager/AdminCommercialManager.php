<?php

if(version_compare(_PS_VERSION_, '1.5.0.0', '<'))
{
	include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
	include_once(_PS_MODULE_DIR_.'/commercialmanager/commercialmanager.php');
}

class AdminCommercialManager extends AdminTab
{
  private $moduleName = 'commercialmanager';

  public function __construct()
  {
  	if(version_compare(_PS_VERSION_, '1.5.0.0', '<'))
		{
	    global $cookie, $_LANGADM;
	    $langFile = _PS_MODULE_DIR_.$this->moduleName.'/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
	    if(file_exists($langFile))
	    {
	      require_once $langFile;
	      foreach($_MODULE as $key=>$value)
	        if(substr(strip_tags($key), 0, 5) == 'Admin')
	          $_LANGADM[str_replace('_', '', strip_tags($key))] = $value;
	    }
	  }
    parent::__construct();
  }

  public function display()
  {
    $module = Module::getInstanceByName($this->moduleName);
    echo $module->getContent();
  }
}
