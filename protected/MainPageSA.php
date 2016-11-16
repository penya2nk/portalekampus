<?php
class MainPageSA extends MainPage {
    /**     
     * show page variable [setting variable]
     */
    public $showVariable=false;        
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