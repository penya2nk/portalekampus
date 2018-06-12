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
	*
	*/
	protected $DB;
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
	* generate json content	
	*/
	public function getJsonContent() {
		
		return array('apps'=>'Portal Ekampus API v1.0');
	}
}