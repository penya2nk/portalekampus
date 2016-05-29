<?php
class MainPageK extends MainPage {      
    /**     
     * show page rekening [datamaster]
     */
    public $showRekening=false;
    /**     
     * show page komponen biaya per TA [datamaster]
     */
    public $showKombiPerTA=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>