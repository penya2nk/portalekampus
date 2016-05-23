<?php
prado::using ('Application.MainPageM');
class CMatkulPrasyarat extends MainPageM {		    	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showMatakuliah=true;        
        $this->createObj('Akademik');
        
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageMatkulPrasyarat'])||$_SESSION['currentPageMatkulPrasyarat']['page_name']!='m.dmaster.MatkulPrasyarat') {
				$_SESSION['currentPageMatkulPrasyarat']=array('page_name'=>'m.dmaster.MatkulPrasyarat','page_num'=>0,'search'=>false,'dataMatkul'=>array());
			}
			$this->populateData();            
		}
	}   
	protected function populateData () {						
        try {
            $id=addslashes($this->request['id']);
            $kmatkul=$this->Demik->getKMatkul($id);
            $str = "SELECT kmatkul,nmatkul,semester FROM matakuliah WHERE kmatkul='$id'";                        
            $this->DB->setFieldTable(array('kmatkul','nmatkul','semester'));
            $r = $this->DB->getRecord($str);	
            if (!isset($r[1])) {
                $_SESSION['currentPageMatkulPrasyarat']['dataMatkul']=array();
                throw new Exception("Matakuliah dengan kode ($kmatkul) tidak terdaftar.");
            }
            $data=$r[1];
            $this->lblMatkul->Text=$data['nmatkul'];                 
            if ($data['semester']==1) {
                throw new Exception("Matakuliah yang berada di semester 1 tidak ada prasyaratnya.");
            }          
            $_SESSION['currentPageMatkulPrasyarat']['dataMatkul']=$data;
            
            $str = "SELECT ms.idsyarat_kmatkul,m2.kmatkul,m2.nmatkul,m2.sks,m2.semester,m2.sks_tatap_muka,m2.sks_praktikum,m2.sks_praktik_lapangan FROM matakuliah m,matakuliah m2,matakuliah_syarat ms WHERE ms.kmatkul=m.kmatkul AND m2.kmatkul=ms.kmatkul_syarat AND ms.kmatkul='$id' ORDER BY m.nmatkul ASC";				
            $this->DB->setFieldTable(array('idsyarat_kmatkul','kmatkul','nmatkul','sks','semester','sks_tatap_muka','sks_praktikum','sks_praktik_lapangan'));
            $r = $this->DB->getRecord($str);
            $result = array();
            $kjur=$_SESSION['kjur']; 
            while (list($k,$v)=each($r)) {            
                $v['kode_matkul']=$this->Demik->getKMatkul($v['kmatkul']);
                $v['nama_konsentrasi']=$this->DMaster->getNamaKonsentrasiByID($v['idkonsentrasi'],$kjur);
                $result[$k]=$v;
            }
            $this->RepeaterS->DataSource=$result;		
            $this->RepeaterS->dataBind();	
        } catch (Exception $e) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();			
        }		
	}
    public function checkKodeMatkul ($sender,$param) {		
        $value=$param->Value;		
        if ($value != '') {
            try {   
                $kmatkul=$this->Demik->getIDKurikulum($_SESSION['kjur']).'_'.$value;                
                $str = "SELECT kmatkul,nmatkul,semester FROM matakuliah WHERE kmatkul='$kmatkul'";                        
                $this->DB->setFieldTable(array('kmatkul','nmatkul','semester'));
                $r = $this->DB->getRecord($str);
                
                if (!isset($r[1])) {                                
                    throw new Exception ("Kode matakuliah ($value) tidak terdaftar.");		
                }
                $datamatkul=$_SESSION['currentPageMatkulPrasyarat']['dataMatkul'];                
                if ($datamatkul['semester'] <= $r[1]['semester']) {
                    throw new Exception ("Semester matakuliah yang di inputkan tidak boleh lebih besar atau lebih besar sama dengan dari matakuliah ini.");		
                }      
                $id=$datamatkul['kmatkul'];
                if ($this->DB->checkRecordIsExist("kmatkul_syarat",'matakuliah_syarat',$kmatkul, " AND kmatkul='$id'")) {                                
                    throw new Exception ("Kode matakuliah ($kmatkul) sudah menjadi prasyarat matakuliah ini.");		
                } 
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function saveData ($sender,$param) {
        if ($this->IsValid) {            
			$kmatkul=$_SESSION['currentPageMatkulPrasyarat']['dataMatkul']['kmatkul'];		
			$kmatkul_syarat=$this->Demik->getIDKurikulum($_SESSION['kjur']).'_'.$this->txtAddKodeMatkul->Text;				
			$str = "INSERT INTO matakuliah_syarat (kmatkul,kmatkul_syarat) VALUES ('$kmatkul','$kmatkul_syarat')";
			$this->DB->insertRecord($str);            
            $this->redirect('dmaster.MatkulPrasyarat',true,array('id'=>$kmatkul));
        }
    }
    public function deleteRecord($sender,$param) { 		
		$id=$this->getDataKeyField($sender,$this->RepeaterS);		
		$this->DB->deleteRecord("matakuliah_syarat WHERE idsyarat_kmatkul='$id'");
        $kmatkul=$_SESSION['currentPageMatkulPrasyarat']['dataMatkul']['kmatkul'];	
		$this->redirect('dmaster.MatkulPrasyarat',true,array('id'=>$kmatkul));
	}
}
?>