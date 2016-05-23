<?php

class ResetPassword extends MainPage { 
    public function OnPreInit ($param) {	
		parent::onPreInit ($param);	
		$this->MasterClass="Application.layouts.LoginTemplate";				
        $this->Theme='default';
	}
	public function onLoad($param) {		
		parent::onLoad($param);				
		if (!$this->IsPostBack&&!$this->IsCallBack) {            
            
		}
	}        
    public function doResetPassword ($sender,$param) {
        if ($this->IsValid) { 
            $mail=$this->getLogic('Mail');
            $mail->send();
        }
    }
}
?>