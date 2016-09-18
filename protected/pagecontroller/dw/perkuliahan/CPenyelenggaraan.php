<?php
prado::using ('Application.MainPageDW');
class CPenyelenggaraan extends MainPageDW {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPenyelenggaraan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePenyelenggaraan'])||$_SESSION['currentPagePenyelenggaraan']['page_name']!='dw.perkuliahan.Penyelenggaraan') {                
				$_SESSION['currentPagePenyelenggaraan']=array('page_name'=>'dw.perkuliahan.Penyelenggaraan','page_num'=>0,'search'=>false,'DaftarDosen'=>array());												
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
        $iddosen_wali=$this->iddosen_wali;
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$this->Demik->getIDKurikulum($kjur);
        
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nama_dosen,nidn FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur ORDER BY semester ASC,kmatkul ASC";
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nama_dosen','nidn'));			
        $r= $this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpenyelenggaraan=$v['idpenyelenggaraan'];            
            $jumlah_peserta=$this->Demik->getJumlahMhsInPenyelenggaraan($idpenyelenggaraan," AND iddosen_wali=$iddosen_wali");	
            $v['jumlah_peserta']=$jumlah_peserta;
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}	

}

?>