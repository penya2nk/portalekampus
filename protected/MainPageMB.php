<?php
class MainPageMB extends MainPage {         
    /**     
     * show page pendaftaran via Web [spmb]
     */
    public $showFormulirPendaftaran=false;    
    /**     
     * show page soal PMB
     */
    public $showSoalPMB=false;    
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>