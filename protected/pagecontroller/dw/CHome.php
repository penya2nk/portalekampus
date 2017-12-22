<?php
prado::using ('Application.MainPageDW');
class CHome extends MainPageDW {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;               
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
                               
		}                
	}
}