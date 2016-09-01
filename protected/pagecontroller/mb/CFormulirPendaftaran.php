<?php //
prado::using ('Application.MainPageMB');
class CFormulirPendaftaran extends MainPageMB {
	public function onLoad ($param) {
		parent::onLoad ($param);
		$this->showFormulirPendaftaran = true;		        		
        $this->createObj('Finance');        
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            $this->lblModulHeader->Text='T.A '.$this->DMaster->getNamaTA($this->Pengguna->getDataUser('tahun_masuk'));
            try {                
                if (!isset($_SESSION['currentPageFormulirPendaftaran'])||$_SESSION['currentPageFormulirPendaftaran']['page_name']!='mb.FormulirPendaftaran') {
                    $_SESSION['currentPageFormulirPendaftaran']=array('page_name'=>'mb.FormulirPendaftaran','page_num'=>0,'reguler'=>0,'karyawan'=>0,'ekstensi'=>0);												
                }     
                $semester_default=$this->setup->getSettingValue('default_semester');
                $reguler=$this->Finance->getBiayaPendaftaran($_SESSION['tahun_masuk'],$semester_default,'A');							
                $karyawan=$this->Finance->getBiayaPendaftaran($_SESSION['tahun_masuk'],$semester_default,'B');							
                $ekstensi=$this->Finance->getBiayaPendaftaran($_SESSION['tahun_masuk'],$semester_default,'C');
                $_SESSION['currentPageFormulirPendaftaran']['reguler']=$reguler;
                $_SESSION['currentPageFormulirPendaftaran']['karyawan']=$karyawan;
                $_SESSION['currentPageFormulirPendaftaran']['ekstensi']=$ekstensi;
                if ($reguler <= 0 || $karyawan <= 0 || $ekstensi <= 0){                    
                    throw new Exception ("Biaya Pendaftaran kelas Reguler, Karyawan, Ekstensi belum di set oleh Manajemen");
                }
                $this->Finance->setDataMHS(array('no_formulir'=>$this->Pengguna->getDataUser('username')));
                if ($this->Finance->isMhsRegistered(true)) {
                    throw new Exception ("Anda sudah ter-register sebagai mahasiswa,maka dari itu tidak bisa mengisi atau mengubah formulir lagi");
                }elseif ($this->Finance->isNoFormulirExist()) {	
                    $this->editProcess();                
                }else{
                    $this->addProcess();                
                }                         
            }catch (Exception $e) {
                $this->idProcess='view';
                $this->errorMessage->Text=$e->getMessage();
            }                        
		}
	}
    private function editProcess() {
        $this->idProcess='edit';                         
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.idwarga,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','idwarga','idstatus','alamat_kantor','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt'));
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
        if ($dataMhs['idwarga']=='WNI')
            $this->rdEditWNI->Checked=true;
        else
            $this->rdEditWNA->Checked=true;
        
        $this->txtEditAlamatKTP->Text=$dataMhs['alamat_rumah'];	
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
    }
    private function addProcess() {     
        $this->idProcess='add';        
        $this->txtAddNoFormulir->Text = $this->Pengguna->getDataUser('no_formulir');		

        $this->cmbAddAgama->DataSource=$this->DMaster->getListAgama();        
        $this->cmbAddAgama->dataBind();		

        $this->cmbAddPekerjaanOrtu->DataSource=$this->DMaster->getListJenisPekerjaan ();
        $this->cmbAddPekerjaanOrtu->dataBind();		
        
        $this->cmbAddKelas->DataSource=$this->DMaster->getListKelas();
        $this->cmbAddKelas->dataBind();
        
        $this->cmbAddKjur1->DataSource=$_SESSION['daftar_jurusan'];            
        $this->cmbAddKjur1->dataBind();
        $this->cmbAddKjur2->Enabled=false;
			
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
    public function checkEmail ($sender,$param) {
        $id=$sender->getId ();
        $this->idProcess = ($id=='editEmail')?'edit':'add';			
        $email_mhs=$param->Value;
		try {			
			if ($email_mhs != '') {
				if ($this->hiddenemail->Value != $email_mhs) {
                    if ($this->DB->checkRecordIsExist('email','profiles_mahasiswa',$email_mhs)) {
                        throw new Exception ("Email ($email_mhs) sudah tidak tersedia. Silahkan ganti dengan yang lain.");
                    }					
				}
			}
		}catch (Exception $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->getMessage();
		}	
	}
    public function saveData ($sender,$param) {
		if ($this->IsValid) {
			$no_formulir=$this->txtAddNoFormulir->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtAddNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtAddTempatLahir->Text));						
			$tgl_lahir=date ('Y-m-d',$this->txtAddTanggalLahir->TimeStamp);
			$jk=$this->rdAddPria->Checked===true?'L':'P';
            $idagama=$this->cmbAddAgama->Text;
			$idwarga=$this->rdAddWNI->Checked===true?'WNI':'WNA';
            $alamat_rumah=strtoupper(trim(addslashes($this->txtAddAlamatKTP->Text)));	
            $telp_rumah=addslashes($this->txtAddNoTelpRumah->Text);		
            $telp_hp=addslashes($this->txtAddNoTelpHP->Text);
            $email=addslashes($this->txtAddEmail->Text);            
			$idstatus=$this->rdAddTidakBekerja->Checked===true?'TIDAK_BEKERJA':'PEKERJA';
			$alamat_kantor=strtoupper(trim($this->txtAddAlamatKantor->Text));									
			$telp_kantor=addslashes($this->txtAddNoTelpKantor->Text);
			$idjp=$this->cmbAddPekerjaanOrtu->Text;
			$pendidikan_terakhir=strtoupper(addslashes($this->txtAddPendidikanTerakhir->Text));
			$jurusan=strtoupper(addslashes($this->txtAddJurusan->Text));	
			$kota=strtoupper(addslashes($this->txtAddKotaPendidikanTerakhir->Text));	
			$provinsi=strtoupper(addslashes($this->txtAddProvinsiPendidikanTerakhir->Text));	
			$tahun_pa=strtoupper(trim($this->txtAddTahunPendidikanTerakhir->Text));		
            $jenisslta=$this->cmbAddJenisSLTA->Text;
			$asal_slta=strtoupper(addslashes($this->txtAddAsalSLTA->Text));			
            $statusslta=$this->cmbAddStatusSLTA->Text;
			$nomor_ijazah=trim($this->txtAddNomorIjazah->Text);			            
            $kjur1=$this->cmbAddKjur1->Text;
            $kjur2=$this->cmbAddKjur2->Text;
            $waktu_mendaftar=date('Y-m-d H:m:s');            
            $ta=$this->Pengguna->getDataUser('tahun_masuk');
            $idkelas=$this->cmbAddKelas->Text;
            switch ($idkelas) {
                case 'A' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['reguler'];
                break;
                case 'B' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['karyawan'];
                break;
                case 'C' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['ekstensi'];
                break;
            }
			$str ="INSERT INTO formulir_pendaftaran  (no_formulir,nama_mhs,tempat_lahir,tanggal_lahir,jk,idagama,idwarga,idstatus,alamat_kantor,alamat_rumah,telp_kantor,telp_rumah,telp_hp,idjp,pendidikan_terakhir,jurusan,kota,provinsi,tahun_pa,jenis_slta,asal_slta,status_slta,nomor_ijazah,kjur1,kjur2,waktu_mendaftar,ta,idsmt,idkelas,daftar_via) VALUES ('$no_formulir','$nama_mhs','$tempat_lahir','$tgl_lahir','$jk',$idagama,'$idwarga','$idstatus','$alamat_kantor','$alamat_rumah','$telp_kantor','$telp_rumah','$telp_hp',$idjp,'$pendidikan_terakhir','$jurusan','$kota','$provinsi','$tahun_pa','$jenisslta','$asal_slta','$statusslta','$nomor_ijazah','$kjur1','$kjur2','$waktu_mendaftar',$ta,1,'$idkelas','WEB')";		
            $this->DB->query('BEGIN');
			if ($this->DB->insertRecord($str)) {
                
                $userpassword=md5($this->Pengguna->getDataUser('no_pin'));
                $str = "INSERT INTO profiles_mahasiswa (idprofile,no_formulir,email,userpassword) VALUES (NULL,$no_formulir,'$email','$userpassword')";
                $this->DB->insertRecord($str);
                $ket="Input Via WEB";
                $userid=1;
                $str = 'INSERT INTO bipend (idbipend,tahun,no_faktur,tgl_bayar,no_formulir,gelombang,dibayarkan,ket,userid) VALUES ';
                $str .= "(NULL,".$_SESSION['tahun_masuk'].",'$no_formulir','$waktu_mendaftar','$no_formulir','1','$dibayarkan','$ket','$userid')";				
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }
			$this->redirect('FormulirPendaftaran',true);
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
			$idwarga=$this->rdEditWNI->Checked===true?'WNI':'WNA';
            $alamat_rumah=strtoupper(trim(addslashes($this->txtEditAlamatKTP->Text)));	
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
            $kjur1=$this->cmbEditKjur1->Text;
            $kjur2=$this->cmbEditKjur2->Text;
            $waktu_mendaftar=date('Y-m-d H:m:s');            
            $ta=$this->Pengguna->getDataUser('tahun_masuk');
            $idkelas=$this->cmbEditKelas->Text;
            switch ($idkelas) {
                case 'A' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['reguler'];
                break;
                case 'B' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['karyawan'];
                break;
                case 'C' :
                    $dibayarkan=$_SESSION['currentPageFormulirPendaftaran']['ekstensi'];
                break;
            }			
            $str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=$idagama,idwarga='$idwarga',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=$idjp,pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='$jenisslta',asal_slta='$asal_slta',status_slta='$statusslta',nomor_ijazah='$nomor_ijazah',kjur1='$kjur1',kjur2='$kjur2',waktu_mendaftar='$waktu_mendaftar',ta=$ta,idsmt=1,idkelas='$idkelas',daftar_via='WEB' WHERE no_formulir='$no_formulir'";
            $this->DB->query('BEGIN');
			if ($this->DB->updateRecord($str)) {
                $email=$this->txtEditEmail->Text;                
                $str = "UPDATE profiles_mahasiswa SET email='$email' WHERE no_formulir=$no_formulir";
                $this->DB->updateRecord($str);
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }			
			$this->redirect('FormulirPendaftaran',true);
		}
	}
}

?>