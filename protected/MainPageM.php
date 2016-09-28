<?php
class MainPageM extends MainPage {   
    /**     
     * show page matakuliah [datamaster]
     */
    public $showMatakuliah=false;
    /**     
     * show sub menu kuesioner [dmaster kuesioner]
     */
    public $showSubMenuKuesioner=false;
    /**     
     * show page Kelompok Pertanyaan [dmaster kuesioner]
     */
    public $showKelompokPertanyaan=false;
    /**     
     * show page Daftar Pertanyaan [dmaster kuesioner]
     */
    public $showDaftarPertanyaan=false;
    /**     
     * show page daftar mahasiswa [akademik kemahasiswaan]
     */
    public $showDaftarMahasiswa=false;   
    /**     
     * show page pendaftaran konsentrasi [akademik kemahasiswaan]
     */
    public $showPendaftaranKonsentrasi=false; 
    /**     
     * show page status mahasiswa [akademik kemahasiswaan]
     */
    public $showRekapStatusMahasiswa=false;
    /**     
     * show sub menu akademik daftar ulang [akademik daftar ulang calon mahasiswa]
     */
    public $showCalonMHS=false;        
    /**     
     * show sub menu akademik daftar ulang [akademik daftar ulang mahasiswa baru]
     */
    public $showDulangMHSBaru=false;    
    /**     
     * show sub menu akademik daftar ulang [akademik daftar ulang mahasiswa ekstension]
     */
    public $showDulangMHSEkstension=false;
    /**     
     * show page variable [setting variable]
     */
    public $showVariable=false;
    /**     
     * show page konversi sementara [spmb]
     */
    public $showKonversiMatakuliah=false;
    /**     
     * show sub menu spmb pendaftaran
     */
    public $showSubMenuSPMBPendaftaran=false;
    /**     
     * show page pendaftaran via fo [spmb pendaftaran]
     */
    public $showPendaftaranViaFO=false;
    /**     
     * show page pendaftaran via Web [spmb pendaftaran]
     */
    public $showPendaftaranViaWeb=false;    
    /**     
     * show sub menu spmb Ujian PMB
     */
    public $showSubMenuSPMBUjianPMB=false;
    /**     
     * show page passing grade [ujian PMB]
     */
    public $showPassingGradePMB=false; 
    /**     
     * show page nilai ujian [ujian PMB]
     */
    public $showNilaiUjianPMB=false; 
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
     * show page Peserta matakuliah [perkuliahan]
     */
    public $showPesertaMatakuliah=false;
    /**     
     * show page transkrip asli [akademik nilai]
     */
    public $showTranskripFinal=false;
    /**     
     * show sub menu keuangan rekapitulasi[keuangan]
     */
    public $showSubMenuRekapKeuangan=false; 
    /**     
     * show page rekapitulasi pembayaran semester ganjil[keuangan]
     */
    public $showReportRekapPembayaranGanjil=false;
    /**     
     * show sub menu setting akademik[setting]
     */
    public $showSubMenuSettingAkademik=false;       
    /**     
     * show sub menu setting sistem[setting]
     */
    public $showSubMenuSettingSistem=false;
    /**     
     * show page user Manajemen [setting sistem]
     */
    public $showUserManajemen=false;
    /**     
     * show page user dosen [setting sistem]
     */
    public $showUserDosen=false;
    /**     
     * show page cache [setting sistem]
     */
    public $showCache=false;    
	public function onLoad ($param) {		
		parent::onLoad($param);				
        if (!$this->IsPostBack&&!$this->IsCallBack) {	           
        }
	}           
}
?>