<?php
prado::using ('Application.MainPageSA');
class CAPI extends MainPageSA {               
    public function onLoad($param) {
        parent::onLoad($param);          
        $this->showSubMenuSettingSistem=true;
        $this->showAPI=true;   
        if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageAPI'])||$_SESSION['currentPageAPI']['page_name']!='sa.settings.API') {
                $_SESSION['currentPageAPI']=array('page_name'=>'sa.settings.API','page_num'=>0,'search'=>false);
            }
            $_SESSION['currentPageAPI']['search']=false;
            $this->populateData();            
        }
    }       
    public function renderCallback ($sender,$param) {
        $this->RepeaterS->render($param->NewWriter);    
    }
    public function Page_Changed ($sender,$param) {
        $_SESSION['currentPageAPI']['page_num']=$param->NewPageIndex;
        $this->populateData($_SESSION['currentPageAPI']['search']);
    }
    
    public function searchRecord ($sender,$param) {
        $_SESSION['currentPageAPI']['search']=true;
        $this->populateData($_SESSION['currentPageAPI']['search']);
    }    
    protected function populateData ($search=false) {
        if ($search) {
            $str = "SELECT u.userid,u.username,u.nama,u.email,u.active,u.foto,u.logintime FROM user u WHERE page='api'";            
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'username' :
                    $clausa="AND username='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='api' $clausa",'userid');                   
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND nama LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='api' $clausa",'userid');                   
                    $str = "$str $clausa";
                break;
                case 'email' :
                    $clausa="AND email LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='api' $clausa",'userid');                   
                    $str = "$str $clausa";
                break;
            }
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("user WHERE page='api'",'userid');                   
            $str = "SELECT u.userid,u.username,u.nama,u.email,u.active,u.foto,u.ipaddress,u.token,u.logintime,u.active FROM user u WHERE page='api'";         
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageAPI']['page_num'];
        $this->RepeaterS->VirtualItemCount=$jumlah_baris;
        $currentPage=$this->RepeaterS->CurrentPageIndex;
        $offset=$currentPage*$this->RepeaterS->PageSize;        
        $itemcount=$this->RepeaterS->VirtualItemCount;
        $limit=$this->RepeaterS->PageSize;
        if (($offset+$limit)>$itemcount) {
            $limit=$itemcount-$offset;
        }
        if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageAPI']['page_num']=0;}
        $str = "$str ORDER BY username ASC LIMIT $offset,$limit";               
        $this->DB->setFieldTable(array('userid','username','nama','email','email','ipaddress','token','foto','logintime','active'));
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
    public function addProcess ($sender,$param) {
        $this->idProcess='add';       
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
            $ipaddress=addslashes($this->txtAddIPAddress->Text);
            $data=$this->Pengguna->createHashPassword($this->txtAddPassword1->Text);
            $salt=$data['salt'];
            $password=$data['password'];      
            $page='api';            
            $str = "INSERT INTO user SET userid=NULL,username='$username',userpassword='$password',salt='$salt',token='$password',ipaddress='$ipaddress',nama='$nama',email='$email',page='$page',active=1,theme='cube',foto='resources/userimages/no_photo.png',date_added=NOW()";
            $this->DB->insertRecord($str);           
            
            $this->redirect('settings.API',true);
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
        $this->hiddenid->Value=$id;     
        
        $str = "SELECT userid,username,nama,email,ipaddress,active FROM user WHERE userid='$id'";
        $this->DB->setFieldTable(array('userid','username','nama','email','ipaddress','active'));
        $r=$this->DB->getRecord($str);
        
        $result=$r[1];          
        $this->txtEditNama->Text=$result['nama'];
        $this->txtEditEmail->Text=$result['email'];
        $this->hiddenemail->Value=$result['email'];     
        $this->txtEditUsername->Text=$result['username'];
        $this->txtEditIPAddress->Text=$result['ipaddress'];         
        $this->hiddenusername->Value=$result['username'];   
        
        $this->cmbEditStatus->Text=$result['active'];
    }
    public function updateData ($sender,$param) {
        if ($this->Page->isValid) {         
            $id=$this->hiddenid->Value;
            $nama = addslashes($this->txtEditNama->Text);
            $email = addslashes($this->txtEditEmail->Text);
            $username=addslashes($this->txtEditUsername->Text); 
            $ipaddress=addslashes($this->txtEditIPAddress->Text);
            $status=$this->cmbEditStatus->Text;
            
            if ($this->txtEditPassword1->Text == '') {
                $str = "UPDATE user SET username='$username',email='$email',nama='$nama',ipaddress='$ipaddress',active='$status' WHERE userid=$id";
            }else {
                $data=$this->Pengguna->createHashPassword($this->txtEditPassword1->Text);
                $salt=$data['salt'];
                $password=$data['password'];                              
                $str = "UPDATE user SET username='$username',userpassword='$password',salt='$salt',token='$password',ipaddress='$ipaddress',nama='$nama',email='$email',active='$status' WHERE userid=$id";
            }
            $this->DB->updateRecord($str); 
            $this->redirect('settings.API',true);
        }
    }
    public function deleteRecord ($sender,$param) {        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
        $this->DB->deleteRecord("user WHERE userid=$id");
        $this->redirect('settings.API',true);
    }   
    
}