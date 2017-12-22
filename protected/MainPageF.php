<?php
class MainPageF extends MainPage { 
    public function OnPreInit ($param) {	
		parent::onPreInit ($param);	
        $this->createObj('Akademik');
		$this->MasterClass="Application.layouts.FrontTemplate";	
        $this->Theme='limitless';
	}
	public function onLoad ($param) {		
		parent::onLoad($param);	
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}           
}