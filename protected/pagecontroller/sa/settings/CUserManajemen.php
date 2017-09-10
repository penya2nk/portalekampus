<?php
prado::using ('Application.MainPageSA');
class CUserManajemen extends MainPageSA {		    	
	public function onLoad($param) {
		parent::onLoad($param);		     
        $this->showSubMenuSettingSistem=true;
        $this->showUserManajemen=true;   
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageUserManajemen'])||$_SESSION['currentPageUserManajemen']['page_name']!='sa.settings.UserManajemen') {
				$_SESSION['currentPageUserManajemen']=array('page_name'=>'sa.settings.UserManajemen','page_num'=>0,'search'=>false);
			}
            $_SESSION['currentPageUserManajemen']['search']=false;
            $this->populateData();            
		}
	}       
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageUserManajemen']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageUserManajemen']['search']);
	}
    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageUserManajemen']['search']=true;
        $this->populateData($_SESSION['currentPageUserManajemen']['search']);
	}    
	protected function populateData ($search=false) {
        if ($search) {
            $str = "SELECT u.userid,u.username,u.nama,u.email,ug.group_name,u.active,u.foto,u.logintime FROM user u LEFT JOIN user_group ug ON (ug.group_id=u.group_id) WHERE page='m'";			
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'username' :
                    $clausa="AND username='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='m' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND nama LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='m' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
                case 'email' :
                    $clausa="AND email LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='m' $clausa",'userid');		            
                    $str = "$str $clausa";
                break;
            }
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='m'",'userid');		            
            $str = "SELECT u.userid,u.username,u.nama,u.email,ug.group_name,u.active,u.foto,u.kjur,u.logintime FROM user u LEFT JOIN user_group ug ON (ug.group_id=u.group_id) WHERE page='m'";			
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageUserManajemen']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageUserManajemen']['page_num']=0;}
        $str = "$str ORDER BY username ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('userid','username','nama','email','email','group_name','active','foto','kjur','logintime'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['logintime']=$v['logintime']=='0000-00-00 00:00:00'?'BELUM PERNAH':$this->Page->TGL->tanggal('d F Y',$v['logintime']);
            $v['group_name']=$v['kjur']==0?$v['group_name']:$v['group_name'] . ' '.$_SESSION['daftar_jurusan'][$v['kjur']];
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}		    
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        $this->cmbAddGroup->DataSource=$this->Pengguna->removeIdFromArray($this->Pengguna->getListGroup(),'none');
        $this->cmbAddGroup->DataBind();
        $daftar_jurusan=$_SESSION['daftar_jurusan'];
        $daftar_jurusan['none'] = ' ';
        $this->cmbAddProdi->DataSource=$daftar_jurusan;
        $this->cmbAddProdi->DataBind();        
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
    public function saveData ($sender,$param) {
		if ($this->Page->isValid) {
            $nama = addslashes($this->txtAddNama->Text);
            $email = addslashes($this->txtAddEmail->Text);
            $username=addslashes($this->txtAddUsername->Text);    
            $data=$this->Pengguna->createHashPassword($this->txtAddPassword1->Text);
            $salt=$data['salt'];
            $password=$data['password'];           
            $page='m';
            $group_id=$this->cmbAddGroup->Text;  
            $kjur=$this->cmbAddProdi->Text;
            $str = "INSERT INTO user (userid,username,userpassword,salt,nama,email,page,group_id,kjur,active,theme,foto,date_added) VALUES (NULL,'$username','$password','$salt','$nama','$email','$page','$group_id','$kjur',1,'cube','resources/userimages/no_photo.png',NOW())";
            $this->DB->insertRecord($str);           
            
			$this->redirect('settings.UserManajemen',true);
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
        
        $this->cmbEditGroup->DataSource=$this->Pengguna->removeIdFromArray($this->Pengguna->getListGroup(),'none');
        $this->cmbEditGroup->Text=$result['group_id'];  
        $this->cmbEditGroup->DataBind();
        $daftar_jurusan=$_SESSION['daftar_jurusan'];
        $daftar_jurusan['none'] = ' ';
        $this->cmbEditProdi->DataSource=$daftar_jurusan;
        $this->cmbEditProdi->Text=$result['kjur'];
        $this->cmbEditProdi->DataBind();       
        
        
        
        $this->cmbEditStatus->Text=$result['active'];
    }
    public function updateData ($sender,$param) {
		if ($this->Page->isValid) {			
            $id=$this->hiddenid->Value;
            $nama = addslashes($this->txtEditNama->Text);
            $email = addslashes($this->txtEditEmail->Text);
            $username=addslashes($this->txtEditUsername->Text); 
            $group_id=$this->cmbEditGroup->Text;  
            $kjur=$this->cmbEditProdi->Text;
            $status=$this->cmbEditStatus->Text;
            
            if ($this->txtEditPassword1->Text == '') {
                $str = "UPDATE user SET username='$username',nama='$nama',email='$email',group_id='$group_id',kjur='$kjur',active='$status' WHERE userid=$id";               
            }else {
                $data=$this->Pengguna->createHashPassword($this->txtEditPassword1->Text);
                $salt=$data['salt'];
                $password=$data['password'];
                $str = "UPDATE user SET username='$username',userpassword='$password',salt='$salt',nama='$nama',email='$email',group_id='$group_id',kjur='$kjur',active='$status' WHERE userid=$id";               
            }
            $this->DB->updateRecord($str); 
			$this->redirect('settings.UserManajemen',true);
		}
	}
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);        
        $this->DB->deleteRecord("user WHERE userid=$id");
        $this->redirect('settings.UserManajemen',true);
    }   
    
}
?>
