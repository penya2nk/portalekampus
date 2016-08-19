<?php
prado::using ('Application.MainPageMHS');
class CKHS extends MainPageMHS {		    
	public static $TotalSKS=0;
	public static $TotalM=0;
    public $NilaiSemesterLalu;
    public $NilaiSemesterSekarang;
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikNilai=true;
        $this->showKHS=true;    
        $this->createObj('Nilai');
        
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageKHS'])||$_SESSION['currentPageKHS']['page_name']!='mh.nilai.KHS') {
				$_SESSION['currentPageKHS']=array('page_name'=>'mh.nilai.KHS','page_num'=>0,'search'=>false);												                                               
			}            		
            $nama_tahun=$this->DMaster->getNamaTA ($_SESSION['ta']);
            $nama_semester = $this->setup->getSemester($_SESSION['semester']);
            $this->labelModuleHeader->Text = "T.A $nama_tahun Semester $nama_semester";
			$this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();			
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
			$this->populateData();            
		}
	}
	
	public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
		$this->redirect('nilai.KHS',true);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
		$this->redirect('nilai.KHS',true);
	}	
    public function itemBound ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {                
            $sks=$item->DataItem['sks'];
            KHS::$TotalSKS += $sks;            
            $m = (intval($sks)) * $this->Nilai->getAngkaMutu($item->DataItem['n_kual']);
            KHS::$TotalM += $m;            
        }
    }
	protected function populateData () {						
		try {			
            $datamhs=$this->Pengguna->getDataUser();            
            $this->Nilai->setDataMHS($datamhs);
			if ($_SESSION['ta']>= 2010 && $datamhs['idkelas']!='C') {
                $this->createObj('Finance');
                $datadulang=$this->Nilai->getDataDulang($_SESSION['semester'],$_SESSION['ta']);
                $idkelas=$datadulang['idkelas'];
                $datamhs['idkelas']=$idkelas;
                $datamhs['idsmt']=$_SESSION['semester'];
                $this->Finance->setDataMHS($datamhs);
				$data=$this->Finance->getLunasPembayaran($_SESSION['ta'],$_SESSION['semester'],true);	
                
				if (!$data['bool'])throw new Exception ("Anda tidak bisa melihat KHS karena Anda baru membayar (".$this->Finance->toRupiah($data['total_bayar'],false)."), dari total kewajiban sebesar (".$this->Finance->toRupiah($data['total_biaya'],false).").");		
			}			
			$khs = $this->Nilai->getKHS($_SESSION['ta'],$_SESSION['semester']);            
			if(isset($khs[1])){
				$this->NilaiSemesterLalu=$this->Nilai->getKumulatifSksDanNmSemesterLalu($_SESSION['ta'],$_SESSION['semester']);				
                $this->NilaiSemesterSekarang=$this->Page->Nilai->getIPKSampaiTASemester($_SESSION['ta'],$_SESSION['semester'],'ipksksnm');
                $this->RepeaterS->DataSource=$khs ;
                $this->RepeaterS->dataBind();							
			}else{				
				throw new Exception ('Anda belum mengisi KRS atau KRS-nya belum disahkan oleh dosen wali.');
			}		
		}catch (Exception $e) {			
			$this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();			
		}						
	}	
	public function printKHS ($sender,$param) {
		$this->createObj('reportnilai');             
		$tahun=$_SESSION['ta'];
        $semester=$_SESSION['semester'];
        $nama_tahun = $this->DMaster->getNamaTA($tahun);
        $nama_semester = $this->setup->getSemester($semester);
        
        $dataReport=$this->Pengguna->getDataUser();
        $dataReport['ta']=$tahun;
        $dataReport['semester']=$semester;
        $dataReport['nama_tahun']=$nama_tahun;
        $dataReport['nama_semester']=$nama_semester;        
        $dataReport['linkoutput']=$this->linkOutput; 
        $this->report->setDataReport($dataReport); 
        $this->report->setMode($_SESSION['outputreport']);
		$this->report->printKHS($this->Nilai);				
        
        $this->lblPrintout->Text="Kartu Hasil Studi T.A $nama_tahun Semester $nama_semester";
        $this->modalPrintOut->show();
	}
}
?>