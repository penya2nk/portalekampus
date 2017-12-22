<?php
prado::using ('Application.MainPageON');
class CDetailKonversiMatakuliah extends MainPageON {	
    public static $TotalSKS=0;
	public static $TotalM=0;
    public $NilaiSemesterLalu;
    public $NilaiSemesterSekarang;
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showKonversiMatakuliah=true;    
        $this->createObj('Nilai');
        $this->createObj('Finance');
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailKonversiMatakuliah'])||$_SESSION['currentPageDetailKonversiMatakuliah']['page_name']!='m.spmb.DetailKonversiMatakuliah') {
				$_SESSION['currentPageDetailKonversiMatakuliah']=array('page_name'=>'m.spmb.DetailKonversiMatakuliah','page_num'=>0,'search'=>false,'DataKonversi'=>array());												                                               
			}  
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
			$this->populateData();	
		}
	}
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }
	protected function populateData() {		
        try {
            $iddata_konversi=addslashes($this->request['id']);  
            $str = "SELECT nim FROM data_konversi WHERE iddata_konversi=$iddata_konversi";
            $this->DB->setFieldTable(array('nim'));
            $r = $this->DB->getRecord($str);
            $this->txtAddNIM->Text=isset($r[1])?$r[1]['nim']:'';
            
            $str = "SELECT dk.iddata_konversi,dk.nama,dk.alamat,dk.no_telp,dk.nim_asal,dk.kode_pt_asal,dk.nama_pt_asal,js.njenjang,dk.kode_ps_asal,dk.nama_ps_asal,dk.tahun,dk.kjur,dk.idkur,date_added FROM data_konversi2 dk,jenjang_studi js WHERE dk.kjenjang=js.kjenjang AND dk.iddata_konversi=$iddata_konversi";
            $this->DB->setFieldTable(array('iddata_konversi','nama','alamat','no_telp','nim_asal','kode_pt_asal','nama_pt_asal','njenjang','kode_ps_asal','nama_ps_asal','tahun','kjur','idkur','date_added'));
            $r = $this->DB->getRecord($str);			
            $dataView=$r[1];	
            $dataView['jumlahmatkul']=$this->DB->getCountRowsOfTable("nilai_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $dataView['jumlahsks']=$this->DB->getSumRowsOfTable('sks',"v_konversi2 WHERE iddata_konversi=$iddata_konversi");
            if (!isset($r[1])) {
                $_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']=array();
                throw new Exception("Data Konversi dengan ID ($iddata_konversi) tidak terdaftar.");
            }
            $_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']=$dataView;
            $this->Nilai->setDataMHS($dataView);
            $nilai=$this->Nilai->getNilaiKonversi($iddata_konversi,$dataView['idkur']);		
            $this->RepeaterS->dataSource=$nilai;
            $this->RepeaterS->dataBind();            	            
        } catch (Exception $ex) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$ex->getMessage();
        }        
	}  
    public function checkNIM ($sender,$param) {					
		$nim=$param->Value;		
        if ($nim != '') {
            try {   
                $str = "SELECT nama_mhs,k_status,kjur FROM v_datamhs WHERE nim='$nim'";
                $this->DB->setFieldTable(array('nama_mhs','k_status','kjur'));
                $r = $this->DB->getRecord($str);
                if (!isset($r[1])) {  
                    throw new Exception ("NIM ($nim) tidak terdaftar di Portal, silahkan ganti dengan yang lain.");		
                }
                if ($r[1]['k_status']=='L') {
                    throw new Exception ("Tidak bisa dihubungkan, karena status ($nim) sudah lulus.");
                }
                $kjur=$this->Pengguna->getDataUser('kjur');
                if ($kjur > 0) {
                    $kjur_mhs=$r[1]['kjur'];
                    if ($kjur != $kjur_mhs){
                        throw new Exception ("Anda tidak berhak mengakses data mahasiswa dengan NIM ($nim).");		
                    } 
                }
				if ($this->DB->checkRecordIsExist('nim','data_konversi',$nim)) {
                    throw new Exception ("Data Konversi ini tidak bisa dihubungkan dengan NIM ($nim) karena NIM ini sudah terhubung dengan yang lain.");
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
	}
    public function getInfoMHS ($sender,$param) {		
		if ($this->IsValid) {
			$nim=$this->txtAddNIM->Text;
            $str = "SELECT vdm.nim,vdm.nama_mhs,sm.n_status AS status FROM v_datamhs vdm LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) WHERE vdm.nim='$nim'";
            $this->DB->setFieldTable(array('nim','nama_mhs','status'));
            $r=$this->DB->getRecord($str);
            $datamhs=$r[1];
            
            $pindahan=$this->Nilai->isMhsPindahan($nim,true);
            $ulr_profil=$this->constructUrl('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$datamhs['nim']));
            $url='<a href="'.$ulr_profil.'" style="color:#fff">'.$nim.'</a> ';
            $str_pindahan = $pindahan == 0? '' :'<span class="label label-warning">Pindahan</span>';
            $this->labelNIM->Text=$url.$str_pindahan;
            $this->labelNamaMHS->Text=$datamhs['nama_mhs'];
            $this->labelStatusMSH->Text=$datamhs['status'];            
			$this->modalInfoMHS->show();
        }
    }
    public function linkingData ($sender,$param) {		
		if ($this->IsValid) {		
			$iddata_konversi=$_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']['iddata_konversi'];
			$nim=$this->txtAddNIM->Text;
			$str = "INSERT INTO data_konversi (idkonversi,iddata_konversi,nim) VALUES (NULL,$iddata_konversi,'$nim')";
			$this->DB->insertRecord($str);
			$this->redirect('DetailKonversiMatakuliah',true,array('id'=>$iddata_konversi));
        }
    }
    public function unlinkData ($sender,$param) {		
		if ($this->IsValid) {		
			$iddata_konversi=$_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']['iddata_konversi'];
			$this->DB->deleteRecord("data_konversi WHERE iddata_konversi=$iddata_konversi");
			$this->redirect('DetailKonversiMatakuliah',true,array('id'=>$iddata_konversi));
        }
    }
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        $dataReport=$_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi'];                
        $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$dataReport['kjur']];
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :                
                $messageprintout='Hasil konversi matakuliah :';                
                
                $kaprodi=$this->Nilai->getKetuaPRODI($dataReport['kjur']);
                $dataReport['nama_kaprodi']=$kaprodi['nama_dosen'];
                $dataReport['jabfung_kaprodi']=$kaprodi['nama_jabatan'];
                $dataReport['nidn_kaprodi']=$kaprodi['nidn'];
                
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                $this->report->printKonversiMatakuliah($this->Nilai);
            break;
            case  'pdf' :                
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text="Konversi Matakuliah";
        $this->modalPrintOut->show();
	}
}