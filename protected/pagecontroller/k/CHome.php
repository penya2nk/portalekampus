<?php
prado::using ('Application.MainPageK');
class CHome extends MainPageK {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;               
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
                               
		}                
	}
}
?>