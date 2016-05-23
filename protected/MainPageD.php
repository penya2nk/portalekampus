<?php
class MainPageD extends MainPage {         
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>