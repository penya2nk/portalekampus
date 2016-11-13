<?php
prado::using ('Application.MainPageM');
class CDosen extends MainPageM {		    	
	public function onLoad($param) {
		parent::onLoad($param);		     
        $this->showDosen=true;   
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDosen'])||$_SESSION['currentPageDosen']['page_name']!='sa.settings.UserManajemen') {
				$_SESSION['currentPageDosen']=array('page_name'=>'sa.settings.UserManajemen','page_num'=>0,'search'=>false);
			}
            $_SESSION['currentPageDosen']['search']=false;
            $this->populateData();            
		}
	}       
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDosen']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDosen']['search']);
	}
    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageDosen']['search']=true;
        $this->populateData($_SESSION['currentPageDosen']['search']);
	}    
	protected function populateData ($search=false) {
        if ($search) {
            $str = "SELECT iddosen,nidn,nipy,gelar_depan,nama_dosen,gelar_belakang,telp_hp,username,status FROM dosen";			
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'nidn' :
                    $cluasa="WHERE nidn='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("dosen $cluasa",'iddosen');		            
                    $str = "$str $cluasa";
                break;
                case 'nip' :
                    $cluasa="WHERE nipy='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("dosen $cluasa",'iddosen');		            
                    $str = "$str $cluasa";
                break;
                case 'nama_dosen' :
                    $cluasa="WHERE nama_dosen LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("dosen $cluasa",'iddosen');		            
                    $str = "$str $cluasa";
                break;
            }
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("dosen",'iddosen');		            
            $str = "SELECT iddosen,nidn,nipy,gelar_depan,nama_dosen,gelar_belakang,telp_hp,username,status FROM dosen";			
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDosen']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageDosen']['page_num']=0;}
        $str = "$str ORDER BY nama_dosen ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('iddosen','nidn','nipy','gelar_depan','nama_dosen','gelar_belakang','telp_hp','username','status'));
		$r = $this->DB->getRecord($str,$offset+1);	
        
        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}		    
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        $this->cmbAddJabatanAkademik->dataSource=$this->DMaster->getListJabfung ();
        $this->cmbAddJabatanAkademik->dataBind();	             
    }
    public function checkNIDN ($sender,$param) {	
        $this->idProcess=$sender->getId()=='addNidn'?'add':'edit';
        $nidn=$param->Value;
        if ($nidn != '') {
            try {   
                if ($this->hiddennidn->Value!=$nidn) {                                                            
                    if ($this->DB->checkRecordIsExist('nidn','dosen',$nidn)) {                                
                        throw new Exception ("NIDN/NIDK ($nidn) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }
	}
	public function checkNIPY ($sender,$param) {						
		$this->idProcess=$sender->getId()=='addNidn'?'add':'edit';
        $nipy=$param->Value;
        if ($nipy != '') {
            try {   
                if ($this->hiddennipy->Value!=$nipy) {                                                            
                    if ($this->DB->checkRecordIsExist('nipy','dosen',$nipy)) {                                
                        throw new Exception ("NIP Yayasan ($nipy) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }
	}
    public function checkUsername ($sender,$param) {
		$this->idProcess=$sender->getId()=='addUsername'?'add':'edit';
        $username=$param->Value;		
        if ($username != '') {
            try {
                if ($this->DB->checkRecordIsExist('username','dosen',$username) ) {
                    throw new Exception ("Username ($username) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                }                
                if($this->DB->checkRecordIsExist('username','user',$username)) {
                    throw new Exception ("Username ($username) sudah tidak tersedia silahkan ganti dengan yang lain.");		
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
                    if ($this->DB->checkRecordIsExist('email','dosen',$email)) {                                
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
            $nidn=addslashes($this->txtAddNIDN->Text);
            $nipy=addslashes($this->txtAddNIPY->Text);
            $nama=strtoupper(addslashes($this->txtAddNama->Text));
			$gelar_depan=addslashes($this->txtAddGelarDepan->Text);
			$gelar_belakang=addslashes($this->txtAddGelarBelakang->Text);
            $idjabatanfungsional=$this->cmbAddJabatanAkademik->Text;
			$alamat_dosen=strtoupper(addslashes($this->txtAddAlamat->Text));
            $no_telepon=addslashes($this->txtAddTelepon->Text);
            $email=addslashes($this->txtAddEmail->Text);
            $username=addslashes($this->txtAddUsername->Text);
			$password=md5(txtAddPassword1);
            
			$str = "INSERT INTO dosen SET nidn='$nidn',nipy='$nipy',nama_dosen='$nama',gelar_depan='$gelar_depan',gelar_belakang='$gelar_belakang',idjabatan='$idjabatanfungsional',alamat_dosen='$alamat_dosen',telp_hp='$no_telepon',email='$email',username='$username',userpassword='$password',theme='cube'";
			$this->DB->query('BEGIN');
            if ($this->DB->insertRecord($str)) {
                $data=$this->Pengguna->createHashPassword($this->txtAddPassword1->Text);
                $salt=$data['salt'];
                $password=$data['password'];           
                $page='d';
                $str = "INSERT INTO user SET username='$username',userpassword='$password',salt='$salt',nama='$nama',email='$email',page='$page',active=1,theme='cube',foto='resources/userimages/no_photo.png',date_added=NOW()";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->Redirect('dmaster.Dosen',true);
            }else{
                $this->DB->query('ROLLBACK');
            }
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $iddosen=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$iddosen;     
        
        $str = "SELECT nidn,nipy,nama_dosen,gelar_depan,gelar_belakang,idjabatan,alamat_dosen,telp_hp,email,username,status FROM dosen WHERE iddosen='$iddosen'";
		$this->DB->setFieldTable(array('nidn','nipy','nama_dosen','gelar_depan','gelar_belakang','idjabatan','alamat_dosen','telp_hp','email','username','status'));
        $r=$this->DB->getRecord($str);
        $result=$r[1];   
        
        $this->hiddenid->Value=$iddosen;
        $this->hiddennidn->Value=$result['nidn'];
        $this->hiddennipy->Value=$result['nipy'];
        $this->hiddenemail->Value=$result['email'];
        $this->hiddenusername->Value=$result['username'];
        
        $this->txtEditNIDN->Text=$result['nidn'];
        $this->txtEditNIPY->Text=$result['nipy'];
        $this->txtEditNama->Text=$result['nama_dosen'];
        $this->txtEditGelarDepan->Text=$result['gelar_depan'];
        $this->txtEditGelarBelakang->Text=$result['gelar_belakang'];
        $this->cmbEditJabatanAkademik->dataSource=$this->DMaster->getListJabfung ();
        $this->cmbEditJabatanAkademik->dataBind();	
        $this->cmbEditJabatanAkademik->Text=$result['idjabatan'];
        $this->txtEditAlamat->Text=$result['alamat_dosen'];
        $this->txtEditTelepon->Text=$result['telp_hp'];
        $this->txtEditEmail->Text=$result['email'];
        
        $this->cmbEditStatus->Text=$result['status'];
    }
    public function updateData ($sender,$param) {
		if ($this->Page->isValid) {
            $iddosen=$this->hiddenid->Value;
            $username=$this->hiddenusername->Value;
            $nidn=addslashes($this->txtEditNIDN->Text);
            $nipy=addslashes($this->txtEditNIPY->Text);
            $nama=strtoupper(addslashes($this->txtEditNama->Text));
			$gelar_depan=addslashes($this->txtEditGelarDepan->Text);
			$gelar_belakang=addslashes($this->txtEditGelarBelakang->Text);
            $idjabatanfungsional=$this->cmbEditJabatanAkademik->Text;
			$alamat_dosen=strtoupper(addslashes($this->txtEditAlamat->Text));
            $no_telepon=addslashes($this->txtEditTelepon->Text);
            $email=addslashes($this->txtEditEmail->Text);
            $status=$this->cmbEditStatus->Text;
			$str = "UPDATE dosen SET nidn='$nidn',nipy='$nipy',nama_dosen='$nama',gelar_depan='$gelar_depan',gelar_belakang='$gelar_belakang',idjabatan='$idjabatanfungsional',alamat_dosen='$alamat_dosen',telp_hp='$no_telepon',email='$email',status=$status WHERE iddosen=$iddosen";
			$this->DB->query('BEGIN');
            if ($this->DB->updateRecord($str)) {   
                if($this->DB->checkRecordIsExist('username','user',$username)) {
                    $str = "UPDATE user SET nama='$nama',email='$email',active=$status WHERE username='$username'";
                    $this->DB->updateRecord($str);
                }else{
                    $data=$this->Pengguna->createHashPassword(1234);
                    $salt=$data['salt'];
                    $password=$data['password'];           
                    $page='d';
                    $str = "INSERT INTO user SET username='$username',userpassword='$password',salt='$salt',nama='$nama',email='$email',page='$page',active=1,theme='cube',foto='resources/userimages/no_photo.png',date_added=NOW()";
                    $this->DB->insertRecord($str);
                }                
                $this->DB->query('COMMIT');
                $this->Redirect('dmaster.Dosen',true);
            }else{
                $this->DB->query('ROLLBACK');
            }
            $this->Redirect('dmaster.Dosen',true);
           
        }
	}
    public function deleteRecord ($sender,$param) {        
		$iddosen=$this->getDataKeyField($sender,$this->RepeaterS);  		
        if ($this->DB->checkRecordIsExist('iddosen','pengampu_penyelenggaraan',$iddosen)) {
            $this->lblHeaderMessageError->Text='Menghapus Dosen';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus dosen dengan ID ($iddosen) karena sedang digunakan di pengampu penyelenggaraan.";
            $this->modalMessageError->Show();
        }elseif ($this->DB->checkRecordIsExist('iddosen','dosen_wali',$iddosen)) {
            $this->lblHeaderMessageError->Text='Menghapus Dosen';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus dosen dengan ID ($iddosen) karena telah menjadi Dosen Wali.";
            $this->modalMessageError->Show();
        }elseif ($this->DB->checkRecordIsExist('iddosen','kjur',$iddosen)) {
            $this->lblHeaderMessageError->Text='Menghapus Matakuliah';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus dosen dengan ID ($iddosen) karena sedang menjadi Ketua Jurusan.";
            $this->modalMessageError->Show();
        }else{
            $str = "SELECT username,status FROM dosen WHERE iddosen='$iddosen'";
            $this->DB->setFieldTable(array('username'));
            $r=$this->DB->getRecord($str);
            $username=$r[1]['username'];
            $this->DB->deleteRecord("dosen WHERE iddosen=$iddosen");
            $this->DB->deleteRecord("user WHERE username='$username'");
            $this->redirect('dmaster.Dosen',true);
        }        
    }   
    
}
?>
