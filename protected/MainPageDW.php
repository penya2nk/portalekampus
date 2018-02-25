<?php
class MainPageDW extends MainPage {
    /**     
     * show page daftar mahasiswa [akademik kemahasiswaan]
     */
    public $showDaftarMahasiswa=false;    
    /**     
     * show sub menu akademik daftar ulang [akademik daftar ulang mahasiswa baru]
     */
    public $showDulangMHSBaru=false;    
    /**     
     * show sub menu akademik daftar ulang [akademik daftar ulang mahasiswa ekstension]
     */
    public $showDulangMHSEkstension=false;
    /**     
     * show page penyelenggaraan [perkuliahan]
     */
    public $showPenyelenggaraan=false;
    /**     
     * show page pembagian kelas [perkuliahan]
     */
    public $showPembagianKelas=false;      
    /**     
     * show page KRS Kelas Ekstension [perkuliahan]
     */
    public $showKRSEkstension=false;
    /**     
     * show page Perubahan KRS [perkuliahan]
     */
    public $showPKRS=false;
    /**     
     * show page Peserta matakuliah [perkuliahan]
     */
    public $showPesertaMatakuliah=false;
    /**     
     * show page transkrip asli [akademik nilai]
     */
    public $showTranskripFinal=false;
    /**
     * ID Dosen Wali
     * @var integer
     */
    public $iddosen_wali;
	public function onLoad ($param) {		
		parent::onLoad($param);				
        $this->iddosen_wali = $this->Pengguna->getDataUser('iddosen_wali');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            
        }
	}   
}