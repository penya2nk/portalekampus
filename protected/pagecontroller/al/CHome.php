<?php
prado::using ('Application.MainPageAL');
class CHome extends MainPageAL {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;               
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
                               
		}                
	}
}
?>