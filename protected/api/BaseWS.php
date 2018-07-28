<?php

class BaseWS extends TJsonResponse {
	/**
	* link
	*/
	private $Link;
	
	/**
	* host database
	*/
	private $Host;
	
	/**
	* Port Server
	*/
	private $DbPort;
	
	/**
	* user database
	*/
	private $UserName;
	
	/**
	* user password
	*/
	private $UserPassword;
	
	/**
	* db name
	*/
	private $DbName;
	
	/**
	* Tipe Database => Postgres, MySQL, dll
	*/
	private $DbType;
	/**
	* Object Variable "Database"
	*/
	protected $DB;
	/**
	* Object Payload JSON
	*/
	protected $payload = array('connection'=>-1,'message'=>'INVALID REQUEST');
	public function init($config) {
		parent::init($config);
		//open connection to database	
		$this->linkOpen();			}
	/**
	* digunakan untuk membuka koneksi ke server, dan memilih database
	*
	*/
	private function linkOpen() {
		$this->prepareParameters();
		switch ($this->DbType) {
			case 'postgres' :
				prado::using ('Application.lib.Database.PostgreSQL');
				$this->DB = new PostgreSQL ();
				$config=array("host"=>$this->Host,
							"port"=>$this->DbPort,
							"user"=>$this->UserName,
			 				"password"=>$this->UserPassword,
							"dbname"=>$this->DbName);
			break;
			case 'mysql' :
				prado::using ('Application.lib.Database.MySQL');
				$this->DB = new MySQL ();
				$config=array("host"=>$this->Host,
							"user"=>$this->UserName,
			 				"password"=>$this->UserPassword,
							"dbname"=>$this->DbName);
								
			break;
			default :
				throw new Exception ('No Driver Found.');
		}
		$this->DB->connectDB ($config);
	}
	
	/**
	* menyiapkan beberapa paramaters
	*
	*/
	private function prepareParameters () {
		$db=$this->Application->getParameters ();		
		$this->Host = $db['db_host'];
		$this->UserName=$db['db_username'];
		$this->UserPassword=$db['db_userpassword'];
		$this->DbName=$db['db_name'];
		$this->DbType=$db['db_type'];
		$this->DbPort=$db['db_port'];			
	}
	/**
	* digunakan	untuk memvalidasi request api
	*/
	public function validate () {
		$headers = getallheaders();
		if (isset($headers['Username']) && isset($headers['Token'])) {
			$username = $headers['Username'];
			$token = $headers['Token'];			
			$ip=explode('.',$_SERVER['REMOTE_ADDR']);		        
			$ipaddress=$ip[0];	       	
			if ($ipaddress == '127' || $ipaddress == '::1') {
				$alamat_ip='127.0.0.1';
			}else{
				$alamat_ip=$ip;
			}
			$str = "SELECT userid,username,token,ipaddress,active FROM user WHERE username='$username' AND token='$token'";
			$this->DB->setFieldTable(array('userid','username','token','ipaddress','active'));
			$r  = $this->DB->getRecord($str);
			if (isset($r[1])) {	
				$data=$r[1];
				$_ip = explode(',',$data['ipaddress']);
				$jumlah_ip=count($_ip);
				$bool=-1;
				for ($i=0;$i<=$jumlah_ip;$i+=1) {
					if ($data['ipaddress'] == $alamat_ip) {
						$bool=1;
					}
				}
				$this->payload['connection'] = $bool;
				$this->payload['message'] = $bool > 0 ?"Username ($username) dan Token ($token) Valid !!!" : "Akses dari Alamat IP ($alamat_ip) tidak di ijinkan";					
				
			}else{
				$this->payload['message'] = "Tidak bisa mengeksekusi perintah, karena Username ($username) atau Token ($token) Salah !!!";
			}			
		}else{
			$this->payload['message'] = "Username atau Token tidak tersedia di header HTTP !!!";
		}
	}	
	/**
	* generate json content	
	*/
	public function getJsonContent() {		
		return $this->payload;
	}
}