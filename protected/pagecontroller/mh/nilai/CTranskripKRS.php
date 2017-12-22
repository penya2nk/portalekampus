<?php
prado::using ('Application.MainPageMHS');
class CTranskripKRS extends MainPageMHS {		
	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showTranskripKRS=true;    
        $this->createObj('Nilai');
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageTranskripKRS'])||$_SESSION['currentPageTranskripKRS']['page_name']!='mh.nilai.TranskripKRS') {
				$_SESSION['currentPageTranskripKRS']=array('page_name'=>'mh.nilai.TranskripKRS','page_num'=>0,'search'=>false);												                                               
			}  
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
			$this->populateData();	
		}
	}	
	protected function populateData() {		
        $datamhs=$this->Pengguna->getDataUser();
        $this->Nilai->setDataMHS($datamhs);
		$transkrip = $this->Nilai->getTranskripFromKRS(true);		
        
		$this->RepeaterS->DataSource=$transkrip;
		$this->RepeaterS->dataBind();		
	}
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             		
        $dataReport=$this->Pengguna->getDataUser();  
        $dataReport['cek_isikuesioner']=true;
        $dataReport['linkoutput']=$this->linkOutput; 
        $this->report->setDataReport($dataReport); 
        $this->report->setMode($_SESSION['outputreport']);
		$this->report->printTranskripKRS($this->Nilai);				
        
        $this->lblPrintout->Text='Transkrip KRS';
        $this->modalPrintOut->show();
	}
}