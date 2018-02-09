<?php
prado::using ('Application.MainPageF');
class Pendaftaran extends MainPageF {
	public function onLoad($param) {		
		parent::onLoad($param);	         
		if (!$this->IsPostBack&&!$this->IsCallBack) {            
           
		}
	}    
}