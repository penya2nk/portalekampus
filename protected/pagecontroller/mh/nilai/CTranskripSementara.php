<?php
prado::using ('Application.MainPageMHS');
class CTranskripSementara extends MainPageMHS {		
	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showTranskripSementara=true;    
        $this->createObj('Nilai');
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageTranskripSementara'])||$_SESSION['currentPageTranskripSementara']['page_name']!='mh.nilai.TranskripSementara') {
				$_SESSION['currentPageTranskripSementara']=array('page_name'=>'mh.nilai.TranskripSementara','page_num'=>0,'search'=>false);												                                               
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
		$transkrip = $this->Nilai->getTranskrip(true,true);		
        
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
		$this->report->printTranskripSementara($this->Nilai);				
        
        $this->lblPrintout->Text='Transkrip Sementara';
        $this->modalPrintOut->show();
	}
}