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
    /**     
     * show page nilai per matakuliah [nilai]
     */
    public $showNilaiPerMatakuliah=false;
     /**     
     * show page nilai final yang akan lulus [nilai]
     */
    public $showNilaiFinal=false;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	           
        }
	}           
}
?>