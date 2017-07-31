<?php
prado::using ('Application.MainPageK');
class CHome extends MainPageK {
    public static $TotalPembayaranMahasiswa = 0;
	public function onLoad($param) {
		parent::onLoad($param);		            
        $this->showDashboard=true;  
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();			
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->populateData();
		}                
	}
    public function changeTbTA ($sender,$param) {				
        $ta=$this->tbCmbTA->Text;
		$_SESSION['ta']=$ta;    
		$this->redirect('Home',true);
	} 
    public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function populateData () {
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $totalpembayaranmahasiswa = $this->DB->getSumRowsOfTable('dibayarkan',"transaksi t,transaksi_detail td WHERE t.no_transaksi=td.no_transaksi AND t.tahun=$ta AND idsmt=$idsmt");
        CHome::$TotalPembayaranMahasiswa = $totalpembayaranmahasiswa;
    }
}
?>