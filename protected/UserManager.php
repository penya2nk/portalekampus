<?php
class UserManager extends TAuthManager {
	/**
	* Obj DB
	*/
	private $db;	
	/**
	* Page Manajemen, Mahasiswa, DPA, Dosen, Mahasiswa Baru, Orang Tua Wali
	*/
	public $page;	
	/**
	* Username
	*/
	private $username;				
	/**
	* data user
	*/
	private $dataUser=array('data_user'=>array(),'hak_akses'=>array());
	
	public function __construct () {
		$this->db = $this->Application->getModule('db')->getLink();						
	}
		
	/**
	* digunakan untuk mengeset username serta mensplit username dan page	
	*/
	public function setUser ($username) {
		$datauser = explode('/',$username);
		$this->username=$datauser[0];
		$this->page=$datauser[1];
	}
	/**
	* get roles username	
	*/
	public function getDataUser () {				
		$username=$this->username;
		switch ($this->page) {
            case 'SuperAdmin' :
                $str = "SELECT u.userid,u.username,u.nama,u.email,u.page,u.isdeleted,u.foto,u.theme FROM user u WHERE username='$username' AND u.page='sa'";
                $this->db->setFieldTable (array('userid','username','nama','email','page','isdeleted','foto','theme'));							
                $r= $this->db->getRecord($str);	
				$this->dataUser['data_user']=$r[1];	
                $userid=$this->dataUser['data_user']['userid'];
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE userid=$userid");
            break;                
            case 'Keuangan' :				
				$str = "SELECT u.userid,u.username,u.nama,u.email,u.page,u.isdeleted,u.foto,u.theme FROM user u WHERE username='$username' AND u.page='k'";
                $this->db->setFieldTable (array('userid','username','nama','email','page','isdeleted','foto','theme'));							
                $r= $this->db->getRecord($str);	
				$this->dataUser['data_user']=$r[1];	
                $userid=$this->dataUser['data_user']['userid'];
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE userid=$userid");
			break;
            case 'OperatorNilai' :
                $str = "SELECT u.userid,u.username,u.nama,u.email,u.page,u.group_id,u.kjur,u.isdeleted,u.foto,u.theme FROM user u WHERE username='$username' AND u.page='on'";
                $this->db->setFieldTable (array('userid','username','nama','email','page','group_id','kjur','isdeleted','foto','theme'));							
                $r= $this->db->getRecord($str);	
				$this->dataUser['data_user']=$r[1];	
                $userid=$this->dataUser['data_user']['userid'];
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE userid=$userid");
            break;
			case 'Manajemen' :	
                $str = "SELECT u.userid,u.username,u.nama,u.email,u.page,u.group_id,u.kjur,u.isdeleted,u.foto AS photo_profile,u.theme FROM user u WHERE username='$username' AND u.page='m'";
                $this->db->setFieldTable (array('userid','username','nama','email','page','group_id','kjur','isdeleted','photo_profile','theme'));							
                $result= $this->db->getRecord($str);	
                			
				$this->dataUser['data_user']=$result[1];							
				$this->dataUser['data_user']['username']=$username;				
				$this->dataUser['data_user']['page']='m';
				$this->dataUser['hak_akses']=$this->loadAclUser($result[1]['userid']);
                $userid=$this->dataUser['data_user']['userid'];
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE userid=$userid");
			break;            
			case 'Mahasiswa' :	                
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.jk,vdm.alamat_rumah,vdm.email,vdm.kjur,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.iddosen_wali,vdm.tahun_masuk,vdm.semester_masuk,vdm.nama_ps,vdm.k_status AS k_status,sm.n_status AS status,perpanjang,theme,photo_profile FROM v_datamhs vdm LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE nim='$username'";
                $this->db->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','email','kjur','idkonsentrasi','nama_konsentrasi','iddosen_wali','tahun_masuk','semester_masuk','nama_ps','k_status','status','perpanjang','theme','photo_profile'));
                $r=$this->db->getRecord($str);					
                if (isset($r[1])) {
                    $dataMhs=$r[1];	
                    $logic=$this->Application->getModule('logic');
                    $mhs=$logic->getInstanceOfClass('Mahasiswa');							
                    $mhs->setDataMHS (array('nim'=>$username));										
                    $kelas=$mhs->getKelasMhs();																	
                    $dataMhs['idkelas']=($kelas['idkelas']=='')?null:$kelas['idkelas'];
                    $dataMhs['nkelas']=($kelas['nkelas']=='')?'Belum ada':$kelas['nkelas'];			                    
                    $dataMhs['nama_konsentrasi']=($dataMhs['idkonsentrasi']==0) ? '-':$dataMhs['nama_konsentrasi'];
                    $dataMhs['iddata_konversi']=$mhs->isMhsPindahan($username,true);
                    
                    $dmaster=$logic->getInstanceOfClass('DMaster');							
                    $nama_dosen=$dmaster->getNamaDosenWaliByID($dataMhs['iddosen_wali']);				                    
                    $dataMhs['nama_dosen']=$nama_dosen;			                
                }
				$this->dataUser['data_user']=$dataMhs;
                $this->dataUser['data_user']['userid']=$username;
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='mh';						
			break;
            case 'Alumni' :	                
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.jk,vdm.alamat_rumah,vdm.email,vdm.kjur,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.iddosen_wali,vdm.tahun_masuk,vdm.semester_masuk,vdm.nama_ps,vdm.k_status AS k_status,sm.n_status AS status,perpanjang,theme,photo_profile FROM v_datamhs vdm LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE nim='$username'";
                $this->db->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','email','kjur','idkonsentrasi','nama_konsentrasi','iddosen_wali','tahun_masuk','semester_masuk','nama_ps','k_status','status','perpanjang','theme','photo_profile'));
                $r=$this->db->getRecord($str);					
                if (isset($r[1])) {
                    $dataMhs=$r[1];	
                    $logic=$this->Application->getModule('logic');
                    $mhs=$logic->getInstanceOfClass('Mahasiswa');							
                    $mhs->setDataMHS (array('nim'=>$username));										
                    $kelas=$mhs->getKelasMhs();																	
                    $dataMhs['idkelas']=($kelas['idkelas']=='')?null:$kelas['idkelas'];
                    $dataMhs['nkelas']=($kelas['nkelas']=='')?'Belum ada':$kelas['nkelas'];			                    
                    $dataMhs['nama_konsentrasi']=($dataMhs['idkonsentrasi']==0) ? '-':$dataMhs['nama_konsentrasi'];
                    $dataMhs['iddata_konversi']=$mhs->isMhsPindahan($username,true);
                    
                    $dmaster=$logic->getInstanceOfClass('DMaster');							
                    $nama_dosen=$dmaster->getNamaDosenWaliByID($dataMhs['iddosen_wali']);				                    
                    $dataMhs['nama_dosen']=$nama_dosen;			                
                }
				$this->dataUser['data_user']=$dataMhs;
                $this->dataUser['data_user']['userid']=$username;
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='al';			
                $this->dataUser['data_user']['theme']='limitless';	
			break;
			case 'Dosen' :				
				$str = "SELECT u.userid,d.iddosen,d.nidn,d.nipy,d.nama_dosen,d.theme FROM user u,dosen d WHERE u.username=d.username AND d.username='$username'";
				$this->db->setFieldTable(array('userid','iddosen','nidn','nipy','nama_dosen','theme'));
				$r=$this->db->getRecord($str);				
				$this->dataUser['data_user']=$r[1];
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='d';	                
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE username='$username'");
			break;
			case 'DosenWali' :					
				$str = "SELECT d.iddosen,dw.iddosen_wali,d.nidn,d.nipy,d.nama_dosen,theme FROM dosen d,dosen_wali dw WHERE d.iddosen=dw.iddosen AND d.username='$username'";
				$this->db->setFieldTable(array('iddosen','iddosen_wali','nidn','nipy','nama_dosen','theme'));
				$r=$this->db->getRecord($str);				
				$this->dataUser['data_user']=$r[1];
                $this->dataUser['data_user']['userid']=$username;
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='dw';		
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE username='$username'");
			break;
			case 'MahasiswaBaru' :				
                $str = "SELECT pm.no_formulir,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk,pm.theme,pm.photo_profile FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE pm.no_formulir=fp.no_formulir AND fp.no_formulir='$username'";						
                $this->db->setFieldTable(array('no_formulir','tahun_masuk','semester_masuk','theme','photo_profile'));
                $r=$this->db->getRecord($str);
                if (!isset($r[1])) {
                    $str = "SELECT pin.no_formulir,pin.tahun_masuk,pin.semester_masuk,pin.no_pin,pin.idkelas FROM transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1 AND pin.no_formulir='$username'";						
                    $this->db->setFieldTable(array('no_formulir','tahun_masuk','semester_masuk','no_pin','idkelas'));
                    $r=$this->db->getRecord($str);
                    $r[1]['theme']='cube';
                    $r[1]['photo_profile']='resources/photomhs/no_photo.png';
                }
				$this->dataUser['data_user']=$r[1];
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='mb';
			break;
			case 'OrangtuaWali' :				
				$str="SELECT idprofile AS userid,nim,email,theme FROM profiles_ortu WHERE username='$username'";
				$this->db->setFieldTable (array('userid','nim','email'));					
				$r=$this->db->getRecord($str);				
				$mhs=$this->Application->getModule('logic')->getInstanceOfClass('Mahasiswa');											
				$mhs->setNim ($r[1]['nim'],true);														
				$this->dataUser['data_user']=$mhs->dataMhs;								
				$this->dataUser['data_user']['userid']=$r[1]['userid'];
				$this->dataUser['data_user']['email']=$r[1]['email'];
				$this->dataUser['data_user']['username']=$this->username;
				$this->dataUser['data_user']['page']='ot';
			break;
			case 'Library' :
				$this->db->setFieldTable (array('userid','username','userpassword'));					
				$str = "SELECT userid,username,userpassword FROM lib_users WHERE username='$username'";
				$r= $this->db->getRecord($str);							
				$this->dataUser['data_user']=$r[1];
				$this->dataUser['data_user']['page']='l';
			break;
			default :
				throw new Exception ('Page ('.$this->page.')is empty ...');
		}	
		return $this->dataUser;
	}
	/**
	* digunakan untuk mendapatkan data user	
	*/
	public function getUser () {
        $username=$this->username;
        $data_user=array();
		switch ($this->page) {
            case 'SuperAdmin' :
                $str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active FROM user u WHERE username='$username' AND active=1 AND page='sa'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $result = $this->db->getRecord($str);
                $data_user=isset($result[1])?$result[1]:array();
            break;
            case 'OperatorNilai' :
                $str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active FROM user u WHERE username='$username' AND active=1 AND page='on'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $result = $this->db->getRecord($str);
                $data_user=isset($result[1])?$result[1]:array();
            break;
            case 'Keuangan' :
				$str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active FROM user u WHERE username='$username' AND active=1 AND page='k'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $result = $this->db->getRecord($str);	      
                $data_user=isset($result[1])?$result[1]:array();
			break;	
			case 'Manajemen' :
                $str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active FROM user u WHERE username='$username' AND active=1 AND page='m'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $result = $this->db->getRecord($str);
                $data_user=isset($result[1])?$result[1]:array();
			break;
			case 'Dosen' :
                $str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active AS active  FROM user u WHERE username='$username' AND active=1 AND page='d'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $result = $this->db->getRecord($str);
                $data_user=isset($result[1])?$result[1]:array();
			break;
			case 'DosenWali' :
                $str = "SELECT u.username,u.userpassword,u.salt,u.page,u.active AS active FROM user u WHERE username='$username' AND active=1 AND page='d'";
                $this->db->setFieldTable (array('username','userpassword','salt','page','active'));							
                $r = $this->db->getRecord($str);
                if (isset($r[1])) {
                    $data_user=$r[1];
                }else{
                    $str = "SELECT dw.iddosen_wali,d.userpassword,d.status AS active FROM dosen_wali dw,dosen d WHERE d.iddosen=dw.iddosen AND d.username='$username'";
                    $this->db->setFieldTable (array('iddosen_wali','userpassword','active'));							
                    $result = $this->db->getRecord($str);
                    $data_user=isset($result[1])?$result[1]:array();
                }
			break;
			case 'Mahasiswa' :
				$nim=$this->username;
				$str = "SELECT nim,userpassword,k_status FROM v_datamhs WHERE nim='$nim'";
				$this->db->setFieldTable (array('nim','userpassword','k_status'));					
				$result = $this->db->getRecord($str);				
				if (isset($result[1])){				 
				    $data_user=$result[1];
				    $data_user['active']=$result[1]['k_status']=='A' ? 1:0;
				    $data_user['page']='mh';
                }                
			break;			
			case 'MahasiswaBaru' :
				$this->db->setFieldTable (array('username','nim','userpassword'));					
                $str = "SELECT no_formulir AS username,nim,no_formulir AS userpassword FROM profiles_mahasiswa WHERE no_formulir='$username'";
                $result = $this->db->getRecord($str);	
                if (isset($result[1])) {
                    $data_user=$result[1];
                    $data_user['userpassword']=md5($result[1]['userpassword']);
                    $data_user['active']=$result[1]['nim']==''?1:0;
                }else{
                    $str = "SELECT pin.no_formulir AS username,pin.no_formulir AS userpassword FROM transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1 AND pin.no_formulir='$username'";
                    $result = $this->db->getRecord($str);
                    if (isset($result[1])) {
                        $data_user=$result[1];
                        $data_user['userpassword']=md5($result[1]['userpassword']);
                        $data_user['active']=1;
                    }                   
                }				
			break;
            case 'Alumni' :
				$nim=$this->username;
				$str = "SELECT nim,nim AS userpassword,k_status FROM v_datamhs WHERE nim='$nim'";
				$this->db->setFieldTable (array('nim','userpassword','k_status'));					
				$result = $this->db->getRecord($str);
                if (isset($result[1])) {
                    $data_user=$result[1];
                    $data_user['active']=$result[1]['k_status']=='L' ? 1:0;
                    $data_user['userpassword']=md5($result[1]['userpassword']);
                    $data_user['page']='al';
                }
			break;	
			case 'OrangtuaWali' :
				$this->db->setFieldTable (array('username','userpassword'));					
				$str = "SELECT username,userpassword FROM profiles_ortu WHERE username='$username'";
				$result = $this->db->getRecord($str);	
				$data_user=isset($result[1])?$result[1]:array();
			break;
			case 'Library' :
				$this->db->setFieldTable (array('username','userpassword'));					
				$str = "SELECT username,userpassword FROM lib_users WHERE username='$username'";
				$result = $this->db->getRecord($str);		
				$data_user=isset($result[1])?$result[1]:array();
			break;
			default :
				throw new Exception ('Page ('.$this->page.') is empty ...');
		}	
		return $data_user;
	}
	
	/**
	* mendapatkan section	
	*/
	private function loadAclUser ($userid) {
        
	}
}