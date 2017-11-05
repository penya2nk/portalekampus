<?php
class MainPageSA extends MainPage {
    /**
     * show sub menu [dmaster perkuliahan]
     */
    public $showSubMenuDMasterPerkuliahan=false;
    /**     
     * show page tahun akademik [dmaster perkuliahan]
     */
    public $showTA=false;
    /**
     * show page penyelenggaraan [perkuliahan]
     */
    public $showPenyelenggaraan=false;
    /**
     * show page KRS Kelas Ekstension [perkuliahan]
     */
    public $showKRSEkstension=false;
    /**
     * show page Peserta matakuliah [perkuliahan]
     */
    public $showPesertaMatakuliah=false;
    /**     
     * show page variable [setting variable]
     */
    public $showVariable=false;        
    /**     
     * show sub menu setting akademik[setting]
     */
    public $showSubMenuSettingAkademik=false;    
    /**     
     * show page ketua prodi [setting sistem]
     */
    public $showKaprodi=false;
    /**     
     * show sub menu setting sistem[setting]
     */
    public $showSubMenuSettingSistem=false;
    /**     
     * show page user super admin [setting sistem]
     */
    public $showUserSA=false;
    /**     
     * show page user Manajemen [setting sistem]
     */
    public $showUserManajemen=false;
    /**     
     * show page user keuangan [setting sistem]
     */
    public $showUserKeuangan=false;
    /**     
     * show page user dosen [setting sistem]
     */
    public $showUserDosen=false;
     /**     
     * show page user operator nilai [setting sistem]
     */
    public $showUserON=false;
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