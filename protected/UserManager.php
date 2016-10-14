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
            case 'Keuangan' :				
				$str = "SELECT u.userid,u.idbank,u.username,u.nama,u.email,u.page,u.isdeleted,foto,theme FROM user u WHERE username='$username'";
                $this->db->setFieldTable (array('userid','idbank','username','userpassword','salt','nama','email','page','isdeleted','foto','theme'));							
                $r= $this->db->getRecord($str);	
				$this->dataUser['data_user']=$r[1];	
                $userid=$this->dataUser['data_user']['userid'];
                $this->db->updateRecord("UPDATE user SET logintime=NOW() WHERE userid=$userid");
			break;
			case 'Manajemen' :				
				$str = "SELECT su.userid,sg.groupname,su.active,su.kjur,su.theme FROM simak_user su,simak_group sg WHERE su.groupid=sg.groupid AND su.username='$username'";
				$this->db->setFieldTable (array('userid','groupname','active','kjur','theme'));							
				$result = $this->db->getRecord($str);
				$this->dataUser['data_user']=$result[1];							
				$this->dataUser['data_user']['username']=$username;				
				$this->dataUser['data_user']['page']='m';
				$this->dataUser['hak_akses']=$this->loadAclUser($result[1]['userid']);
			break;            
			case 'Mahasiswa' :	                
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.jk,vdm.alamat_rumah,vdm.email,vdm.kjur,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.iddosen_wali,vdm.tahun_masuk,vdm.semester_masuk,vdm.nama_ps,vdm.k_status AS k_status,sm.n_status AS status,perpanjang,theme FROM v_datamhs vdm LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE nim='$username'";
                $this->db->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','email','kjur','idkonsentrasi','nama_konsentrasi','iddosen_wali','tahun_masuk','semester_masuk','nama_ps','k_status','status','perpanjang','theme'));
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
			case 'Dosen' :				
				$str = "SELECT d.iddosen,d.nidn,d.nipy,d.nama_dosen,theme FROM dosen d WHERE d.username='$username'";
				$this->db->setFieldTable(array('iddosen','nidn','nipy','nama_dosen','theme'));
				$r=$this->db->getRecord($str);				
				$this->dataUser['data_user']=$r[1];
                $this->dataUser['data_user']['userid']=$username;
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='d';				
			break;
			case 'DosenWali' :					
				$str = "SELECT d.iddosen,dw.iddosen_wali,d.nidn,d.nipy,d.nama_dosen,theme FROM dosen d,dosen_wali dw WHERE d.iddosen=dw.iddosen AND d.username='$username'";
				$this->db->setFieldTable(array('iddosen','iddosen_wali','nidn','nipy','nama_dosen','theme'));
				$r=$this->db->getRecord($str);				
				$this->dataUser['data_user']=$r[1];
                $this->dataUser['data_user']['userid']=$username;
				$this->dataUser['data_user']['username']=$username;
				$this->dataUser['data_user']['page']='dw';				
			break;
			case 'MahasiswaBaru' :				
                $str = "SELECT pm.no_formulir,fp.ta AS tahun_masuk,pm.theme FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE pm.no_formulir=fp.no_formulir AND fp.no_formulir='$username'";						
                $this->db->setFieldTable(array('no_formulir','tahun_masuk','theme'));
                $r=$this->db->getRecord($str);
                if (!isset($r[1])) {
                    $str = "SELECT no_formulir,tahun_masuk,no_pin FROM pin WHERE no_formulir='$username'";						
                    $this->db->setFieldTable(array('no_formulir','tahun_masuk','no_pin'));
                    $r=$this->db->getRecord($str);
                    $r[1]['theme']='cube';
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
		switch ($this->page) {
            case 'SuperAdmin' :
            case 'Keuangan' :
				$str = "SELECT u.username,u.userpassword,u.salt,u.page FROM user u WHERE username='$username' AND active=1";
                $this->db->setFieldTable (array('username','userpassword','salt','page'));							
                $result = $this->db->getRecord($str);	                
			break;	
			case 'Manajemen' :
				$str = "SELECT userid,username,userpassword,active FROM simak_user WHERE username='$username'";
				$this->db->setFieldTable (array('userid','username','userpassword','active'));							
				$result = $this->db->getRecord($str);				
				if (!$result[1]['active'])$result=array();					
			break;
			case 'Dosen' :
				$str = "SELECT d.iddosen,d.userpassword FROM dosen d WHERE d.username='$username'";
				$this->db->setFieldTable (array('iddosen','userpassword'));							
				$result = $this->db->getRecord($str);
				$this->id=$result[1]['iddosen'];	
			break;
			case 'DosenWali' :				
				$str = "SELECT dw.iddosen_wali,d.userpassword FROM dosen_wali dw,dosen d WHERE d.iddosen=dw.iddosen AND d.username='$username'";
				$this->db->setFieldTable (array('iddosen_wali','userpassword'));							
				$result = $this->db->getRecord($str);
				$this->id=$result[1]['iddosen_wali'];
			break;
			case 'Mahasiswa' :
				$nim=$this->username;
				$str = "SELECT nim,userpassword,k_status FROM v_datamhs WHERE nim='$nim'";
				$this->db->setFieldTable (array('nim','userpassword','k_status'));					
				$result = $this->db->getRecord($str);
                $result[1]['page']='mh';
			break;			
			case 'MahasiswaBaru' :
				$this->db->setFieldTable (array('username','userpassword'));					
                $str = "SELECT no_formulir AS username,userpassword FROM profiles_mahasiswa WHERE no_formulir='$username'";
                $result = $this->db->getRecord($str);			
                if (!isset($result[1])) {
                    $str = "SELECT no_formulir AS username,no_pin AS userpassword FROM pin WHERE no_formulir='$username'";				
                    $result = $this->db->getRecord($str);
                    $result[1]['userpassword']=md5($result[1]['userpassword']);                    
                }				
			break;
			case 'OrangtuaWali' :
				$this->db->setFieldTable (array('username','userpassword'));					
				$str = "SELECT username,userpassword FROM profiles_ortu WHERE username='$username'";
				$result = $this->db->getRecord($str);							
			break;
			case 'Library' :
				$this->db->setFieldTable (array('username','userpassword'));					
				$str = "SELECT username,userpassword FROM lib_users WHERE username='$username'";
				$result = $this->db->getRecord($str);							
			break;
			default :
				throw new Exception ('Page ('.$this->page.') is empty ...');
		}	
		return $result[1];
	}
	
	/**
	* mendapatkan section	
	*/
	private function loadAclUser ($userid) {
		$str = 'SELECT idsection,section_name FROM simak_section';
		$this->db->setFieldTable(array('idsection','section_name')); 
		$r=$this->db->getRecord($str);				
		$str = "SELECT sm.module_file,su.read_,su.write_ FROM simak_module sm,simak_groupacl sg,simak_useracl su,simak_user sus WHERE sm.idmodule=sg.idmodule AND sg.idgroupacl=su.idgroupacl AND sg.groupid=sus.groupid AND sus.userid=su.userid AND su.userid='$userid' AND sm.idsection=";
		$result=$this->db->setFieldTable(array('module_file','read_','write_'));
		foreach ($r as $v) {
			$idsection=$v['idsection'];
			$str2=$str . $idsection;
			$result=$this->db->getRecord($str2);			
			if (isset($result[1])) {
				$hasil=array();
				while (list($k,$n)=each($result)) {
					$hasil[$n['module_file'].'_read']=$n['read_'];
					$hasil[$n['module_file'].'_write']=$n['write_'];						
				}				
			}else {
				$hasil='false';
			}
			$acl[$v['section_name']]=$hasil;
		}		
		return $acl;
	}
}

?>