<?php
prado::using ('Application.MainPageM');
class CRekapStatusMahasiswa Extends MainPageM {	
    public static $TotalJumlahMahasiswaP=0;
    public static $TotalJumlahMahasiswaW=0;
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showRekapStatusMahasiswa=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageRekapStatusMahasiswa'])||$_SESSION['currentPageRekapStatusMahasiswa']['page_name']!='m.kemahasiswaan.RekapStatusMahasiswa') {
				$_SESSION['currentPageRekapStatusMahasiswa']=array('page_name'=>'m.kemahasiswaan.RekapStatusMahasiswa','page_num'=>0,'search'=>false,'ta1'=>$_SESSION['ta'],'ta2'=>$_SESSION['ta'],'k_status'=>'none');												
			}
            $_SESSION['currentPageRekapStatusMahasiswa']['search']=false; 
            
            $daftar_ps=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');            
			$this->tbCmbPs->DataSource=$daftar_ps;
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();	            
            
            $tahun=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');
            $this->tbCmbTA1->DataSource=$tahun;
            $this->tbCmbTA1->Text= $_SESSION['currentPageRekapStatusMahasiswa']['ta1'];
            $this->tbCmbTA1->dataBind();
            
            $this->tbCmbTA2->DataSource=$this->getAngkatan(false);
            $this->tbCmbTA2->Text= $_SESSION['currentPageRekapStatusMahasiswa']['ta2'];
            $this->tbCmbTA2->dataBind();            
            
            $this->cmbFilterStatus->Text=$_SESSION['currentPageRekapStatusMahasiswa']['k_status'];
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->populateData();            
            $this->setInfoToolbar();
		}	
	}
    
	public function getAngkatan ($tanpanone=true) {
		$dt =$this->DMaster->getListTA();		        
		$ta=$_SESSION['currentPageRekapStatusMahasiswa']['ta1'];		
		$tahun_akademik=$tanpanone==true?array('none'=>'All'):array();
		while (list($k,$v)=each ($dt)) {
			if ($k != 'none') {
				if ($k >= $ta) {
					$tahun_akademik[$k]=$v;
				}
			}			
		}        
		return $tahun_akademik;
	}
    public function setInfoToolbar() {                
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta1=$this->DMaster->getNamaTA($_SESSION['currentPageRekapStatusMahasiswa']['ta1']);
        $ta2=$this->DMaster->getNamaTA($_SESSION['currentPageRekapStatusMahasiswa']['ta2']); 
		$this->lblModulHeader->Text="Program Studi $ps PERIODE $ta1 - $ta2";        
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
    public function changeTbTA1 ($sender,$param) {               
        $ta1=$this->tbCmbTA1->Text;
		$_SESSION['currentPageRekapStatusMahasiswa']['ta1']=$ta1;
        $ta2=$_SESSION['currentPageRekapStatusMahasiswa']['ta2'];
        if ($ta1 > $ta2) {
            $_SESSION['currentPageRekapStatusMahasiswa']['ta2']=$ta1;
        }
        $this->tbCmbTA2->DataSource=$this->getAngkatan(false);
        $this->tbCmbTA2->Text= $_SESSION['currentPageRekapStatusMahasiswa']['ta2'];
        $this->tbCmbTA2->dataBind();
        $this->setInfoToolbar();
		$this->populateData();
	} 
    public function changeTbTA2 ($sender,$param) {               
        $ta2=$this->tbCmbTA2->Text;
		$_SESSION['currentPageRekapStatusMahasiswa']['ta2']=$ta2;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageRekapStatusMahasiswa']['k_status']=$this->cmbFilterStatus->Text;
        $this->redirect('kemahasiswaan.RekapStatusMahasiswa',true);
	} 
	public function populateData() {		
		$ta1=$_SESSION['currentPageRekapStatusMahasiswa']['ta1'];
        $ta2=$_SESSION['currentPageRekapStatusMahasiswa']['ta2'];     
		$kjur=$_SESSION['kjur'];	
        
        $k_status=$_SESSION['currentPageRekapStatusMahasiswa']['k_status'];
        switch ($k_status) {
            case 'A' :                
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'A');
                $this->RepeaterAktifS->DataSource=$r;
                $this->RepeaterAktifS->dataBind();  
            break;
            case 'N' :                
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'N');
                $this->RepeaterNonAktifS->DataSource=$r;
                $this->RepeaterNonAktifS->dataBind();  
            break;
            case 'C' :                
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'C');
                $this->RepeaterCutiS->DataSource=$r;
                $this->RepeaterCutiS->dataBind();  
            break;
            case 'D' :
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'D');
                $this->RepeaterDOS->DataSource=$r;
                $this->RepeaterDOS->dataBind();
            break;
            case 'K' :
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'K');
                $this->RepeaterKS->DataSource=$r;
                $this->RepeaterKS->dataBind();
            break;
            case 'L' :
                $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'L');
                $this->RepeaterLulusS->DataSource=$r;
                $this->RepeaterLulusS->dataBind();
            break;
            default :
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'A');
            $this->RepeaterAktifS->DataSource=$r;
            $this->RepeaterAktifS->dataBind();     

            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP=0;
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW=0;
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'N');
            $this->RepeaterNonAktifS->DataSource=$r;
            $this->RepeaterNonAktifS->dataBind();  

            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP=0;
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW=0;
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'C');
            $this->RepeaterCutiS->DataSource=$r;
            $this->RepeaterCutiS->dataBind();  
            
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP=0;
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW=0;
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'D');
            $this->RepeaterDOS->DataSource=$r;
            $this->RepeaterDOS->dataBind();
            
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP=0;
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW=0;
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'K');
            $this->RepeaterKS->DataSource=$r;
            $this->RepeaterKS->dataBind();
            
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP=0;
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW=0;
            $r=$this->Demik->getRekapStatusMHS($kjur,$ta1,$ta2,'L');
            $this->RepeaterLulusS->DataSource=$r;
            $this->RepeaterLulusS->dataBind();
        }        
	}
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaP+=$item->DataItem['jumlah_pria'];
            CRekapStatusMahasiswa::$TotalJumlahMahasiswaW+=$item->DataItem['jumlah_wanita'];            
        }
    }
    public function generateData ($sender,$param) {
        $ta1=$_SESSION['currentPageRekapStatusMahasiswa']['ta1'];
        $ta2=$_SESSION['currentPageRekapStatusMahasiswa']['ta2'];     
		$kjur=$_SESSION['kjur'];	
        
        $this->DB->deleteRecord("rekap_status_mahasiswa WHERE  kjur=$kjur AND ta >= $ta1 AND ta <= $ta2");
        
        $str = "INSERT INTO rekap_status_mahasiswa (idrekap,nim,nirm,nama_mhs,jk,kjur,ta,idsmt,idkelas,k_status) SELECT NULL,d.nim,rm.nirm,fp.nama_mhs,fp.jk,rm.kjur,d.tahun AS ta,d.idsmt,d.idkelas,d.k_status FROM dulang d JOIN register_mahasiswa rm ON (d.nim=rm.nim) JOIN formulir_pendaftaran fp ON (fp.no_formulir=rm.no_formulir) WHERE rm.kjur=$kjur AND d.tahun >= $ta1 AND d.tahun <= $ta2";
        $this->DB->insertRecord($str); 
        
        $str_update="UPDATE rekap_status_mahasiswa rsm JOIN (SELECT nim,kjur,tahun,idsmt,MAX(no_transaksi) AS no_transaksi FROM transaksi WHERE tahun >= $ta1 AND tahun <= $ta2 AND kjur=$kjur GROUP BY nim,kjur,tahun,idsmt) AS src ON (src.nim=rsm.nim) SET is_bayar=src.idsmt WHERE src.nim=rsm.nim AND src.idsmt=rsm.idsmt AND src.kjur=rsm.kjur";
        $this->DB->updateRecord($str_update);
        
        $this->DB->updateRecord("UPDATE rekap_status_mahasiswa SET is_bayar=1 WHERE is_bayar !=0");
         
        $this->redirect('kemahasiswaan.RekapStatusMahasiswa', true);
    }
    
	public function printOut ($sender,$param) {	
//        $this->createObj('reportfinance');
//        $this->linkOutput->Text='';
//        $this->linkOutput->NavigateUrl='#';
//        switch ($_SESSION['outputreport']) {
//            case  'summarypdf' :
//                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
//            break;
//            case  'summaryexcel' :
//                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
//            break;
//            case  'excel2007' :
//                $messageprintout="";
//                $dataReport['kjur']=$_SESSION['kjur'];
//                $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
//                $tahun=$_SESSION['ta'];                
//                $tahun_masuk=$_SESSION['tahun_masuk'];                
//                $nama_tahun = $this->DMaster->getNamaTA($tahun);
//                
//                $dataReport['ta']=$tahun;                
//                $dataReport['nama_tahun']=$nama_tahun;                
//                $dataReport['tahun_masuk']=$tahun_masuk;                
//                $dataReport['nama_tahun_masuk']=$this->DMaster->getNamaTA($tahun_masuk);
//                $dataReport['semester']=$_SESSION['currentPageRekapStatusMahasiswa']['semester'];
//                $dataReport['nama_semester']=$this->setup->getSemester($_SESSION['currentPageRekapStatusMahasiswa']['semester']);
//                
//                $dataReport['kelas']=$_SESSION['currentPageRekapStatusMahasiswa']['kelas'];
//                $dataReport['linkoutput']=$this->linkOutput;
//                $this->report->setDataReport($dataReport); 
//                $this->report->setMode($_SESSION['outputreport']);
//                
//                $this->report->printRekapPembayaranSemester($this->Finance,$this->DMaster); 
//            break;
//            case  'pdf' :
//                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
//            break;
//        }
//        $this->lblMessagePrintout->Text=$messageprintout;
//        $this->lblPrintout->Text='Rekapitulasi Pembayaran Semester Ganjil';
//        $this->modalPrintOut->show();
    }
}
?>
