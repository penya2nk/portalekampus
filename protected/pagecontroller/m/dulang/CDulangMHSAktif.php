<?php
prado::using ('Application.MainPageM');
class CDulangMHSAktif Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSAktif=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSAktif'])||$_SESSION['currentPageDulangMHSAktif']['page_name']!='m.dulang.DulangMHSAktif') {
				$_SESSION['currentPageDulangMHSAktif']=array('page_name'=>'m.dulang.DulangMHSAktif','page_num'=>0,'search'=>false,'tahun_masuk'=>$_SESSION['tahun_masuk'],'iddosen_wali'=>'none','DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSAktif']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	

            $tahun_masuk=$this->getAngkatan ();			            
            $this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
            $this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSAktif']['tahun_masuk'];						
            $this->tbCmbTahunMasuk->dataBind();

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

            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();

            $this->populateData();
            $this->setInfoToolbar();
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
    }
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$tahunmasuk=$_SESSION['currentPageDulangMHSAktif']['tahun_masuk'] == 'none'?'':'Tahun Masuk '.$this->DMaster->getNamaTA($_SESSION['currentPageDulangMHSAktif']['tahun_masuk']);		        
		$this->lblModulHeader->Text="Program Studi $ps T.A $ta Semester $semester $tahunmasuk";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDulangMHSAktif']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDulangMHSAktif']['search']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;		        
		$_SESSION['currentPageDulangMHSAktif']['tahun_masuk']=$_SESSION['ta'];
		$this->tbCmbTahunMasuk->DataSource=$this->getAngkatan();
		$this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSAktif']['tahun_masuk'];
		$this->tbCmbTahunMasuk->dataBind();		
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['currentPageDulangMHSAktif']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function searchRecord ($sender,$param){
        $_SESSION['currentPageDulangMHSAktif']['search']=true;
        $this->populateData($_SESSION['currentPageDulangMHSAktif']['search']);
    }
    public function populateData($search=false) {
        $ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];
		$tahun_masuk=$_SESSION['currentPageDulangMHSAktif']['tahun_masuk'];
        $iddosen_wali=$_SESSION['currentPageDulangMHSAktif']['iddosen_wali'];
        $str_dw = $iddosen_wali=='none'?'':" AND vdm.iddosen_wali=$iddosen_wali";
        $str_tahun_masuk=$tahun_masuk=='none'?'':" AND vdm.tahun_masuk=$tahun_masuk";      
        if ($search) {
            $str = "SELECT d.iddulang,vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal,d.tahun,d.idsmt FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' AND vdm.kjur='$kjur'";
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {
                case 'no_formulir' :
                    $clausa="AND vdm.no_formulir='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nim' :
                    $clausa="AND d.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                            
            $str = "SELECT d.iddulang,vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal,d.tahun,d.idsmt FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' AND d.k_status='A' $str_dw $str_tahun_masuk";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' $str_dw $str_tahun_masuk",'vdm.nim');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDulangMHSAktif']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDulangMHSAktif']['page_num']=0;}
		$str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('iddulang','no_formulir','nim','nirm','nama_mhs','iddosen_wali','tanggal','tahun','idsmt'));
		$result=$this->DB->getRecord($str,$offset+1);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function cekNIM ($sender,$param) {		
        $nim=addslashes($param->Value);		
        if ($nim != '') {
            try {
                if (!isset($_SESSION['currentPageDulangMHSAktif']['DataMHS']['no_formulir'])) {
                    
                    $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                    $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                    $r=$this->DB->getRecord($str);	           
                    if (!isset($r[1])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                    }
                    $datamhs=$r[1];  
                    $datamhs['idsmt']=$_SESSION['semester'];
                    $datamhs['ta']=$_SESSION['ta'];
                    
                    $this->Finance->setDataMHS($datamhs);                              
                    $datadulang=$this->Finance->getDataDulang($_SESSION['semester'],$_SESSION['ta']);
                    
                    if (isset($datadulang['iddulang'])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) telah daftar ulang di T.A dan Semester ini.");
                    }
                    $data=$this->Finance->getTresholdPembayaran($_SESSION['ta'],$_SESSION['semester'],true);						                                
                    if (!$data['bool']) {
                        throw new Exception ("Mahasiswa a.n ".$this->Finance->getDataMHS('nama_mhs')."($nim) tidak bisa daftar ulang karena baru membayar(".$this->Finance->toRupiah($data['total_bayar'])."), harus minimal setengahnya sebesar (".$this->Finance->toRupiah($data['ambang_pembayaran']).") dari total (".$this->Finance->toRupiah($data['total_biaya']).")");
                    }                    
                    $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                    $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
                    $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                    $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
                    $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
                    $_SESSION['currentPageDulangMHSAktif']['DataMHS']=$datamhs;
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function Go($param,$sender) {	
        if ($this->Page->isValid) {            
            $nim=addslashes($this->txtNIM->Text);
            $this->redirect('dulang.DetailDulangMHSAktif',true,array('id'=>$nim));
        }
	}
    public function viewRecord($sender,$param) {	
		$this->idProcess='view';		
		$iddulang=$this->getDataKeyField($sender,$this->RepeaterS);	
        $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,semester_masuk,iddosen_wali,d.idkelas,d.k_status,d.idsmt,d.tahun FROM v_datamhs vdm JOIN dulang d ON (d.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE d.iddulang='$iddulang'";
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status','idsmt','tahun'));
        $r=$this->DB->getRecord($str);	           
        $datamhs=$r[1];
        $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
        $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
        $this->hiddenidsmt->Value=$datamhs['idsmt'];
        $this->hiddenta->Value=$datamhs['tahun'];
        $this->Demik->setDataMHS($datamhs);
	}
    public function deleteRecord ($sender,$param) {			
		$nim=$sender->CommandParameter;		
		$idsmt=$this->hiddenidsmt->Value;
		$ta=$this->hiddenta->Value;
		$this->DB->query ('BEGIN');
        $this->Demik->setDataMHS(array('nim'=>$nim));
        $datadulang=$this->Demik->getDataDulang($idsmt,$ta);
        $k_status=$datadulang['status_sebelumnya'];
        $str = "UPDATE register_mahasiswa SET k_status='$k_status' WHERE nim='$nim'";
		if ($this->DB->updateRecord($str)) {
            $this->DB->deleteRecord("dulang WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'");
			$this->DB->deleteRecord("krs WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'");	
            $this->DB->deleteRecord("transkrip_asli WHERE nim='$nim'");
            $this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'");	
			$this->DB->query ('COMMIT');
            $this->redirect('dulang.DulangMHSAktif',true);
		}else {
			$this->DB->query ('ROLLBACK');
		}		
	}
    public function printOut ($sender,$param) {		
        $this->createObj('reportakademik');
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
                $messageprintout="Daftar Mahasiswa Daftar Ulang Status AKTIF: <br/>";
                $dataReport['ta']=$_SESSION['ta'];
                $dataReport['nama_tahun']=$this->DMaster->getNamaTA($dataReport['ta']);
                $dataReport['idsmt']=$_SESSION['semester'];
                $dataReport['nama_semester']=$this->setup->getSemester($_SESSION['semester']);
                $dataReport['kjur']=$_SESSION['kjur'];
                $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
                $dataReport['linkoutput']=$this->linkOutput;                
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                
                $this->report->printDulangAKTIF($this->DMaster);
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
            break;
        } 
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Ulang Mahasiswa AKTIF';
        $this->modalPrintOut->show();
    }
}