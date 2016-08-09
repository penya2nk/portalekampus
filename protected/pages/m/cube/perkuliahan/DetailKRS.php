<?php
prado::using ('Application.pagecontroller.m.perkuliahan.CDetailKRS');
class DetailKRS extends CDetailKRS {		
    public function onLoad($param) {
		parent::onLoad($param);	
        if (!$this->IsPostBack&&!$this->IsCallback) {
            try {
                if ($_SESSION['currentPageKRS']['DataMHS']['idkelas'] == 'C') {
                    $idkrs=$_SESSION['currentPageKRS']['DataKRS']['krs']['idkrs'];
                    throw new Exception("KRS dengan ID ($idkrs) terdaftar di KRS Ekstension.");
                }
            } catch (Exception $e) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$e->getMessage();
            }            
        }        
	} 
}

?>