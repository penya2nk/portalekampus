<?php

prado::using ('Application.logic.Logic_Global');
class Logic_Users extends Logic_Global {	
	/**
	* object Users
	*/
	private $U;	
	/**
	* Roles
	*/
	private $Roles;
	/**
	* Data User
	*/
	private $DataUser;	
    /**
	* Access Control List User
	*/
	private $UserAcl;
	public function __construct ($db) {
		parent::__construct ($db);	
		$this->U = $this->User;
		if (method_exists($this->U,'getRoles')) {
			$dataUser=$this->U->getName();	
			if ($dataUser != 'Guest') {
				$this->Roles=$this->U->getRoles();			                
				$this->DataUser=$dataUser['data_user'];					
				$this->UserAcl=$dataUser['hak_akses'];
            }		
		}				
	}
	/**
	* digunakan untuk membuat hash password
	* @return array
	*/
	public function createHashPassword($password,$salt='',$new=true) {
		if ($new) {
			$salt = substr(md5(uniqid(rand(), true)), 0, 6);	
			$password = hash('sha256', $salt . hash('sha256', $password));
			$data =array('salt'=>$salt,'password'=>$password);			
		}else {
			$data = hash('sha256', $salt . hash('sha256', $password));	
			$data =array('salt'=>$salt,'password'=>$password);		
		}
		return $data;
	}
    /**
	* digunakan untuk mendapatkan roles user
	*/		
	public function getRoles () {
		return $this->Roles[0];
	}    
	/**
	* digunakan untuk mendapatkan tipe user
	*/		
	public function getTipeUser () {
		return $this->DataUser['page'];
	}		
	/**
	* digunakan untuk mendapatkan data user
	*
	* @return datauser
	*/
	public function getDataUser ($id='all') {						
	    if ($id=='all') {
			return $this->DataUser;
	    }else{
	        return isset($this->DataUser[$id])?$this->DataUser[$id]:'N.A';
	    }
	}	
	/**
	* untuk mendapatkan userid dari user
	*
	*/
	public function getUserid () {			
		return $this->DataUser['userid'];		
	}		
	/**
	* untuk mendapatkan username dari user	
	*/
	public function getUsername () {		
		return $this->DataUser['username'];
	}   
    /**
	* digunakan untuk mendapatkan daftar group 	
	*/	
	public function getListGroup () {
		$result = $this->getList('user_group',array('group_id','group_name'),null,null,1);
		return $result;
	}
    /**
	* dapatkan hak akses untuk module	
	*/
	public function getModuleAccess ($sectionname,$modulename) {		
		$modul=$this->UserAcl[$sectionname];	        
		if ($modul =='') {
			throw new Exception ("section ($sectionname) tidak di kenal");			
		}else {
			$modul_read=$modul[$modulename.'_read'];
			$modul_write=$modul[$modulename.'_write'];						 
		}		
		$bool=false;
		if(($modul_read='Y' && $modul_write=='T')||($modul_read='Y' && $modul_write=='Y')) {
			$bool=true;
		}	
		return $bool;
	}
	/**
	* jika user tidak memiliki hak pada suatu modul maka akan forbiden	
	*/
	public function moduleForbiden ($section,$modulename) {
		if (!$this->getModuleAccess($section,$modulename)) {
			$section=ucfirst($section);
			$modulename=ucfirst($modulename);						
			echo 'Anda tidak boleh mengakses ', $section,'::'.$modulename;
			exit();
		}
	}
}