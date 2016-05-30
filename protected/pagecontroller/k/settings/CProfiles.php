<?php
prado::using ('Application.MainPageK');
class CProfiles extends MainPageK {    
	public function onLoad($param) {		
		parent::onLoad($param);		
        $this->showProfiles=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageCache'])||$_SESSION['currentPageCache']['page_name']!='k.settings.Profiles') {
				$_SESSION['currentPageCache']=array('page_name'=>'k.settings.Profiles','page_num'=>0);												
			}
		}
	}
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
           $id=$this->Pengguna->getDataUser('userid');                   
            if ($this->txtPassword->Text != '') {                
                $data=$this->Pengguna->createHashPassword($this->txtPassword->Text);
                $salt=$data['salt'];
                $password=$data['password'];
                $str = "UPDATE user SET userpassword='$password',salt='$salt' WHERE userid=$id";
            }
            $this->DB->updateRecord($str);                       
            $this->redirect('settings.Profiles',true);
        }
    }
}