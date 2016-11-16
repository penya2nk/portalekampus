<?php
class MainPageON extends MainPage {
    /**     
     * show page transkrip asli [akademik nilai]
     */
    public $showTranskripFinal=false; 
     /**     
     * show page konversi sementara [akademik nilai]
     */
    public $showKonversiMatakuliah=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	           
        }
	}           
}
?>