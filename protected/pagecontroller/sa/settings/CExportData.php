<?php
prado::using ('Application.MainPageSA');
class CExportData extends MainPageSA {    
	public function onLoad($param) {		
		parent::onLoad($param);				        
		$this->showVariable=true;       
		if (!$this->IsPostBack&&!$this->IsCallBack) {	           
            if (!isset($_SESSION['currentPageExportData'])||$_SESSION['currentPageExportData']['page_name']!='sa.settings.ExportData') {
				$_SESSION['currentPageExportData']=array('page_name'=>'sa.settings.ExportData','page_num'=>0);												
			}            
		}
	}     
    public function cekNIM ($sender,$param) {     
        $nim=addslashes($param->Value);     
        if ($nim != '') {
            try {
                $str = "SELECT nim FROM v_datamhs vdm  WHERE vdm.nim='$nim'";
                $this->DB->setFieldTable(array('nim'));
                $r=$this->DB->getRecord($str);             
                if (!isset($r[1])) {
                    throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }   
        }   
    }
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            switch ($sender->getId()) {
                case 'btnSaveExportNIM' :
                    $nim=addslashes($nim);
                                
                break;
            }   
            $this->redirect('settings.ExportData',true);
        }
    }
}