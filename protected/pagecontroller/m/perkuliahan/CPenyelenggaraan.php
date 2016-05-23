<?php
prado::using ('Application.MainPageM');
class CPenyelenggaraan extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPenyelenggaraan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePenyelenggaraan'])||$_SESSION['currentPagePenyelenggaraan']['page_name']!='m.perkuliahan.Penyelenggaraan') {                
				$_SESSION['currentPagePenyelenggaraan']=array('page_name'=>'m.perkuliahan.Penyelenggaraan','page_num'=>0,'search'=>false,'DaftarDosen'=>array());												
			}
            $_SESSION['currentPagePenyelenggaraan']['search']=false;
            $_SESSION['currentPagePenyelenggaraan']['DaftarDosen']=$this->DMaster->getDaftarDosen();
                        
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();	
            
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTA->DataSource=$ta;					
			$this->tbCmbTA->Text=$_SESSION['ta'];						
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();		
		}			
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPagePenyelenggaraan']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPagePenyelenggaraan']['search']);
	}	
    public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
	public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$this->Demik->getIDKurikulum($kjur);
        
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur ORDER BY semester ASC,kmatkul ASC";
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen'));			
        $r= $this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($v['idpenyelenggaraan']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}
	public function itemBound ($sender,$param) {
		$item=$param->item;
		if ($item->itemType==='Item' || $item->itemType === 'AlternatingItem') {
			$item->cmbFrontDosen->DataSource=$this->DMaster->removeIdFromArray($_SESSION['currentPagePenyelenggaraan']['DaftarDosen'],'none');			
            $item->cmbFrontDosen->Text=$item->DataItem['iddosen'];
			$item->cmbFrontDosen->dataBind();									
		}
	}	
	public function addProcess ($sender,$param) {		
		$this->idProcess='add';	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$this->Demik->getIDKurikulum($kjur);
        $str = "SELECT kmatkul,nmatkul,sks,semester FROM matakuliah WHERE aktif=1 AND kmatkul NOT IN (SELECT kmatkul FROM penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur') AND idkur='$idkur' ORDER BY semester,nmatkul ASC";
        $this->DB->setFieldTable (array('kmatkul','nmatkul','sks','semester'));
        $r = $this->DB->getRecord($str);					
		$this->RepeaterAdd->DataSource=$r;
		$this->RepeaterAdd->dataBind();		
	}
	public function setDosen ($sender,$param) {
		$item=$param->item;
		if ($item->itemType==='Item' || $item->itemType === 'AlternatingItem') {
            $dd=$_SESSION['currentPagePenyelenggaraan']['DaftarDosen'];
            $dd['none']=' ';
			$item->cmbAddDaftarDosen->DataSource=$dd;
			$item->cmbAddDaftarDosen->dataBind();			
		}
	}
	public function saveData ($sender,$param) {
		if ($this->page->isValid) {				
			$ta=$_SESSION['ta'];
            $idsmt=$_SESSION['semester'];
            $kjur=$_SESSION['kjur'];
            
			$str = "INSERT INTO penyelenggaraan (idpenyelenggaraan,idsmt,tahun,kmatkul,kjur,iddosen) VALUES (NULL,'$idsmt','$ta','";
			$str_pengampu='INSERT INTO pengampu_penyelenggaraan (idpengampu_penyelenggaraan,idpenyelenggaraan,iddosen) VALUES (NULL,';
			foreach ($this->RepeaterAdd->Items As $inputan) {
                $iddosen=$inputan->cmbAddDaftarDosen->Text;
				if ($iddosen != 'none'&& $iddosen !='') {					
					$kmatkul=$inputan->txtKmatkul->Value;					
					$str2 = $str . "$kmatkul','$kjur',$iddosen)";					
					$this->DB->query('BEGIN');
					if ($this->DB->insertRecord($str2)) {
						$idpenyelenggaraan=$this->DB->getLastInsertID();
						$str_pengampu2=$str_pengampu."$idpenyelenggaraan,$iddosen)";
						$this->DB->insertRecord($str_pengampu2);
						$this->DB->query('COMMIT');
					}else {
						$this->DB->query('ROLLBACK');
					}
				}
			}
			$this->redirect('perkuliahan.Penyelenggaraan',true);     
		}
	}
	
	public function ubahPengampuMatkul ($sender,$param) {		
		$idpenyelenggaraan=$this->getDataKeyField($sender,$this->RepeaterS);
		$iddosen=$sender->Text;				
		$str = "UPDATE penyelenggaraan SET iddosen='$iddosen' WHERE idpenyelenggaraan='$idpenyelenggaraan'";
		$this->DB->query('BEGIN');
		if ($this->DB->updateRecord($str)) {			
			$str = "UPDATE pengampu_penyelenggaraan SET iddosen='$iddosen' WHERE idpenyelenggaraan='$idpenyelenggaraan'";
			$this->DB->query('COMMIT');
		}else {
			$this->DB->query('ROLLBACK');
		}	
		$this->redirect('perkuliahan.Penyelenggaraan',true);            
	}
	
	public function deleteRecord ($sender,$param) {		
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist ('idpenyelenggaraan','krsmatkul',$id)) {	            
            $this->lblHeaderMessageError->Text='Menghapus Penyelenggaraan Matakuliah';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus penyelenggaraan ini, karena sedang digunakan di KRS Mahasiswa.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("penyelenggaraan WHERE idpenyelenggaraan=$id");
            $this->redirect('perkuliahan.Penyelenggaraan',true);            
        }
	}	
	public function printOut ($sender,$param) {		
		
	}
}

?>