<?php
class MainService extends TModule  {  
    /**     
     * @var array parameters koneksi ke database 
     */
    private $Parameters;
    /**     
     * @var array FieldTable 
     */
    private $FieldTable;
    /**     
     * @var array Connection
     */
    private $Conn;
    public function __construct () {
        $this->Parameters=$this->Application->getParameters ();		        
    }
    /**
     * digunakan untuk koneksi ke database
     */
    protected function connectDB () {
        $host=$this->Parameters['db_host'];
        $username=$this->Parameters['db_username'];
		$password=$this->Parameters['db_userpassword'];
		$dbname=$this->Parameters['db_name'];
		
        $this->Conn = new mysqli ($host,$username,$password,$dbname);
        if($this->Conn->connect_errno > 0){
            throw new Exception('Unable to connect to database [' . $this->Conn->connect_error . ']');
        }
    }
    /**
	* digunakan untuk mengeksekusi  perintah sql
	* @param param sqlString
	* @return void
	*/
	private function query ($sqlString) {				
		if ($result=$this->Conn->query($sqlString)) {						
			return $result;
		}else {
			throw new Exception ('Query Failed = '.$sqlString.' ['.$this->Conn->error.']');
		}
	}
    /**
     * mengeset daftar field
     * @param array $field
     */
	public function setFieldTable ($field=array()) {	 
		foreach ($field as $k=>$v) {
			$ft[]=array("field"=>$v);			 
		}
		$this->FieldTable = $ft;
	}
    /**
	* get field of table
	* @param arg field or type
	*/	
	public function getFieldTable ($arg="all") {
		if (count ($this->FieldTable) != 0 ) {
			$ft = $this->FieldTable;
			if ($arg == "all") {
				return $ft;
			}else {
				foreach ($ft as $value) {
					if ($arg == "type") {
						$arrTable = "";
					}else if ($arg == "field") {
						$arrTable[]=$value[$arg];
					}else {
						throw new Exception ("DBHandler::getFieldTable::arg valid are type and field");
						break;
					}					
				}			
				return $arrTable;
			}
		}else {
			throw new Exception ("First, please set the table name or field of table");
		}
	}
    /**
	* mengambil record dari sebuah tabel dalam bentuk array
	* @param sqlString ini sql string
	* @param offset 
	*
	*/	
	public function getRecord ($sqlString,$offset=1) {				
        $result=$this->query($sqlString);
        $data=array();
		if ($result->num_rows >= 1) {            
			$ft = $this->getFieldTable('field');			
			$countFieldTable = count ($ft);
			$counter = 1;									
			while ($row=$result->fetch_assoc()) {			
				$tempRecord['no']=$offset;
				for ($i=0;$i < $countFieldTable;$i++) {
					$tempRecord[$ft[$i]]=trim($row[$ft[$i]]);
				}
				$data[$counter]=$tempRecord;
				$counter++;			
				$offset++;
			} 	
			
		}
        return $data;
	}	
}