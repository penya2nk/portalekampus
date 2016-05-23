<?php
prado::using ('Application.MainPageMHS');
class CKRS extends MainPageMHS {
	/**
	* total SKS
	*/
	static $totalSKS=0;
	
	/**
	* jumlah matakuliah
	*/
	static $jumlahMatkul=0;	
	public function onLoad($param) {
		parent::onLoad($param);	
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showKRS = true;   
        
        $this->createObj('KRS');
		if (!$this->IsPostBack&&!$this->IsCallback) {	
            if (!isset($_SESSION['currentPageKRS'])||$_SESSION['currentPageKRS']['page_name']!='mh.perkuliahan.KRS') {
				$_SESSION['currentPageKRS']=array('page_name'=>'mh.perkuliahan.KRS','page_num'=>0,'DataKRS'=>array());
			} 
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
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
    public function getInfoToolbar() {                
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	}
	public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
		$this->redirect('perkuliahan.KRS',true);        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
		$this->redirect('perkuliahan.KRS',true);
	}	
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {                
            CKRS::$totalSKS+=$item->DataItem['sks'];
            CKRS::$jumlahMatkul+=1;
        }
    }
	protected function populateData () {
        try {			
            $datamhs=$this->Pengguna->getDataUser();            
            $this->KRS->setDataMHS($datamhs);
            $this->KRS->getKRS($_SESSION['ta'],$_SESSION['semester']);                                                            
            $_SESSION['currentPageKRS']['DataKRS']=$this->KRS->DataKRS;
            $this->RepeaterS->DataSource=$this->KRS->DataKRS['matakuliah'];
            $this->RepeaterS->dataBind();
        }catch (Exception $e) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();	
        }

	}		
	public function printKRS ($sender,$param) {
        $this->createObj('reportkrs');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :
                $messageprintout="Mohon maaf Print out pada mode excel 2007 belum kami support.";                
            break;
            case  'pdf' :                
                $messageprintout='';                
                $tahun=$_SESSION['ta'];
                $semester=$_SESSION['semester'];
                $nama_tahun = $this->DMaster->getNamaTA($tahun);
                $nama_semester = $this->setup->getSemester($semester);

                $dataReport=$this->Pengguna->getDataUser();
                $dataReport['krs']=$_SESSION['currentPageKRS']['DataKRS']['krs'];        
                $dataReport['matakuliah']=$_SESSION['currentPageKRS']['DataKRS']['matakuliah'];        
                $dataReport['nama_tahun']=$nama_tahun;
                $dataReport['nama_semester']=$nama_semester;        
                
                $kaprodi=$this->KRS->getKetuaPRODI($dataReport['kjur']);                  
                $dataReport['nama_kaprodi']=$kaprodi['nama_dosen'];
                $dataReport['jabfung_kaprodi']=$kaprodi['nama_jabatan'];
                $dataReport['nipy_kaprodi']=$kaprodi['nipy'];
                
                $dataReport['linkoutput']=$this->linkOutput;                 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                $this->report->printKRS();				

                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text="Kartu Rencana Studi T.A $nama_tahun Semester $nama_semester";
        $this->modalPrintOut->show();
	}
}

?>