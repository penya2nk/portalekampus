<?php
class MainTemplate extends TTemplateControl {    
    public function onLoad ($param) {
		parent::onLoad($param);	
        if (!$this->Page->IsPostBack&&!$this->Page->IsCallback) {		
            $tipeuser=$this->Page->Pengguna->getTipeUser();
            $this->linkTopTASemester->NavigateUrl=$tipeuser=='m'?$this->Page->constructUrl('settings.Variables',true):'#';            
            $this->loggerJS->Visible=$this->Page->setup->getSettingValue('jslogger');
		}  
	}
    public function logoutUser ($sender,$param) {
        if (!$this->User->isGuest) {
            $this->Application->getModule ('auth')->logout();
            $this->Page->redirect('Login');
		}
    }
}
?>