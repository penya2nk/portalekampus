<?php
prado::using ('Application.MainPageON');
class CProfiles extends MainPageON {    
	public function onLoad($param) {		
		parent::onLoad($param);		
        $this->showProfiles=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageCache'])||$_SESSION['currentPageCache']['page_name']!='on.settings.Profiles') {
				$_SESSION['currentPageCache']=array('page_name'=>'on.settings.Profiles','page_num'=>0);												
			}
		}
	}
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            $id=$this->Pengguna->getDataUser('userid');                   
            if ($this->txtPassword->Text == '') {
                $str = "UPDATE user SET theme='limitless' WHERE userid=$id";
            }else{
                $data=$this->Pengguna->createHashPassword($this->txtPassword->Text);
                $salt=$data['salt'];
                $password=$data['password'];
                $str = "UPDATE user SET userpassword='$password',salt='$salt',theme='limitless' WHERE userid=$id";
            }
            $this->DB->updateRecord($str);
            $this->redirect('settings.Profiles',true);
        }
    }
}