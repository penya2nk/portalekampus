<?php
prado::using ('Application.MainPageDW');
class DetailKRSEkstension extends MainPageDW {
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
        $this->showKRSEkstension = true;           
        $this->createObj('KRS');
		if (!$this->IsPostBack&&!$this->IsCallback) {	            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
				
            $this->populateData();				
            $this->lblModulHeader->Text=$this->getInfoToolbar();            
				
		}				
	}
    public function getDataMHS($idx) {		        
        return $this->KRS->getDataMHS($idx);
    }
    public function getInfoToolbar() {                
		$ta=$this->DMaster->getNamaTA($this->Page->KRS->DataKRS['krs']['tahun']);
		$semester=$this->setup->getSemester($this->Page->KRS->DataKRS['krs']['idsmt']);
		$text="TA $ta Semester $semester";
		return $text;
	}	
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {                
            DetailKRSEkstension::$totalSKS+=$item->DataItem['sks'];
            DetailKRSEkstension::$jumlahMatkul+=1;
        }
    }
	protected function populateData () {
        try {			
            $idkrs=addslashes($this->request['id']);            				
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,vdm.iddosen_wali,vdm.idkelas,vdm.k_status,sm.n_status AS status,krs.idsmt,krs.tahun,krs.tasmt,krs.sah FROM krs LEFT JOIN v_datamhs vdm ON (krs.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) WHERE krs.idkrs='$idkrs'";
            $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status','status','idsmt','tahun','tasmt','sah'));
            $r=$this->DB->getRecord($str);	           
            $datamhs=$r[1];
            if (!isset($r[1])) {
                $_SESSION['currentPageKRSEkstension']['DataKRS']=array();
                throw new Exception("KRS dengan ID ($idkrs) tidak terdaftar.");
            }  
            $datamhs['iddata_konversi']=$this->KRS->isMhsPindahan($datamhs['nim'],true);            
            $this->KRS->setDataMHS($datamhs);
            
            $kelas=$this->KRS->getKelasMhs();	
            $datamhs['nkelas']=($kelas['nkelas']=='')?'Belum ada':$kelas['nkelas'];			                    
            $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];
            
            $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
            $datamhs['nama_dosen']=$nama_dosen;
            
            $_SESSION['currentPageKRSEkstension']['DataMHS']=$datamhs;
            $this->KRS->setDataMHS($datamhs);
            $this->KRS->getKRS($_SESSION['ta'],$_SESSION['semester']);                                                                        
            $_SESSION['currentPageKRSEkstension']['DataKRS']=$this->KRS->DataKRS;
            $this->RepeaterS->DataSource=$this->KRS->DataKRS['matakuliah'];
            $this->RepeaterS->dataBind();
        }catch (Exception $e) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();	
        }

	}		
    public function tambahKRS ($sender,$param) {
        $this->createObj('Nilai');
        $datakrs=$_SESSION['currentPageKRSEkstension']['DataKRS']; 
        $maxSKS=24;        
        $datakrs['krs']['maxSKS']=$maxSKS;               
        $this->Nilai->setDataMHS($_SESSION['currentPageKRSEkstension']['DataMHS']);
        $this->Nilai->getKHSBeforeCurrentSemester($datakrs['krs']['tahun'],$datakrs['krs']['idsmt']);
        $datakrs['krs']['ipstasmtbefore']=$this->Nilai->getIPS();
        $_SESSION['currentPageKRSEkstension']['DataKRS']=$datakrs;
        $this->redirect ('perkuliahan.TambahKRSEkstension',true);
    }
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);  
        $idkrs=$_SESSION['currentPageKRSEkstension']['DataKRS']['krs']['idkrs'];
        $this->DB->deleteRecord("krsmatkul WHERE idkrsmatkul=$id");
        $this->redirect ('perkuliahan.DetailKRSEkstension',true,array('id'=>$idkrs));        
    }
    public function closeDetailKRSEkstension ($sender,$param) { 
        unset($_SESSION['currentPageKRSEkstension']);
        $this->redirect ('perkuliahan.KRSEkstension',true);
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

                $dataReport=$_SESSION['currentPageKRSEkstension']['DataMHS'];
                $dataReport['krs']=$_SESSION['currentPageKRSEkstension']['DataKRS']['krs'];        
                $dataReport['matakuliah']=$_SESSION['currentPageKRSEkstension']['DataKRS']['matakuliah'];        
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