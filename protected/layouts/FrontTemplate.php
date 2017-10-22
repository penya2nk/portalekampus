<?php
class FrontTemplate extends TTemplateControl {    
    public function onLoad ($param) {
		parent::onLoad($param);
        if (!$this->Page->IsPostBack&&!$this->Page->IsCallback) {		
            $this->loggerJS->Visible=$this->Page->setup->getSettingValue('jslogger');
            $this->literalTotalLulusan->Text=$this->Page->Demik->getJumlahSeluruhMHS('L');
		}  
	}
}
?>