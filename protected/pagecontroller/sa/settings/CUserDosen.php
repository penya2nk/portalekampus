<?php
prado::using ('Application.MainPageSA');
class CUserDosen extends MainPageSA {		    	
	public function onLoad($param) {
		parent::onLoad($param);		     
        $this->showSubMenuSettingSistem=true;
        $this->showUserDosen=true;   
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageUserDosen'])||$_SESSION['currentPageUserDosen']['page_name']!='sa.settings.UserDosen') {
				$_SESSION['currentPageUserDosen']=array('page_name'=>'sa.settings.UserDosen','page_num'=>0,'search'=>false);
			}
            $_SESSION['currentPageUserDosen']['search']=false;
            $this->populateData();            
		}
	}       
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageUserDosen']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageUserDosen']['search']);
	}
    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageUserDosen']['search']=true;
        $this->populateData($_SESSION['currentPageUserDosen']['search']);
	}    
	protected function populateData ($search=false) {
        if ($search) {
            $str = "SELECT u.userid,u.username,u.nama,u.email,ug.group_name,u.active,u.foto,u.logintime FROM user u LEFT JOIN user_group ug ON (ug.group_id=u.group_id) WHERE page='d'";			
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'username' :
                    $clausa="AND username='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='d' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND nama LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='d' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
                case 'email' :
                    $clausa="AND email LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='d' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
            }
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='d'",'userid');		            
            $str = "SELECT u.userid,u.username,u.nama,u.email,u.active,u.foto,u.logintime FROM user u WHERE page='d'";			
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageUserDosen']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageUserDosen']['page_num']=0;}
        $str = "$str ORDER BY username ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('userid','username','nama','email','email','active','foto','logintime'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['logintime']=$v['logintime']=='0000-00-00 00:00:00'?'BELUM PERNAH':$this->Page->TGL->tanggal('d F Y',$v['logintime']);            
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}
    public function checkUsername ($sender,$param) {
		$this->idProcess=$sender->getId()=='addUsername'?'add':'edit';
        $username=$param->Value;		
        if ($username != '') {
            try {   
                if ($this->hiddenusername->Value!=$username) {                                                            
                    if ($this->DB->checkRecordIsExist('username','user',$username)) {                                
                        throw new Exception ("Username ($username) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function checkEmail ($sender,$param) {
		$this->idProcess=$sender->getId()=='addEmail'?'add':'edit';
        $email=$param->Value;		
        if ($email != '') {
            try {   
                if ($this->hiddenemail->Value!=$email) {                    
                    if ($this->DB->checkRecordIsExist('email','user',$email)) {                                
                        throw new Exception ("Email ($email) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }    
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;     
        
        $str = "SELECT userid,username,nama,email,group_id,kjur,active FROM user WHERE userid='$id'";
        $this->DB->setFieldTable(array('userid','username','nama','email','group_id','kjur','active'));
        $r=$this->DB->getRecord($str);
        
        $result=$r[1];        	
        $this->txtEditNama->Text=$result['nama'];
        $this->txtEditEmail->Text=$result['email'];
        $this->hiddenemail->Value=$result['email'];     
        $this->txtEditUsername->Text=$result['username'];
        $this->hiddenusername->Value=$result['username'];
        $this->cmbEditStatus->Text=$result['active'];
    }
    public function updateData ($sender,$param) {
		if ($this->Page->isValid) {			
            $id=$this->hiddenid->Value;
            $username_old=$this->hiddenusername->Value;
            $nama = addslashes($this->txtEditNama->Text);
            $email = addslashes($this->txtEditEmail->Text);
            $username=addslashes($this->txtEditUsername->Text);
            $status=$this->cmbEditStatus->Text;
            if ($this->txtEditPassword1->Text == '') {
                $str = "UPDATE user SET username='$username',nama='$nama',email='$email',active='$status' WHERE userid=$id";               
                $str_dosen = "UPDATE dosen SET username='$username' WHERE username='$username_old'";
            }else {
                $data=$this->Pengguna->createHashPassword($this->txtEditPassword1->Text);
                $salt=$data['salt'];
                $password=$data['password'];
                $str = "UPDATE user SET username='$username',userpassword='$password',salt='$salt',nama='$nama',email='$email',active='$status' WHERE userid=$id";
                $str_dosen = "UPDATE dosen SET username='$username' WHERE username='$username_old'";
            }
            $this->DB->updateRecord($str);
            $this->DB->updateRecord($str_dosen);
			$this->redirect('settings.UserDosen',true);
		}
	}
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);        
        $this->DB->deleteRecord("user WHERE userid=$id");
        $this->redirect('settings.UserDosen',true);
    }   
    
}