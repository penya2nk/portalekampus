<?php
prado::using ('Application.MainPageD');
class CHome extends MainPageD {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;               
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
                               
		}                
	}
}