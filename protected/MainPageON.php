<?php
class MainPageON extends MainPage {    
     /**     
     * show page konversi sementara
     */
    public $showKonversiMatakuliah=false;
    /**     
     * show page nilai per mahasiswa [nilai]
     */
    public $showNilaiPerMahasiswa=false; 
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	           
        }
	}           
}
?>