<?php
prado::using ('Application.MainPageM');
class CJadwalUjianPMB extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuSPMBUjianPMB=true;
        $this->showJadwalUjianPMB=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageJadwalUjianPMB'])||$_SESSION['currentPageJadwalUjianPMB']['page_name']!='m.perkuliahan.JadwalUjianPMB') {                
				$_SESSION['currentPageJadwalUjianPMB']=array('page_name'=>'m.perkuliahan.JadwalUjianPMB','page_num'=>0,'search'=>false);												
			}
            $_SESSION['currentPageJadwalUjianPMB']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
                        
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_pendaftaran'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $_SESSION['semester']=1;
            $idsmt=$_SESSION['semester'];
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemesterMasuk->DataSource=$semester;
			$this->tbCmbSemesterMasuk->Text=$idsmt;
			$this->tbCmbSemesterMasuk->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();		
            
		}			
	}
    public function changeTbTahunMasuk ($sender,$param) {
		$_SESSION['tahun_pendaftaran']=$this->tbCmbTahunMasuk->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageJadwalUjianPMB']['search']);
	}	
	public function changeTbSemesterMasuk ($sender,$param) {
		$_SESSION['semester']=1;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageJadwalUjianPMB']['search']);
	}
    public function getInfoToolbar() {        
		$ta=$this->DMaster->getNamaTA($_SESSION['tahun_pendaftaran']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Tahun Masuk $ta Semester $semester";
		return $text;
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageJadwalUjianPMB']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageJadwalUjianPMB']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageJadwalUjianPMB']['search']=true;
		$this->populateData($_SESSION['currentPageJadwalUjianPMB']['search']);
	}
	public function populateData($search=false) {	
        $tahun_masuk=$_SESSION['tahun_pendaftaran'];
        $idsmt=$_SESSION['semester'];
        $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE tahun_masuk='$tahun_masuk' AND idsmt='$idsmt' ORDER BY tanggal_ujian ASC";
        
        $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','namaruang','kapasitas','status'));
		$r = $this->DB->getRecord($str);	
        $result = array();
        while (list($k,$v)=each($r)) {  
            $idjadwal_ujian=$v['idjadwal_ujian'];
            $v['jumlah_peserta']=$this->DB->getCountRowsOfTable("peserta_ujian_pmb WHERE idjadwal_ujian=$idjadwal_ujian",'idjadwal_ujian');
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
	}
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        $this->hiddentahunmasuk->Value=$_SESSION['tahun_pendaftaran'];
         //load kelas 				
        $this->cmbAddRuang->DataSource=$this->DMaster->getRuangKelas();
        $this->cmbAddRuang->dataBind();
    }
	public function saveData ($sender,$param) {
        if ($this->IsValid) {
            $tahun_masuk=$this->hiddentahunmasuk->Value;
            $semester=1;
            $nama_kegiatan=addslashes($this->txtAddNamaKegiatan->Text);
            $tgl_ujian=date ('Y-m-d',$this->txtAddTanggalUjian->TimeStamp);
            $jam_masuk=addslashes($this->txtAddJamMasuk->Text);
            $jam_keluar=addslashes($this->txtAddJamKeluar->Text);
            $tgl_akhir_pendaftaran=date ('Y-m-d',$this->txtAddTanggalAkhirDaftar->TimeStamp);
            $ruangkelas=$this->cmbAddRuang->Text;            
            $str = "INSERT INTO jadwal_ujian_pmb SET idjadwal_ujian=NULL,tahun_masuk=$tahun_masuk,idsmt=$semester,nama_kegiatan='$nama_kegiatan',tanggal_ujian='$tgl_ujian',jam_mulai='$jam_masuk',jam_akhir='$jam_keluar',tanggal_akhir_daftar='$tgl_akhir_pendaftaran',idruangkelas='$ruangkelas',date_added=NOW(),date_modified=NOW(),status=1";
            $this->DB->insertRecord($str);
            
            $this->redirect('spmb.JadwalUjianPMB', true);
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;        
        
        $str = "SELECT nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,idruangkelas,status FROM jadwal_ujian_pmb WHERE idjadwal_ujian=$id";
        $this->DB->setFieldTable(array('nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','status'));
        $r = $this->DB->getRecord($str);
        
        $this->txtEditNamaKegiatan->Text=$r[1]['nama_kegiatan'];
        $this->txtEditTanggalUjian->Text=$this->TGL->tanggal('d-m-Y',$r[1]['tanggal_ujian']);
        $this->txtEditJamMasuk->Text=$r[1]['jam_mulai'];
        $this->txtEditJamKeluar->Text=$r[1]['jam_akhir'];
        $this->txtEditTanggalAkhirDaftar->Text=$this->TGL->tanggal('d-m-Y',$r[1]['tanggal_akhir_daftar']);
        $this->cmbEditRuang->DataSource=$this->DMaster->getRuangKelas();
        $this->cmbEditRuang->dataBind();
        $this->cmbEditRuang->Text=$r[1]['idruangkelas'];        
        $this->cmbEditStatus->Text=$r[1]['status'];
    }
    public function updateData ($sender,$param) {
        if ($this->IsValid) {
            $id=$this->hiddenid->Value;
            $nama_kegiatan=addslashes($this->txtEditNamaKegiatan->Text);
            $tgl_ujian=date ('Y-m-d',$this->txtEditTanggalUjian->TimeStamp);
            $jam_masuk=addslashes($this->txtEditJamMasuk->Text);
            $jam_keluar=addslashes($this->txtEditJamKeluar->Text);
            $tgl_akhir_pendaftaran=date ('Y-m-d',$this->txtEditTanggalAkhirDaftar->TimeStamp);
            $ruangkelas=$this->cmbEditRuang->Text;
            $status=$this->cmbEditStatus->Text;            
            $str = "UPDATE jadwal_ujian_pmb SET nama_kegiatan='$nama_kegiatan',tanggal_ujian='$tgl_ujian',jam_mulai='$jam_masuk',jam_akhir='$jam_keluar',tanggal_akhir_daftar='$tgl_akhir_pendaftaran',idruangkelas='$ruangkelas',date_modified=NOW(),status='$status' WHERE idjadwal_ujian=$id";
            $this->DB->updateRecord($str);
            
            $this->redirect('spmb.JadwalUjianPMB', true);
        }
    }
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist ('idjadwal_ujian','peserta_ujian_pmb',$id)) {
            $this->lblHeaderMessageError->Text='Menghapus Jadwal Ujian PMB';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus jadwal ujian dengan ID ($id) karena masih ada pesertanya.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("jadwal_ujian_pmb WHERE idjadwal_ujian='$id'");
            $this->redirect('spmb.JadwalUjianPMB',true);
        }
    }   
    public function printOut ($sender,$param) {		
        $idjadwal_ujian=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->createObj('reportspmb');
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
                $messageprintout="Mohon maaf Print out pada mode excel belum kami support.";
            break;
            case  'pdf' :                
                $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,rk.namaruang,rk.kapasitas,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE idjadwal_ujian=$idjadwal_ujian ORDER BY tanggal_ujian ASC";        
                $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','namaruang','kapasitas','status'));
                $r = $this->DB->getRecord($str);
                $dataReport=$r[1];        
                $jumlah_peserta=$this->DB->getCountRowsOfTable ("peserta_ujian_pmb pum,formulir_pendaftaran fp,pin WHERE fp.no_formulir=pum.no_formulir AND pin.no_formulir=pum.no_formulir AND pum.idjadwal_ujian=$idjadwal_ujian",'pum.no_formulir');

                $dataReport['nama_tahun']=$this->DMaster->getNamaTA($dataReport['tahun_pendaftaran']);
                $dataReport['jumlah_peserta']=$jumlah_peserta;
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);  
                
                $messageprintout="Berita Acara Ujian SPMB : <br/>";
                $this->report->printBeritaAcaraUjianSPMB($this->DMaster);
            break;
        }                
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Berita Acara Ujian SPMB';
        $this->modalPrintOut->show();
	}
}