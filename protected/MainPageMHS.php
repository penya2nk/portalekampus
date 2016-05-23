<?php
class MainPageMHS extends MainPage {    
    /**     
     * show daftar konsentrasi [akademik]
     */
    public $showDaftarKonsentrasi=false;    
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>