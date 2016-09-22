<?php
prado::using ('Application.MainPageDW');
class CProfiles extends MainPageDW {    
	public function onLoad($param) {		
		parent::onLoad($param);		
        $this->showProfiles=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageCache'])||$_SESSION['currentPageCache']['page_name']!='dw.settings.Profiles') {
				$_SESSION['currentPageCache']=array('page_name'=>'dw.settings.Profiles','page_num'=>0);												
			}            
            $this->populateData ();
		}
        
	}   
    public function populateData () {
        $this->cmbTheme->DataSource=$this->setup->getListThemes();
        $this->cmbTheme->Text=$_SESSION['theme'];
        $this->cmbTheme->DataBind();
    }
    
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            $theme=$this->cmbTheme->Text;
            $_SESSION['theme']=$theme;
            $userid=$this->Pengguna->getDataUser('userid');
            $str = "UPDATE simak_user SET theme='$theme' WHERE userid=$userid";            
            $this->DB->updateRecord($str);
            $this->redirect('settings.Profiles',true);
        }
    }
}