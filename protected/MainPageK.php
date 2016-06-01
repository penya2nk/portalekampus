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
    /**     
     * show page piutang jangka pendek [report]
     */
    public $showReportPiutangJangkaPendek=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}
?>