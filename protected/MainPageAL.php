<?php
class MainPageAL extends MainPage {        
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	           
        }
	}           
}
?>