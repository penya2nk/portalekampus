<?php
prado::using ('Application.MainPageM');
class CDetailDulangMHSBaru Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSBaru=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            try {
                if (isset($_SESSION['currentPageDulangMHSBaru']['DataMHS']['no_formulir'])) {
                    
                }else{
                    
                }
            } catch (Exception $ex) {

            }
		}	
	}
}
?>