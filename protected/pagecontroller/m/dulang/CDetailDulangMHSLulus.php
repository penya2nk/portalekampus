<?php
prado::using ('Application.MainPageM');
class CDetailDulangMHSLulus Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSLulus=true;                
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            try {
                if (isset($_SESSION['currentPageDulangMHSLulus']['DataMHS']['no_formulir'])) {
                    $datamhs=$_SESSION['currentPageDulangMHSLulus']['DataMHS'];
                    $nim=$datamhs['nim'];
                    $this->Nilai->setDataMHS($datamhs);
                    
                    $str = "SELECT tahun,idsmt FROM dulang d, (SELECT MAX(iddulang) AS iddulang FROM dulang WHERE nim='$nim' GROUP BY tahun ORDER BY iddulang DESC LIMIT 1) AS temp  WHERE temp.iddulang=d.iddulang";
                    $this->DB->setFieldTable(array('tahun','idsmt'));
                    $datadulang=$this->DB->getRecord($str);	    
                    
                    $this->cmbAddTALulus->DataSource=array($datadulang[1]['tahun']=>$this->DMaster->getNamaTA($datadulang[1]['tahun']));
                    $this->cmbAddTALulus->Text=$datadulang[1]['tahun'];
                    $this->cmbAddTALulus->dataBind();
                     				
                    $this->cmbAddSMTLulus->DataSource=array($datadulang[1]['idsmt']=>$this->setup->getSemester($datadulang[1]['idsmt']));
                    $this->cmbAddSMTLulus->Text=$datadulang[1]['idsmt'];
                    $this->cmbAddSMTLulus->dataBind();
                    
                    $this->Nilai->getTranskripFromKRS ();
                    $jumlah_sks=$this->Nilai->getTotalSKSAdaNilai();
                    $iddata_konversi=$datamhs['iddata_konversi'];
                    if ($iddata_konversi > 0) {
                        $jumlah_sks+=$this->DB->getSumRowsOfTable ('sks',"v_konversi2 WHERE iddata_konversi=$iddata_konversi");
                    }
                    $bool_sks=true;
                    if ($jumlah_sks >= 144) {
                        $ket_jumlah_sks='<span class="label label-success">PASSED</span>';
                    }else{
                        $ket_jumlah_sks="<span class='label label-danger'>FAIL</span> (saat ini baru $jumlah_sks SKS)";
                        $bool_sks=false;
                    }
                    
                    $this->literalJumlahSKS->Text=$ket_jumlah_sks;
                    $bool_bebas_keuangan=true;
                    
                    $this->literalBebasKeuangan->Text='<span class="label label-info">NOT YET IMPLEMENTED</span>';
                    $bool_bebas_perpustakaan=true;
                    $this->literalBebasPerpustakaan->Text='<span class="label label-info">NOT YET IMPLEMENTED</span>';
                    if ($bool_sks == false || $bool_bebas_keuangan==false || $bool_bebas_perpustakaan == false) {
                        $this->btnSave->Enabled=false;
                        $this->btnSave->CssClass='btn';
                    }
                    $this->cmbAddDosenWali->DataSource=$this->DMaster->getListDosenWali();
                    $this->cmbAddDosenWali->Text=$datamhs['iddosen_wali'];
                    $this->cmbAddDosenWali->dataBind();	           
                    
                }else{
                    throw new Exception("No. Formulir belum ada di session.");
                }
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }    
    public function saveData ($sender,$param) {		
		if ($this->IsValid) {	
            $datamhs=$_SESSION['currentPageDulangMHSLulus']['DataMHS'];						
			$ta=$this->cmbAddTALulus->Text;							
			$semester=$this->cmbAddSMTLulus->Text;
            $_SESSION['semester']=$semester;
            $_SESSION['ta']=$ta;
            $_SESSION['kjur']= $datamhs['kjur'];
            $_SESSION['currentPageDulangMHSLulus']['tahun_masuk']=$datamhs['tahun_masuk'];
            $nim=  $datamhs['nim'];           
			$kelas=$datamhs['idkelas'];
            $iddosen_wali=$this->cmbAddDosenWali->Text;
			$str = "UPDATE register_mahasiswa SET iddosen_wali='$iddosen_wali',k_status='L' WHERE nim='$nim'";			
			$this->DB->query ('BEGIN');
			if ($this->DB->updateRecord($str)) {
                $status_sebelumnnya=$datamhs['k_status'];
				$str = "INSERT INTO dulang (iddulang,nim,tahun,idsmt,tanggal,idkelas,status_sebelumnya,k_status) VALUES (NULL,'$nim','$ta','$semester',NOW(),'$kelas','$status_sebelumnnya','L')";
				$this->DB->insertRecord($str);				
				$this->DB->query('COMMIT');
                unset($_SESSION['currentPageDulangMHSLulus']['DataMHS']);
                $this->redirect('dulang.DulangMHSLulus',true);
			}else {
				$this->DB->query('ROLLBACK');
			}
		}
	}
    public function closeDetailDulang ($sender,$param) {
        unset($_SESSION['currentPageDulangMHSLulus']);
        $this->redirect('dulang.DulangMHSLulus',true);
    }
}
?>