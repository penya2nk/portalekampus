<?php
class MainPageK extends MainPage {      
     /**     
     * show page rekening [datamaster]
     */
    public $showRekening=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>