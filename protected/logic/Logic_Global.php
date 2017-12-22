<?php
class Logic_Global extends TModule {	
	/**
	* object db
	*/
	protected $db;	
    /**
     * 
     * data report	
	*/
	protected $dataReport;    
	public function __construct ($db) {
		$this->db = $db;
	}	
    /**
     * digunakan untuk mengeset data report
     * @param type $dataReport
     */
    public function setDataReport ($dataReport) {
        $this->dataReport=$dataReport;
    }    
	/**
	* mendapatkan lo object
	* @return obj
	*
	*/
	protected function getLogic ($_class=null) {
		if ($_class === null)
			return $this->Application->getModule ('logic');
		else 
			return $this->Application->getModule ('logic')->getInstanceOfClass($_class);	
	}

	/**
	* mendapatkan list dari sebuah table
	* 
	* @param tableName
	* @param fieldTable 
	* @param orderField order berdasarkan field
	* @param limit
	* @param mode
	* @return array list isi tabel
	*/
	public function getList ($tableName,$fieldTable,$orderField=null,$limit=null,$mode=0) {
        $orderField=$orderField === null?$orderField=$fieldTable[0]. ' ASC':$orderField;		
		if ($limit !== null) {
			$offset=explode(',',$limit);
			$offset=$offset[0]+1;
			$limit = 'LIMIT '.$limit;			
		}else {
			$offset=1;
		}
		$str='SELECT ';		
		$countField = count($fieldTable);
		if ($countField <= 1) {
			$field =$fieldTable[0];
		}else {
			for ($i=0;$i<$countField;$i++) {
				if ($countField > $i+1) {
					$field = $field . $fieldTable[$i] . ',';
				}else {
					$field = $field . $fieldTable[$i];
				}
			}
		}		
		$str = $str."$field FROM $tableName ORDER BY $orderField $limit";		
		foreach ($fieldTable as $k=>$v) {
			if (strpos($v, '.') !== false) {
				$v2=explode('.',$v);
				$v2=$v2[1];
			}else {
				$v2=$v;				
			}
			$newFieldTable[$k]=$v2;
		}		
		$this->db->setFieldTable($fieldTable);	
		$result = $this->db->getRecord($str,$offset);		
		switch ($mode) {
			case 0 :
				$list_=$result;
			break;
			case 4 :
			case 1 :
				$list_['none']=' ';
				foreach ($result as $k=>$v){
					$list_[$v[$fieldTable[0]]]=$v[$fieldTable[1]];
				}
			break;
			case 2 :
				$list_['none']=' ';
				foreach ($result as $k=>$v){
					$list_[$v[$fieldTable[0]]]=$v[$fieldTable[1]].'-'.$v[$fieldTable[2]];
				}				
			break;
			case 3 :
				$list_['none']=' ';
				foreach ($result as $k=>$v){
					$list_[$v[$fieldTable[0]].'_'.$v[$fieldTable[1]]]=$v[$fieldTable[1]].'-'.$v[$fieldTable[2]];
				}
			break;
			case 5 :
				foreach ($result as $k=>$v){					
					$list_[$v[$fieldTable[0]]]=$v[$fieldTable[1]];
				}
			break;
			case 6 :				
				foreach ($result as $k=>$v){									
					$list_[$v[$fieldTable[0]]]=$v[$fieldTable[1]].'-'.$v[$fieldTable[2]];
				}
			break;
            case 7 :
				$list_['none']=' ';
				foreach ($result as $k=>$v){					
					$list_[$v[$newFieldTable[0]]]=$v[$newFieldTable[0]].' - '.$v[$newFieldTable[1]];
				}
			break;
            case 8 :
				$list_['none']=' ';
				foreach ($result as $k=>$v){
					$list_[$v[$fieldTable[0]]]=$v[$fieldTable[1]].'-'.$v[$fieldTable[2]];
				}
			break;
		}	
		return $list_;
	}
		
	/**
	*
	* Mendapatkan Path
	*
	*/
	public function getPath($path=null) {
		$_path=prado::getPathOfAlias ('Application') .DIRECTORY_SEPARATOR;
		if ($path === null) {
			return $path=$_path;
		}else {
			return $_path.$path.DIRECTORY_SEPARATOR;
		}
	}			
	/**
	* digunakan untuk meremove id dari sebuah array
	* @return array 
	*/
	public function removeIdFromArray ($arr=array(),$id) {
		if (isset($arr[$id])) {
			while (list($k,$v)=each($arr)) {
				if ($k != $id) {
					$arr2[$k]=$v;
				}
			}
			return $arr2;
		}else {
			return $arr;
		}
	}
}