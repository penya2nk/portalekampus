<?php
prado::using ('Application.MainPageM');
class Home extends MainPageM {
	public function onLoad($param) {		
		parent::onLoad($param);	        
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
		}
	}
}
		