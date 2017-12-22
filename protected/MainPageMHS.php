<?php
class MainPageMHS extends MainPage {  
    /**     
     * show daftar konsentrasi [akademik]
     */
    public $showDaftarKonsentrasi=false;   
    /**
     * show page konversi sementara [akademik nilai]
     */
    public $showKonversiMatakuliah=false;
     /**     
     * show page pembayaran semester Ganjil [pembayaran]
     */
    public $showPembayaranSemesterGanjil=false;
    /**     
     * show page pembayaran semester Genap [pembayaran]
     */
    public $showPembayaranSemesterGenap=false;
    /**     
     * show page pembayaran semester Pendek [pembayaran]
     */
    public $showPembayaranSemesterPendek=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}