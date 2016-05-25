<?php
prado::using ('Application.MainPageM');
class CDaftarPertanyaan extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuKuesioner=true;
        $this->showDaftarPertanyaan=true;
                
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentDaftarPertanyaan'])||$_SESSION['currentDaftarPertanyaan']['page_name']!='m.dmaster.DaftarPertanyaan') {                
				$_SESSION['currentDaftarPertanyaan']=array('page_name'=>'m.dmaster.DaftarPertanyaan','page_num'=>0,'search'=>false,'idkelompok_pertanyaan'=>'none');												
			}
            $_SESSION['currentDaftarPertanyaan']['search']=false; 
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTA->DataSource=$ta;					
			$this->tbCmbTA->Text=$_SESSION['ta'];						
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $kelompok_pertanyaan=$this->DMaster->getListKelompokPertanyaan();
            
            $this->cmbKelompokPertanyaan->DataSource=$kelompok_pertanyaan;
            $this->cmbKelompokPertanyaan->Text=$_SESSION['currentDaftarPertanyaan']['idkelompok_pertanyaan'];
            $this->cmbKelompokPertanyaan->DataBind();
            
            $this->populateData();
		}			
	}   
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentDaftarPertanyaan']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentDaftarPertanyaan']['search']);
	}	
    public function changeKelompokPertanyaan($sender,$param) {					
		$_SESSION['currentDaftarPertanyaan']['idkelompok_pertanyaan']=$this->cmbKelompokPertanyaan->Text;        
		$this->populateData();
	}
    public function getInfoToolbar() {                
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	}    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentDaftarPertanyaan']['search']=true;
		$this->populateData($_SESSION['currentDaftarPertanyaan']['search']);
	}
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {		
            if ($item->DataItem['ada']) {
                $item->literalNamaKelompok->Text='<tr class="success">
                                                    <td colspan="9">'.$item->DataItem['nama_kelompok'].'</td></tr>';
            }
        }
    }
	public function populateData($search=false) {
        $ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
        $kelompok_pertanyaan=$this->DMaster->getListKelompokPertanyaan();        
        if ($_SESSION['currentDaftarPertanyaan']['idkelompok_pertanyaan']=='none'){            
            $kelompok_pertanyaan[0]='UNDEFINED';
            unset($kelompok_pertanyaan['none']);
        }else{
            $kelompok_pertanyaan=array($_SESSION['currentDaftarPertanyaan']['idkelompok_pertanyaan']=>$kelompok_pertanyaan[$_SESSION['currentDaftarPertanyaan']['idkelompok_pertanyaan']]);
        }
        $result=array();
        while (list($idkelompok_pertanyaan,$nama_kelompok)=each($kelompok_pertanyaan)) {
            $str = "SELECT idkuesioner,idkelompok_pertanyaan,pertanyaan,`orders`,date_added FROM kuesioner k WHERE tahun='$ta' AND idsmt='$idsmt' AND idkelompok_pertanyaan=$idkelompok_pertanyaan ORDER BY (orders+0) ASC";
            $this->DB->setFieldTable(array('idkuesioner','idkelompok_pertanyaan','pertanyaan','orders','date_added'));
            $r=$this->DB->getRecord($str);
            $jumlah_r=count($r);
            if ($jumlah_r > 0) {
                $r[1]['ada']=true;
                $r[1]['nama_kelompok']=$nama_kelompok;
                $idkuesioner=$r[1]['idkuesioner'];
                $str = "SELECT GROUP_CONCAT(nama_indikator) AS nama_indikator FROM kuesioner_indikator WHERE idkuesioner=$idkuesioner GROUP BY idkuesioner";                    
                $this->DB->setFieldTable(array('nama_indikator'));
                $ind=$this->DB->getRecord($str);            
                $indikator=explode(',',$ind[1]['nama_indikator']);            
                $r[1]['indikator1']=$indikator[0];
                $r[1]['indikator2']=$indikator[1];
                $r[1]['indikator3']=$indikator[2];
                $r[1]['indikator4']=$indikator[3];
                $r[1]['indikator5']=$indikator[4];  
                $result[]=$r[1];
                next($r);                
                while (list($k,$v)=each($r)) {
                    $idkuesioner=$v['idkuesioner'];
                    $str = "SELECT GROUP_CONCAT(nama_indikator) AS nama_indikator FROM kuesioner_indikator WHERE idkuesioner=$idkuesioner GROUP BY idkuesioner";                    
                    $ind=$this->DB->getRecord($str);            
                    $indikator=explode(',',$ind[1]['nama_indikator']);            
                    $v['indikator1']=$indikator[0];
                    $v['indikator2']=$indikator[1];
                    $v['indikator3']=$indikator[2];
                    $v['indikator4']=$indikator[3];
                    $v['indikator5']=$indikator[4];                    
                    $result[]=$v;
                }                
            }            
        }                
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}	
    public function addProcess ($sender,$param) {		
        $this->idProcess='add';
        $kelompok_pertanyaan=$this->DMaster->getListKelompokPertanyaan();
        $this->cmbAddKelompokPertanyaan->DataSource=$kelompok_pertanyaan;
        $this->cmbAddKelompokPertanyaan->DataBind();
    }
    public function saveData ($sender,$param) {		
		if ($this->IsValid) {
            $ta=$_SESSION['ta'];
            $idsmt=$_SESSION['semester'];
            $idkelompok_pertanyaan=$this->cmbAddKelompokPertanyaan->Text;
            $pertanyaan=  addslashes($this->txtAddPertanyaan->Text);
            $urutan = addslashes($this->txtAddUrutan->Text);
            $indikator1=  addslashes($this->txtAddIndikator1->Text);
            $indikator2=  addslashes($this->txtAddIndikator2->Text);
            $indikator3=  addslashes($this->txtAddIndikator3->Text);
            $indikator4=  addslashes($this->txtAddIndikator4->Text);
            $indikator5=  addslashes($this->txtAddIndikator5->Text);
            
            $str = "INSERT INTO kuesioner (idkuesioner,idsmt,tahun,idkelompok_pertanyaan,pertanyaan,`orders`,date_added,date_modified) VALUES (NULL,$idsmt,$ta,'$idkelompok_pertanyaan','$pertanyaan','$urutan',NOW(),NOW())";
            $this->DB->query('BEGIN');
            if ($this->DB->insertRecord($str)) {
                $idkuesioner=$this->DB->getLastInsertID();
                $str ="INSERT INTO kuesioner_indikator (idindikator,idkuesioner,nilai_indikator,nama_indikator) VALUES (NULL,$idkuesioner,1,'$indikator1'),(NULL,$idkuesioner,2,'$indikator2'),(NULL,$idkuesioner,3,'$indikator3'),(NULL,$idkuesioner,4,'$indikator4'),(NULL,$idkuesioner,5,'$indikator5')";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->redirect('dmaster.DaftarPertanyaan',true);  
            }else {
                $this->DB->query('ROLLBACK');
            }            
        }
	}
    public function editRecord ($sender,$param) {		
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;    
        
        $str = "SELECT idkelompok_pertanyaan,pertanyaan,orders FROM kuesioner WHERE idkuesioner=$id";
        $this->DB->setFieldTable(array('idkelompok_pertanyaan','pertanyaan','orders'));        
        $r=$this->DB->getRecord($str);
        $kelompok_pertanyaan=$this->DMaster->getListKelompokPertanyaan();
        $this->cmbEditKelompokPertanyaan->DataSource=$kelompok_pertanyaan;
        $this->cmbEditKelompokPertanyaan->Text=$r[1]['idkelompok_pertanyaan'];
        $this->cmbEditKelompokPertanyaan->DataBind();
        
        $this->txtEditPertanyaan->Text=$r[1]['pertanyaan'];
        $this->txtEditUrutan->Text=$r[1]['orders'];
        $str = "SELECT GROUP_CONCAT(nama_indikator) AS nama_indikator FROM kuesioner_indikator WHERE idkuesioner=$id GROUP BY idkuesioner";
        $this->DB->setFieldTable(array('nama_indikator'));
        $ind=$this->DB->getRecord($str);            
        $indikator=explode(',',$ind[1]['nama_indikator']);            
        $this->txtEditIndikator1->Text=$indikator[0];
        $this->txtEditIndikator2->Text=$indikator[1];
        $this->txtEditIndikator3->Text=$indikator[2];
        $this->txtEditIndikator4->Text=$indikator[3];
        $this->txtEditIndikator5->Text=$indikator[4];
    }
    public function updateData ($sender,$param) {		
		if ($this->IsValid) {    
            $idkuesioner=$this->hiddenid->Value;
            $idkelompok_pertanyaan=$this->cmbEditKelompokPertanyaan->Text;
            $pertanyaan=  addslashes($this->txtEditPertanyaan->Text);
            $urutan = addslashes($this->txtEditUrutan->Text);
            $indikator1=  addslashes($this->txtEditIndikator1->Text);
            $indikator2=  addslashes($this->txtEditIndikator2->Text);
            $indikator3=  addslashes($this->txtEditIndikator3->Text);
            $indikator4=  addslashes($this->txtEditIndikator4->Text);
            $indikator5=  addslashes($this->txtEditIndikator5->Text);
            
            $str = "UPDATE kuesioner SET idkelompok_pertanyaan=$idkelompok_pertanyaan,pertanyaan='$pertanyaan',`orders`='$urutan',date_modified=NOW() WHERE idkuesioner=$idkuesioner";            
            $this->DB->query('BEGIN');
            if ($this->DB->updateRecord($str)) {
                $this->DB->deleteRecord("kuesioner_indikator WHERE idkuesioner=$idkuesioner");
                $str ="INSERT INTO kuesioner_indikator (idindikator,idkuesioner,nilai_indikator,nama_indikator) VALUES (NULL,$idkuesioner,1,'$indikator1'),(NULL,$idkuesioner,2,'$indikator2'),(NULL,$idkuesioner,3,'$indikator3'),(NULL,$idkuesioner,4,'$indikator4'),(NULL,$idkuesioner,5,'$indikator5')";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->redirect('dmaster.DaftarPertanyaan',true);  
            }else {
                $this->DB->query('ROLLBACK');
            }            
        }
	}
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        
        $this->DB->deleteRecord("kuesioner WHERE idkuesioner='$id'");
        $this->redirect('dmaster.DaftarPertanyaan',true);  
        
    }    
	public function printOut ($sender,$param) {		
		
	}
}

?>