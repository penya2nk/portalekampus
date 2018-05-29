<?php
prado::using ('Application.MainPageMB');
class CJadwalUjianPMB extends MainPageMB {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showJadwalUjianPMB=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageJadwalUjianPMB'])||$_SESSION['currentPageJadwalUjianPMB']['page_name']!='mb.spmb.JadwalUjianPMB') {                
				$_SESSION['currentPageJadwalUjianPMB']=array('page_name'=>'mb.spmb.JadwalUjianPMB','page_num'=>0,'search'=>false);												
			}
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            try {
                $no_formulir=$this->Pengguna->getDataUser('username');
                $this->Demik->setDataMHS(array('no_formulir'=>$no_formulir));
                if (!$this->Demik->isNoFormulirExist()) {
                    throw new Exception ('Untuk mengikuti ujian silahkan isi formulir terlebih dahulu');
                }
                $this->populateData();	
            } catch (Exception $e) {
                $this->idProcess='view';
                $this->errorMessage->Text=$e->getMessage();
            }
		}			
	}
    public function getInfoToolbar() {        
		$ta=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);
		$semester=$this->setup->getSemester($this->Pengguna->getDataUser('semester_masuk'));
		$text="Tahun Masuk $ta Semester $semester";
		return $text;
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageJadwalUjianPMB']['search']=true;
		$this->populateData($_SESSION['currentPageJadwalUjianPMB']['search']);
	}
	public function populateData() {	
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $idsmt=$_SESSION['semester'];
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');
        $str = "SELECT idjadwal_ujian FROM peserta_ujian_pmb WHERE no_formulir=$no_formulir";
        $this->DB->setFieldTable(array('idjadwal_ujian'));
        $r = $this->DB->getRecord($str);
        if (isset($r[1])) {
            $idjadwal_ujian=$r[1]['idjadwal_ujian'];
            $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE idjadwal_ujian=$idjadwal_ujian ORDER BY tanggal_ujian ASC";
            $bool_pilih=false;            
        }else{
            $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE tahun_masuk='$tahun_masuk' AND idsmt='$idsmt' AND status=1 ORDER BY tanggal_ujian ASC";        
            $bool_pilih=true;            
        }
        $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','namaruang','kapasitas','status'));
        $r = $this->DB->getRecord($str);	
        $result = array();
        while (list($k,$v)=each($r)) {  
            $idjadwal_ujian=$v['idjadwal_ujian'];
            $v['jumlah_peserta']=$this->DB->getCountRowsOfTable("peserta_ujian_pmb WHERE idjadwal_ujian=$idjadwal_ujian",'idjadwal_ujian');            
            $v['bool_pilih']=$bool_pilih;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();
         
	}
    public function pilihRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $str = "SELECT tahun_masuk,idsmt,nama_kegiatan,tanggal_akhir_daftar,status,rk.kapasitas FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE idjadwal_ujian='$id'";
        $this->DB->setFieldTable(array('tahun_masuk','idsmt','nama_kegiatan','tanggal_akhir_daftar','status','kapasitas'));
		$r = $this->DB->getRecord($str);	
        try {
            $dataujian=$r[1];
            $no_formulir=$this->Pengguna->getDataUser('no_formulir');
            if ($this->DB->checkRecordIsExist ('no_formulir',' peserta_ujian_pmb',$no_formulir)){
                throw new Exception ("No. Formulir sudah terdaftar menjadi peserta ujian PMB pada {$dataujian['nama_kegiatan']} T.A {$dataujian['tahun_masuk']}{$dataujian['idsmt']}");
            }
            if ($dataujian['status']==0){
                throw new Exception ("Tidak bisa mendaftar pada {$dataujian['nama_kegiatan']} T.A {$dataujian['tahun_masuk']}{$dataujian['idsmt']} karena statusnya sudah tutup");
            }
            $date_now=  strtotime('today');
            $tanggal_akhir_daftar = strtotime($dataujian['tanggal_akhir_daftar']);
            if ($date_now > $tanggal_akhir_daftar) {
                throw new Exception ("Tidak bisa mendaftar pada {$dataujian['nama_kegiatan']} T.A {$dataujian['tahun_masuk']}{$dataujian['idsmt']} karena tanggal pendaftaran telah berakhir");
            }
            $jumlah_peserta=$this->DB->getCountRowsOfTable("peserta_ujian_pmb WHERE idjadwal_ujian=$id",'idjadwal_ujian');            
            if ($jumlah_peserta >= $dataujian['kapasitas']){
                throw new Exception ("Tidak bisa mendaftar pada {$dataujian['nama_kegiatan']} T.A {$dataujian['tahun_masuk']}{$dataujian['idsmt']} karena kapasitas ruangan telah penuh");
            }
            
            $str = "INSERT INTO peserta_ujian_pmb SET idpeserta_ujian=NULL,no_formulir=$no_formulir,idjadwal_ujian=$id,date_added=NOW()";
            $this->DB->insertRecord($str);
            
            $this->redirect('JadwalUjianPMB',true);
        } catch (Exception $ex) {
            $this->lblHeaderMessageError->Text='Memilih Jadwal Ujian PMB';
            $this->lblContentMessageError->Text=$ex->getMessage();
            $this->modalMessageError->Show();
        }
    }   
    public function printOut ($sender,$param) {		
        $this->createObj('reportspmb');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        $id = $this->getDataKeyField($sender,$this->RepeaterS);
        
        $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE idjadwal_ujian=$id ORDER BY tanggal_ujian ASC";
        $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','namaruang','kapasitas','status'));
		$r = $this->DB->getRecord($str);
        $dataReport=$r[1];
        
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.nama_ibu_kandung,fp.idwarga,fp.nik,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,kelurahan,kecamatan,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt,pm.photo_profile FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','nama_ibu_kandung','idwarga','nik','idstatus','alamat_kantor','alamat_rumah','kelurahan','kecamatan','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt','photo_profile'));
        $r=$this->DB->getRecord($str);
        
        $dataReport['no_formulir']=$no_formulir;
        $dataReport['nama_mhs']=$r[1]['nama_mhs'];
        $dataReport['nama_ps1']=$_SESSION['daftar_jurusan'][$r[1]['kjur1']];
        $dataReport['nama_ps2']=$r[1]['kjur2'] > 0 ? $_SESSION['daftar_jurusan'][$r[1]['kjur2']] :'-';
        $dataReport['photo_profile']=BASEPATH.$r[1]['photo_profile'];
        
        $dataReport['linkoutput']=$this->linkOutput; 
        $this->report->setDataReport($dataReport); 
        $this->report->setMode('pdf');  
        
        $this->report->printKartuUjianPMB();    
        
        $this->lblMessagePrintout->Text='';
        $this->lblPrintout->Text='Kartu Ujian PMB';
        $this->modalPrintOut->show();
	}
}