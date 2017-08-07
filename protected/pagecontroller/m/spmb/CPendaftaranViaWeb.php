<?php
prado::using ('Application.MainPageM');
class CPendaftaranViaWeb extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);			
		$this->showPendaftaranViaWeb=true; 
        $this->createObj('Akademik');        
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
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_pendaftaran'];						
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
		$_SESSION['tahun_pendaftaran']=$this->tbCmbTahunMasuk->Text;
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
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_pendaftaran']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps Tahun Pendaftaran $tahunmasuk Semester $semester";
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
        $tahun_masuk=$_SESSION['tahun_pendaftaran'];
        $semester=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];
        if ($search) {            
            $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.jk,fp.alamat_rumah,fp.telp_hp,nomor_ijazah,IF(char_length(COALESCE(rm.nim,''))>0,'dulang','-') AS ket,rm.nim FROM formulir_pendaftaran fp JOIN bipend bp ON (fp.no_formulir=bp.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=fp.no_formulir)";
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
    public function changePs($sender,$param) {
        if ($sender->getId()=='cmbAddKjur1') {
            $this->idProcess='add';
            if ($sender->Text == 'none') {
                $this->cmbAddKjur2->Enabled=false;	
                $this->cmbAddKjur2->Text='none';
            }else{			            
                $this->cmbAddKjur2->Enabled=true;
                
                $jurusan=$this->DMaster->removeKjur($_SESSION['daftar_jurusan'],$sender->Text);									            
                $this->cmbAddKjur2->DataSource=$jurusan;
                $this->cmbAddKjur2->dataBind();
            }
        }else {
            $this->idProcess='edit';
            $this->cmbEditKjur2->Enabled=true;	
            $jurusan=$this->DMaster->removeKjur($_SESSION['daftar_jurusan'],$sender->Text);									            
            $this->cmbEditKjur2->DataSource=$jurusan;
            $this->cmbEditKjur2->dataBind();
        }
        
							
	}
	public function updateData ($sender,$param) {
		if ($this->IsValid) {
			$no_formulir=$this->txtEditNoFormulir->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtEditNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtEditTempatLahir->Text));						
			$tgl_lahir=date ('Y-m-d',$this->txtEditTanggalLahir->TimeStamp);
			$jk=$this->rdEditPria->Checked===true?'L':'P';
            $idagama=$this->cmbEditAgama->Text;
            $nama_ibu_kandung=addslashes($this->txtEditNamaIbuKandung->Text);
			$idwarga=$this->rdEditWNI->Checked===true?'WNI':'WNA';
            $no_ktp=strtoupper(trim(addslashes($this->txtEditNIK->Text)));
            $alamat_rumah=strtoupper(trim(addslashes($this->txtEditAlamatKTP->Text)));
            $kelurahan=addslashes($this->txtEditKelurahan->Text);
            $kecamatan=addslashes($this->txtEditKecamatan->Text);
            $telp_rumah=addslashes($this->txtEditNoTelpRumah->Text);		
            $telp_hp=addslashes($this->txtEditNoTelpHP->Text);
            $email=addslashes($this->txtEditEmail->Text);            
			$idstatus=$this->rdEditTidakBekerja->Checked===true?'TIDAK_BEKERJA':'PEKERJA';
			$alamat_kantor=strtoupper(trim($this->txtEditAlamatKantor->Text));									
			$telp_kantor=addslashes($this->txtEditNoTelpKantor->Text);
			$idjp=$this->cmbEditPekerjaanOrtu->Text;
			$pendidikan_terakhir=strtoupper(addslashes($this->txtEditPendidikanTerakhir->Text));
			$jurusan=strtoupper(addslashes($this->txtEditJurusan->Text));	
			$kota=strtoupper(addslashes($this->txtEditKotaPendidikanTerakhir->Text));	
			$provinsi=strtoupper(addslashes($this->txtEditProvinsiPendidikanTerakhir->Text));	
			$tahun_pa=strtoupper(trim($this->txtEditTahunPendidikanTerakhir->Text));		
            $jenisslta=$this->cmbEditJenisSLTA->Text;
			$asal_slta=strtoupper(addslashes($this->txtEditAsalSLTA->Text));			
            $statusslta=$this->cmbEditStatusSLTA->Text;
			$nomor_ijazah=trim($this->txtEditNomorIjazah->Text);	
            
            $idkelas=$this->cmbEditKelas->Text; 
            $kjur1=$this->cmbEditKjur1->Text;
            $kjur2=$this->cmbEditKjur2->Text;                       
            $ta=$this->cmbEditTahunMasuk->Text;
            $idsmt=$this->cmbEditSemester->Text;           
            	
            $str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=$idagama,nama_ibu_kandung='$nama_ibu_kandung',idwarga='$idwarga',nik='$no_ktp',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',kelurahan='$kelurahan',kecamatan='$kecamatan',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=$idjp,pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='$jenisslta',asal_slta='$asal_slta',status_slta='$statusslta',nomor_ijazah='$nomor_ijazah',idkelas='$idkelas',kjur1='$kjur1',kjur2='$kjur2',ta=$ta,idsmt=$idsmt,daftar_via='WEB' WHERE no_formulir='$no_formulir'";
            $this->DB->query('BEGIN');
			if ($this->DB->updateRecord($str)) {
                $email=$this->txtEditEmail->Text;                
                $str = "UPDATE profiles_mahasiswa SET email='$email' WHERE no_formulir=$no_formulir";
                $this->DB->updateRecord($str);
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }			
            $this->redirect('spmb.PendaftaranViaWeb',true);
        }
	}
	
	public function editRecord($sender,$param) {
		$this->idProcess='edit';
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
        
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.nama_ibu_kandung,fp.idwarga,fp.nik,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,kelurahan,kecamatan,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','nama_ibu_kandung','idwarga','nik','idstatus','alamat_kantor','alamat_rumah','kelurahan','kecamatan','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt'));
        $r=$this->DB->getRecord($str);
        $dataMhs=$r[1];								
        if ($dataMhs['waktu_mendaftar']=='0000-00-00 00:00:00') {							
            $dataMhs['tanggal_lahir']='';
            $dataMhs['jk']='';
            $dataMhs['nama_agama']='';
            $dataMhs['idwarga']='';														
            $dataMhs['idstatus']='';		
            $dataMhs['nama_pekerjaan']='';
            $dataMhs['tahun_pa']='';
            $dataMhs['jenis_slta']='';
            $dataMhs['status_slta']='';
        }
        $this->txtEditNoFormulir->Text = $no_formulir;				        
        $this->txtEditNamaMhs->Text = $dataMhs['nama_mhs'];
        $this->txtEditTempatLahir->Text = $dataMhs['tempat_lahir'];
        $this->txtEditTanggalLahir->Text=$this->TGL->tanggal('d-m-Y',$dataMhs['tanggal_lahir'],'entoid');
        if ($dataMhs['jk']=='L')
            $this->rdEditPria->Checked=true;
        else
            $this->rdEditWanita->Checked=true;
        $this->cmbEditAgama->DataSource=$this->DMaster->getListAgama();
        $this->cmbEditAgama->Text=$dataMhs['idagama'];
        $this->cmbEditAgama->dataBind();		
        $this->txtEditNamaIbuKandung->Text=$dataMhs['nama_ibu_kandung'];
        if ($dataMhs['idwarga']=='WNI')
            $this->rdEditWNI->Checked=true;
        else
            $this->rdEditWNA->Checked=true;
        
        $this->txtEditNIK->Text=$dataMhs['nik'];
        $this->txtEditAlamatKTP->Text=$dataMhs['alamat_rumah'];
		$this->txtEditKelurahan->Text=$dataMhs['kelurahan'];
        $this->txtEditKecamatan->Text=$dataMhs['kecamatan'];
        $this->txtEditNoTelpRumah->Text=$dataMhs['telp_rumah'];		
        $this->txtEditNoTelpHP->Text=$dataMhs['telp_hp'];
        $this->txtEditEmail->Text=$dataMhs['email'];  
        $this->hiddenemail->Value=$dataMhs['email'];
        
        if ($dataMhs['idstatus']=='PEKERJA') {
            $this->rdEditBekerja->Checked=true;						
        }else {
            $this->rdEditTidakBekerja->Checked=true;
        }
        $this->txtEditAlamatKantor->Text=$dataMhs['alamat_kantor'];
        $this->txtEditNoTelpKantor->Text=$dataMhs['telp_kantor'];
        
        $this->cmbEditPekerjaanOrtu->DataSource=$this->DMaster->getListJenisPekerjaan ();
        $this->cmbEditPekerjaanOrtu->Text=$dataMhs['idjp'];
        $this->cmbEditPekerjaanOrtu->dataBind();		
        
        $this->txtEditPendidikanTerakhir->Text=$dataMhs['pendidikan_terakhir'];
        $this->txtEditJurusan->Text=$dataMhs['jurusan'];
        $this->txtEditKotaPendidikanTerakhir->Text=$dataMhs['kota'];
        $this->txtEditProvinsiPendidikanTerakhir->Text=$dataMhs['provinsi'];
        $this->txtEditTahunPendidikanTerakhir->Text=$dataMhs['tahun_pa'];        
        $this->cmbEditJenisSLTA->Text=$dataMhs['jenis_slta'];
        $this->txtEditAsalSLTA->Text=$dataMhs['asal_slta'];
        $this->cmbEditStatusSLTA->Text=$dataMhs['status_slta'];
        $this->txtEditNomorIjazah->Text=$dataMhs['nomor_ijazah'];
        
        $daftarkelas=$this->DMaster->removeIdFromArray($this->DMaster->getListKelas(),'none');        
        $this->cmbEditKelas->DataSource=$daftarkelas;
        $this->cmbEditKelas->Text=$dataMhs['idkelas'];
        $this->cmbEditKelas->dataBind();
		
        $bool=!$this->DB->checkRecordIsExist ('no_formulir','nilai_ujian_masuk',$no_formulir);
        $daftar_jurusan=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
        if ($dataMhs['kjur1'] =='') {
            $this->cmbEditKjur1->DataSource=$daftar_jurusan;
            $this->cmbEditKjur1->Text=$dataMhs['kjur1'];
            $this->cmbEditKjur1->Enabled=$bool;
            $this->cmbEditKjur1->dataBind();
            $this->cmbEditKjur2->Enabled=true;	            
        }else {					
            $this->cmbEditKjur1->DataSource=$daftar_jurusan;
            $this->cmbEditKjur1->Text=$dataMhs['kjur1'];
            $this->cmbEditKjur1->Enabled=$bool;
            $this->cmbEditKjur1->dataBind();

            $jurusan=$this->DMaster->removeKjur($_SESSION['daftar_jurusan'],$dataMhs['kjur1']);									            
            $this->cmbEditKjur2->DataSource=$jurusan;
            $this->cmbEditKjur2->Text=$dataMhs['kjur2'];
            $this->cmbEditKjur2->dataBind();
            $this->cmbEditKjur2->Enabled=true;
        }
        
        $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
        $this->cmbEditTahunMasuk->DataSource=$tahun_masuk	;					
        $this->cmbEditTahunMasuk->Text=$dataMhs['ta'];						
        $this->cmbEditTahunMasuk->dataBind();

        $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none'); 
        $this->cmbEditSemester->DataSOurce=$semester;
        $this->cmbEditSemester->Text=$dataMhs['idsmt'];
        $this->cmbEditSemester->DataBind();
        $this->Demik->setDataMHS(array('no_formulir'=>$no_formulir,'kjur'=>'none'));
		if ($this->Demik->isMhsRegistered(true)) {
			$this->cmbEditKelas->Enabled=false;
			$this->cmbEditTahunMasuk->Enabled=false;
			$this->cmbEditSemester->Enabled=false;
			$this->cmbEditKjur1->Enabled=false;
			$this->cmbEditKjur2->Enabled=false;
		}			
		
	}
	
	public function deleteRecord($sender,$param) {
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
        try {
            if ($this->DB->checkRecordIsExist('no_formulir','register_mahasiswa',$no_formulir)) {
                throw new Exception ("Tidak menghapus mahasiswa ini karena telah memiliki NIM.");
            }
            $str = "formulir_pendaftaran WHERE no_formulir='$no_formulir'";
            $this->DB->query('BEGIN');
            if ($this->DB->deleteRecord($str) ) {
                $this->DB->deleteRecord ("transaksi WHERE no_formulir='$no_formulir'");
                $this->DB->query ('COMMIT');
                $this->redirect('spmb.PendaftaranViaWeb',true);
            }else {
                $this->DB->query ('ROLLBACK');
            }	
            
        } catch (Exception $e) {
            $this->modalMessageError->show();
			$this->lblContentMessageError->Text=$e->getMessage();
        }    	
	}
    
	public function closeAddProcess ($sender,$param) {
		unset($_SESSION['addProcess']);
		$this->spmb->redirect('a.m.SPMB.PendaftaranViaFO');
	}
	public function printOut ($sender,$param) {	
        $this->createObj('reportspmb');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
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
                        $kjur=$_SESSION['kjur'];
                        $nama_prodi=$_SESSION['daftar_jurusan'][$kjur];
                        $tahun=$_SESSION['tahun_pendaftaran'];
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
                        $this->report->printFormulirPendaftaranAll($_SESSION['outputcompress'],$_SESSION['daftar_jurusan'],$this->DMaster);                
                    break;
                    case  'pdf' :
                        $kjur=$_SESSION['kjur'];
                        $nama_prodi=$_SESSION['daftar_jurusan'][$kjur];
                        $tahun=$_SESSION['tahun_pendaftaran'];
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
                        $_SESSION['outputcompress']=$_SESSION['outputcompress']=='none'?'zip':$_SESSION['outputcompress'];
                        $this->report->setMode($_SESSION['outputreport']);
                        $this->report->printFormulirPendaftaranAll($_SESSION['outputcompress'],$_SESSION['daftar_jurusan'],$this->DMaster);								
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
                        $this->report->printFormulirPendaftaran($_SESSION['daftar_jurusan'],$this->DMaster);				
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