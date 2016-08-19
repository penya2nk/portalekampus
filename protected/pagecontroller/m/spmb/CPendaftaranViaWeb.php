<?php
prado::using ('Application.MainPageM');
class CPendaftaranViaWeb extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showSubMenuSPMBPendaftaran=true;
		$this->showPendaftaranViaWeb=true;            
                
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePendaftaranWeb'])||$_SESSION['currentPagePendaftaranWeb']['page_name']!='m.spmb.PendaftaranWeb') {
				$_SESSION['currentPagePendaftaranWeb']=array('page_name'=>'m.spmb.PendaftaranWeb','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false,'status_dulang'=>'none');												
			}
            $_SESSION['currentPagePendaftaranWeb']['search']=false;
            $this->cmbDaftarUlang->Text=$_SESSION['currentPagePendaftaranWeb']['status_dulang'];
            
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            
            $daftar_prodi=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');            
			$this->tbCmbPs->DataSource=$daftar_prodi;
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
                        
            $this->tbCmbSemester->DataSource=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');
            $this->tbCmbSemester->Text=$_SESSION['semester'];
            $this->tbCmbSemester->DataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            $this->populateData ();	

		}	
	}
	
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}    
	public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps Tahun Masuk $tahunmasuk Semester $semester";
		return $text;
	}
	public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePendaftaranWeb']['search']=true;
		$this->populateData($_SESSION['currentPagePendaftaranWeb']['search']);
	}
    public function changeStatusDulang ($sender,$param) {
        $_SESSION['currentPagePendaftaranWeb']['status_dulang']=$this->cmbDaftarUlang->Text;
        $this->populateData($_SESSION['currentPagePendaftaranWeb']['search']);
    }
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePendaftaranWeb']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePendaftaranWeb']['search']);
	}		
	public function populateData ($search=false) {	
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $semester=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];
        if ($search) {            
            $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.jk,fp.alamat_rumah,fp.telp_hp,nomor_ijazah,IF(char_length(COALESCE(rm.nim,''))>0,'dulang','-') AS nim FROM formulir_pendaftaran fp JOIN bipend bp ON (fp.no_formulir=bp.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=fp.no_formulir)";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'no_formulir' :
                    $cluasa=" fp.no_formulir='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("formulir_pendaftaran fp,bipend bp WHERE fp.no_formulir=bp.no_formulir AND $cluasa",'fp.no_formulir');
                    $str = "$str WHERE $cluasa";
                break;
                case 'nama_mhs' :
                    $cluasa=" fp.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("formulir_pendaftaran fp,bipend bp WHERE fp.no_formulir=bp.no_formulir AND $cluasa",'fp.no_formulir');
                    $str = "$str WHERE $cluasa";
                break;
            }
        }else{            
            if ($_SESSION['currentPagePendaftaranWeb']['status_dulang'] == 'belum') {
                $str_status= " AND rm.nim IS NULL";
            }elseif ($_SESSION['currentPagePendaftaranWeb']['status_dulang'] == 'sudah') {
                $str_status= " AND rm.nim IS NOT NULL";
            }
            $jumlah_baris=$this->DB->getCountRowsOfTable("formulir_pendaftaran fp JOIN bipend bp ON (fp.no_formulir=bp.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=fp.no_formulir) WHERE fp.ta='$tahun_masuk' AND fp.idsmt='$semester' AND fp.daftar_via='WEB' AND (fp.kjur1='$kjur' OR fp.kjur2='$kjur')$str_status",'fp.no_formulir');
            $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.jk,fp.alamat_rumah,fp.telp_hp,nomor_ijazah,IF(char_length(COALESCE(rm.nim,''))>0,'dulang','-') AS ket,rm.nim FROM formulir_pendaftaran fp JOIN bipend bp ON (fp.no_formulir=bp.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=fp.no_formulir) WHERE fp.ta='$tahun_masuk' AND fp.idsmt='$semester' AND fp.daftar_via='WEB' AND (fp.kjur1='$kjur' OR fp.kjur2='$kjur')$str_status";
        }	
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePendaftaranWeb']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePendaftaranWeb']['page_num']=0;}
		$str = $str . " ORDER BY fp.nama_mhs ASC LIMIT $offset,$limit";				
        $_SESSION['currentPagePendaftaranWeb']['offset']=$offset;
        $_SESSION['currentPagePendaftaranWeb']['limit']=$limit;
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','jk','alamat_rumah','telp_hp','nomor_ijazah','ket','nim'));				
		$r = $this->DB->getRecord($str,$offset+1);
        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();	
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS); 
	}
	
	public function itemCreated ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {			
			$nama_mhs=$item->DataItem['nama_mhs'];
			$item->btnDelete->Attributes->Title="Hapus $nama_mhs";               
            if ($item->DataItem['ket'] == 'dulang') {
                $nim=$item->DataItem['nim'];
                $item->lblKeterangan->CssClass='label label-success';
                $item->lblKeterangan->Text="Sudah [$nim]";
				$item->btnDelete->Enabled=false;
				$item->btnDelete->Attributes->OnClick="alert('Tidak bisa dihapus karena sudah daftar ulang');return false;";			
			}else{
                $item->lblKeterangan->CssClass='label label-danger';
                $item->lblKeterangan->Text='Belum';
            }
		}
	}	
	public function updateData ($sender,$param) {
		if ($this->IsValid) {
			$this->Pengguna->updateActivity();	
			$no_formulir=$this->txtEditNoFormulir->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtEditNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtEditTempatLahir->Text));						
			$tgl_lahir=$this->TGL->tukarTanggal ($this->txtEditTanggalLahir->Text);
			$jk=$this->cmbEditJK->Text;
			$idwarga=$this->editWNI->Checked===true?'WNI':'WNA';
			$idstatus=$this->edittidak_bekerja->Checked===true?'TIDAK_BEKERJA':'PEKERJA';
			$alamat_kantor=strtoupper(trim($this->txtEditAlamatKantor->Text));						
			$alamat_rumah=strtoupper(trim($this->txtEditAlamatRumah->Text));	
			$telp_kantor=trim($this->txtEditNoKantor->Text);
			$telp_rumah=trim($this->txtEditNoTelpRumah->Text);		
			$telp_hp=trim($this->txtEditNoHP->Text);
			$pendidikan_terakhir=strtoupper(trim($this->txtEditPendidikanTerakhir->Text));
			$jurusan=strtoupper(trim($this->txtEditJurusan->Text));	
			$kota=strtoupper(trim($this->txtEditKota->Text));	
			$provinsi=strtoupper(trim($this->txtEditProvinsi->Text));	
			$tahun_pa=strtoupper(trim($this->txtEditTahun->Text));		
			$asal_slta=strtoupper(trim($this->txtEditAsalSlta->Text));			
			$nomor_ijazah=trim($this->txtEditNoIjazah->Text);
			if ($this->txtAddWaktuMendaftar->Value == '0000-00-00 00:00:00') {
				$waktu_mendaftar=date('Y-m-d H:m:s');
				$waktu_mendaftar=",waktu_mendaftar='$waktu_mendaftar'";
			}
			$str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=".$this->cmbEditAgama->Text.",idwarga='$idwarga',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=".$this->cmbEditPekerjaanOrtu->Text.",pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='".$this->cmbEditJenisSlta->Text."',asal_slta='$asal_slta',status_slta='".$this->cmbEditStatusSlta->Text."',nomor_ijazah='$nomor_ijazah',kjur1=".$this->cmbEditKjur1->Text.",kjur2='".$this->cmbEditKjur2->Text."'$waktu_mendaftar,ta='".$this->cmbEditTa->Text."',idsmt='".$this->cmbEditSemester->Text."',idkelas='".$this->cmbEditKelas->Text."' WHERE no_formulir='$no_formulir'";			
			$this->DB->updateRecord($str);
			$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
		}else {
			$this->idProcess='edit';
		}
	}
	public function saveData ($sender,$param) {
		if ($this->IsValid) {
			$this->Pengguna->updateActivity();	
			$this->idProcess='add';
			$no_formulir=$_SESSION['addProcess'];
			$nama_mhs=addslashes(strtoupper(trim($this->txtAddNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtAddTempatLahir->Text));						
			$tgl_lahir=$this->TGL->tukarTanggal ($this->txtAddTanggalLahir->Text);
			$jk=$this->cmbAddJK->Text;
			$idwarga=$this->WNI->Checked===true?'WNI':'WNA';
			$idstatus=$this->tidak_bekerja->Checked===true?'TIDAK_BEKERJA':'PEKERJA';
			$alamat_kantor=strtoupper(trim($this->txtAddAlamatKantor->Text));						
			$alamat_rumah=strtoupper(trim($this->txtAddAlamatRumah->Text));	
			$telp_kantor=trim($this->txtAddNoKantor->Text);
			$telp_rumah=trim($this->txtAddNoTelpRumah->Text);		
			$telp_hp=trim($this->txtAddNoHP->Text);
			$pendidikan_terakhir=strtoupper(trim($this->txtAddPendidikanTerakhir->Text));
			$jurusan=strtoupper(trim($this->txtAddJurusan->Text));	
			$kota=strtoupper(trim($this->txtAddKota->Text));	
			$provinsi=strtoupper(trim($this->txtAddProvinsi->Text));	
			$tahun_pa=strtoupper(trim($this->txtAddTahun->Text));		
			$asal_slta=strtoupper(trim($this->txtAddAsalSlta->Text));			
			$nomor_ijazah=trim($this->txtAddNoIjazah->Text);
			if ($this->txtAddWaktuMendaftar->Value == '0000-00-00 00:00:00') {
				$waktu_mendaftar=date('Y-m-d H:m:s');
				$waktu_mendaftar=",waktu_mendaftar='$waktu_mendaftar'";
			}
			$str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=".$this->cmbAddAgama->Text.",idwarga='$idwarga',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=".$this->cmbAddPekerjaanOrtu->Text.",pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='".$this->cmbAddJenisSlta->Text."',asal_slta='$asal_slta',status_slta='".$this->cmbAddStatusSlta->Text."',nomor_ijazah='$nomor_ijazah',kjur1=".$this->cmbAddKjur1->Text.",kjur2='".$this->cmbAddKjur2->Text."'$waktu_mendaftar,ta='".$this->cmbAddTa->Text."',idkelas='".$this->cmbAddKelas->Text."' WHERE no_formulir='$no_formulir'";
			unset($_SESSION['addProcess']);
			$this->DB->updateRecord($str);
			$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
			
		}else {
			$this->idProcess='add';
		}
	}
	
	public function ubahRecord($sender,$param) {
		$this->Pengguna->updateActivity();	
		$this->idProcess='edit';
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->txtEditNoFormulir->Text = $no_formulir;		
		$this->spmb->setDataMode('complete');		
		$this->spmb->setNoFormulir($no_formulir,true);		

		$this->txtEditNoFormulir->Text = $no_formulir;
		$this->txtEditNamaMhs->Text = $this->spmb->dataMhs['nama_mhs'];;
		$this->txtEditTempatLahir->Text = $this->spmb->dataMhs['tempat_lahir'];;
		$this->txtEditTanggalLahir->Date=$this->TGL->tukarTanggal($this->spmb->dataMhs['tanggal_lahir'],'entoid');
		$this->cmbEditJK->Text=$this->spmb->dataMhs['jk'];
		$this->WNI->Checked=($this->spmb->dataMhs['idwarga']=='WNI')?true:false;		
		$dmaster=$this->getLogic('DMaster');
		$this->cmbEditAgama->DataSource=$dmaster->getListAgama();
		$this->cmbEditAgama->Text=$this->spmb->dataMhs['idagama'];
		$this->cmbEditAgama->dataBind();		
		$this->txtEditAlamatRumah->Text = $this->spmb->dataMhs['alamat_rumah'];
		$this->txtEditNoTelpRumah->Text = $this->spmb->dataMhs['telp_rumah'];
		$this->txtEditNoHP->Text = $this->spmb->dataMhs['telp_hp'];
		$this->cmbEditPekerjaanOrtu->DataSource=$dmaster->getJenisPekerjaan ();
		$this->cmbEditPekerjaanOrtu->dataBind();			
		if ($this->spmb->dataMhs['idstatus']=='PEKERJA') {
			$this->editpekerja->Checked=true;
			$this->txtEditAlamatKantor->Enabled=true;
			$this->txtEditNoKantor->Enabled=true;
			$this->txtEditAlamatKantor->Text=$this->spmb->dataMhs['alamat_kantor'];
			$this->txtEditNoKantor->Text=$this->spmb->dataMhs['telp_kantor'];
		}else {
			$this->edittidak_bekerja->Checked=true;
		}
		$this->cmbEditPekerjaanOrtu->Text=$this->spmb->dataMhs['idjp'];
		$this->txtEditPendidikanTerakhir->Text=$this->spmb->dataMhs['pendidikan_terakhir'];
		$this->txtEditJurusan->Text=$this->spmb->dataMhs['jurusan'];
		$this->txtEditKota->Text=$this->spmb->dataMhs['kota'];
		$this->txtEditProvinsi->Text=$this->spmb->dataMhs['provinsi'];
		$this->txtEditTahun->Text=$this->spmb->dataMhs['tahun_pa'];
							
		$this->cmbEditJenisSlta->Text=$this->spmb->dataMhs['jenis_slta'];
		if ($this->cmbEditJenisSlta->Text != '') {
			$this->txtEditAsalSlta->Enabled=true;
		}
		$this->txtEditAsalSlta->Text=$this->spmb->dataMhs['asal_slta'];
		$this->cmbEditStatusSlta->Text=$this->spmb->dataMhs['status_slta'];
		$this->txtEditNoIjazah->Text=$this->spmb->dataMhs['nomor_ijazah'];
		$this->cmbEditKjur1->DataSource=$_SESSION['daftar_jurusan'];
		$this->cmbEditKjur1->dataBind();
		$this->cmbEditKjur2->DataSource=$this->getLogic('Jurusan')->removeKJur($_SESSION['daftar_jurusan'],$this->spmb->dataMhs['kjur1']);
		$this->cmbEditKjur2->dataBind();				
		$this->cmbEditKjur1->Text=$this->spmb->dataMhs['kjur1'];
		$this->cmbEditKjur2->Text=$this->spmb->dataMhs['kjur2'];
		$this->cmbEditKelas->DataSource=$this->spmb->removeNone($_SESSION['daftar_kelas']);
		$this->cmbEditKelas->Text=$this->spmb->dataMhs['idkelas'];
		$this->cmbEditKelas->dataBind();
		$this->cmbEditTa->DataSource=$this->spmb->removeNone($_SESSION['tahun_akademik']);
		$this->cmbEditTa->Text=$this->spmb->dataMhs['ta'];
		$this->cmbEditTa->dataBind();
		$this->cmbEditSemester->DataSource=$this->spmb->removeNone($_SESSION['daftar_semester']);
		$this->cmbEditSemester->Text=$this->spmb->dataMhs['idsmt'];
		$this->cmbEditSemester->dataBind();
		$this->spmb->setParameterGlobal ('','',$_SESSION['kjur']);
		if ($this->spmb->isMhsRegistered()) {
			$this->cmbEditKelas->Enabled=false;
			$this->cmbEditTa->Enabled=false;
			$this->cmbEditSemester->Enabled=false;
			$this->cmbEditKjur1->Enabled=false;
			$this->cmbEditKjur2->Enabled=false;
		}			
		
	}
	
	public function deleteRecord($sender,$param) {
		$this->Pengguna->updateActivity();	
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
		$str = "formulir_pendaftaran WHERE no_formulir='$no_formulir'";
		if ($this->DB->deleteRecord($str) ) {
			$this->DB->deleteRecord ("transaksi WHERE no_formulir='$no_formulir'");
			$this->DB->query ('COMMIT');
		}else {
			$this->DB->query ('ROLLBACK');
		}		
		$this->Themes->setMode('mhs');
		$this->Themes->hapusPhoto($no_formulir.'.jpg');
		$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
	}
	
	public function checkFormulir ($sender,$param) {
		$error='';
		if ($this->IsValid) {			
			try {
				$no_formulir=trim($this->txtNoFormulir->Text);			
				$this->checkFormulir2($no_formulir,$_SESSION['tahun_masuk']);			
				$_SESSION['addProcess']=$no_formulir;
				$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');									
			}catch (Exception $e) {
				$this->errorMessage->Text=$e->getMessage();
			}			
		}		
	}

	public function viewRecord ($sender,$param) {
		$this->Pengguna->updateActivity();	
		$this->idProcess = 'view';
		$this->disableToolbars();
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->txtPhotoNoFormulir->Value=$no_formulir;		
		$this->spmb->setDataMode('complete');
		$this->spmb->setNoFormulir($no_formulir,true);	        
		$_SESSION['currentPagePendaftaranWeb']['dataMhs']=$this->spmb->dataMhs;		
	}
	
	public function doesFileExist ($sender,$param) {
		$retval=false;
		$uploadFileName=$this->FileUpload->getFileName();
		if ($uploadFileName) {
			$fileType=$this->FileUpload->getFileType();			
			if ($fileType=='image/jpeg') {
				$retval=true;
			}else {
				$sender->ErrorMessage='Hanya menerima tipe file .jpg';
			}
		}
		$param->IsValid=$retval;
	}
	public function unggahPhoto ($sender,$param) {
		if ($this->IsValid) {
			$this->Pengguna->updateActivity();	
			$this->idProcess='view';
			$no_formulir=$this->txtPhotoNoFormulir->Value;
			$nama_file=$this->Themes->getDirImage('bydir').$no_formulir.'.jpg';			
			$this->FileUpload->saveAs($nama_file);
			$this->Themes->load($nama_file);			
			$this->Themes->resize(160,180);
			$this->Themes->save($nama_file);
			$this->Themes->resize(48,48);
			$this->Themes->save($nama_file.'_thub.jpg');			
			$this->spmb->setNoFormulir($no_formulir,true);
			$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
		}
	}
	
	public function closeAddProcess ($sender,$param) {
		$this->Pengguna->updateActivity();	
		unset($_SESSION['addProcess']);
		$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
	}
	public function printOut ($sender,$param) {	
        $this->createObj('reportspmb');
		switch ($sender->getId()) {
			case 'btnPrintOutFormulirPendaftaran' :
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
                        $kjur=$_SESSION['kjur'];
                        $nama_prodi=$_SESSION['daftar_jurusan'][$kjur];
                        $tahun=$_SESSION['tahun_masuk'];
                        $semester=$_SESSION['semester'];
                        $nama_tahun = $this->DMaster->getNamaTA($tahun);
                        $nama_semester = $this->setup->getSemester($semester);

                        $dataReport['kjur']=$_SESSION['kjur'];                
                        $dataReport['tahun_masuk']=$tahun;
                        $dataReport['semester']=$semester;
                        $dataReport['nama_tahun']=$nama_tahun;
                        $dataReport['nama_semester']=$nama_semester;        
                        $dataReport['daftar_via']='WEB';         
                        $dataReport['offset']=$_SESSION['currentPagePendaftaranWeb']['offset'];         
                        $dataReport['limit']=$_SESSION['currentPagePendaftaranWeb']['limit'];         

                        $messageprintout="Daftar Formulir Pendaftaran PS $nama_prodi Tahun Masuk $nama_tahun Semester $nama_semester : <br/>";
                        $dataReport['linkoutput']=$this->linkOutput;         
                        $this->report->setDataReport($dataReport);         
                        $this->report->setMode($_SESSION['outputreport']);
                        $this->report->printFormulirPendaftaranAll($_SESSION['outputcompress']);								
                    break;
                }
			break;
			case 'btnPrintOutFormulirPendaftaranR' :
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
                        $no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
                        $dataReport['no_formulir']=$no_formulir; 
                        $dataReport['linkoutput']=$this->linkOutput; 
                        $this->report->setDataReport($dataReport);      
                        $this->report->setMode($_SESSION['outputreport']);
                        $messageprintout="Formulir Pendaftaran $no_formulir : <br/>";
                        $this->report->printFormulirPendaftaran();				
                    break;
                }
			break;
		}        
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Formulir Pendaftaran MHS Baru/Pindahan';
        $this->modalPrintOut->show();
	}
}

?>