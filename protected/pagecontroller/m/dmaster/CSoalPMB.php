<?php
prado::using ('Application.MainPageM');
class CSoalPMB extends MainPageM {
	public $spmb;
	public function onLoad($param) {
		parent::onLoad($param);		
		$this->showSoalPMB = true;
		if (!$this->IsPostBack && !$this->IsCallBack) {					
			if (!isset($_SESSION['currentPageSoalPMB'])||$_SESSION['currentPageSoalPMB']['page_name']!='m.dmaster.SoalPMB') {
                $_SESSION['currentPageSoalPMB']=array('page_name'=>'m.dmaster.SoalPMB','page_num'=>0,'search'=>false);
			}
            $result = array();
			for ($i=1;$i<=4;$i++) {
				$data = array('no'=>$i);
				$result[]=$data;
			}
			$this->RepeaterJawaban->DataSource=$result;
			$this->RepeaterJawaban->dataBind();            
			$this->populateData();						
		}		
	}		
	public function btnSearch_Click ($sender,$param) {		
		$this->populateData ($this->getStrSearch());
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageSoalPMB']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
	public function populateData () {	
		$this->RepeaterS->VirtualItemCount=$this->DB->getCountRowsOfTable('soal');	
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageSoalPMB']['page_num'];
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageSoalPMB']['page_num']=0;}
		$str = "SELECT s.idsoal,nama_soal,date_added,date_modified FROM soal s ORDER BY date_modified DESC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idsoal','nama_soal','date_added','date_modified')); 
		$r=$this->DB->getRecord($str,$offset+1);			
        $result=array();
        $str = "SELECT j.jawaban FROM jawaban j WHERE status=1 AND idsoal=";
		$this->DB->setFieldTable(array('jawaban')); 		
        while (list($k,$v)=each($r)){            
            $re=$this->DB->getRecord($str.$v['idsoal']);			
            $v['jawaban']=isset($re[1])?$re[1]['jawaban']:'-';
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}	
	public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {					
			$item->rdJawaban->setUniqueGroupName('jawaban');
		}
	}
    public function setJawaban	($sender,$param) {
		if ($this->IsValid) {
			$jumlah_jawaban=$this->txtAddJumlahJawaban->Text;
			$result = array();
			for ($i=1;$i<=$jumlah_jawaban;$i++) {
				$data = array('no'=>$i);
				$result[]=$data;
			}
			$this->RepeaterJawaban->DataSource=$result;
			$this->RepeaterJawaban->dataBind();
		}
	}
	public function saveData () {
		if ($this->IsValid) {
            $nama_soal=  addslashes($this->txtAddNamaSoal->Text);
            $str = "INSERT INTO soal (idsoal,nama_soal,date_added,date_modified) VALUES (NULL,'$nama_soal',NOW(),NOW())";
            $this->DB->query('BEGIN'); 
            if ($this->DB->insertRecord($str)) {
                $i=0;                
                $idsoal=$this->DB->getLastInsertID();
                $countItem=$this->RepeaterJawaban->Items->getCount(); 
                foreach ($this->RepeaterJawaban->Items as $inputan) {				
                    $jawaban=addslashes($inputan->txtJawaban->Text);
                    $checked=$inputan->rdJawaban->Checked==true?1:0;
                    if ($countItem > $i+1) {    
                        $values=$values."(NULL,$idsoal,'$jawaban',$checked),";                            
                    }else {
                        $values=$values."(NULL,$idsoal,'$jawaban',$checked)";                            
                    }
                    $i++;      

                }
                $str = "INSERT INTO jawaban (idjawaban,idsoal,jawaban,status) VALUES $values";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->redirect('dmaster.SoalPMB',true);
            }else {
                $this->DB->query('ROLLBACK');
            } 
		}
	}
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->hiddenidsoal->Value=$id;        
        $str = "SELECT nama_soal FROM soal s WHERE idsoal=$id";
        $this->DB->setFieldTable(array('nama_soal'));
        $r=$this->DB->getRecord($str);
        $this->txtEditNamaSoal->Text=$r[1]['nama_soal'];
        
        $str = "SELECT jawaban,status FROM jawaban WHERE idsoal=$id";
        $this->DB->setFieldTable(array('jawaban','status'));
        $re=$this->DB->getRecord($str);
        $this->RepeaterEditJawaban->DataSource=$re;
        $this->RepeaterEditJawaban->dataBind();        
        
    } 
    public function updateData () {
		if ($this->IsValid) {
            $id=$this->hiddenidsoal->Value;
            $nama_soal=  addslashes($this->txtEditNamaSoal->Text);
            $str = "UPDATE soal SET nama_soal='$nama_soal',date_modified=NOW() WHERE idsoal=$id";
            $this->DB->query('BEGIN'); 
            if ($this->DB->updateRecord($str)) {
                $this->DB->deleteRecord("jawaban WHERE idsoal=$id"); 
                $i=0;                                
                $countItem=$this->RepeaterEditJawaban->Items->getCount(); 
                foreach ($this->RepeaterEditJawaban->Items as $inputan) {				
                    $jawaban=addslashes($inputan->txtJawaban->Text);
                    $checked=$inputan->rdJawaban->Checked==true?1:0;
                    if ($countItem > $i+1) {    
                        $values=$values."(NULL,$id,'$jawaban',$checked),";                            
                    }else {
                        $values=$values."(NULL,$id,'$jawaban',$checked)";                            
                    }
                    $i++;      

                }
                $str = "INSERT INTO jawaban (idjawaban,idsoal,jawaban,status) VALUES $values";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->redirect('dmaster.SoalPMB',true);
            }else {
                $this->DB->query('ROLLBACK');
            } 
		}
	}
	public function deleteRecord ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist('idsoal','jawaban_ujian',$id)) {
            $this->lblHeaderMessageError->Text='Menghapus Soal PMB';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus soal dengan ID ($id) karena sedang digunakan di jawaban ujian.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("soal WHERE idsoal=$id"); 
            $this->redirect('dmaster.SoalPMB',true);
        }        
    } 
	
}

?>