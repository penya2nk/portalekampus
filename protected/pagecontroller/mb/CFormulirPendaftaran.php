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
                    $_SESSION['currentPageFormulirPendaftaran']=array('page_name'=>'mb.FormulirPendaftaran','page_num'=>0,'reguler'=>0,'karyawan'=>0,'ekstensi'=>0,'temp_file'=>'');												
                }
                $semester_default=$this->Pengguna->getDataUser('semester_masuk');
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
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.nama_ibu_kandung,fp.idwarga,fp.nik,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,kelurahan,kecamatan,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt,pm.photo_profile FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','nama_ibu_kandung','idwarga','nik','idstatus','alamat_kantor','alamat_rumah','kelurahan','kecamatan','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt','photo_profile'));
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
        if ($dataMhs['idwarga']=='WNI'){
            $this->rdEditWNI->Checked=true;
        }else{
            $this->rdEditWNA->Checked=true;
        }
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
        $this->imgEditFoto->ImageUrl=$dataMhs['photo_profile']; 
        $this->hiddenEditFoto->Value=$dataMhs['photo_profile']; 
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
        
        $this->imgEditFoto->ImageUrl=$this->Pengguna->getDataUser('photo_profile');
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
        $email_mhs=addslashes($param->Value);
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
        $this->idProcess='add';
		if ($this->IsValid) {
			$no_formulir=$this->txtAddNoFormulir->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtAddNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtAddTempatLahir->Text));						
			$tgl_lahir=date ('Y-m-d',$this->txtAddTanggalLahir->TimeStamp);
			$jk=$this->rdAddPria->Checked===true?'L':'P';
            $idagama=$this->cmbAddAgama->Text;
            $nama_ibu_kandung=addslashes($this->txtAddNamaIbuKandung->Text);
			$idwarga=$this->rdAddWNI->Checked===true?'WNI':'WNA';
            $no_ktp=strtoupper(trim(addslashes($this->txtAddNIK->Text)));
            $alamat_rumah=strtoupper(trim(addslashes($this->txtAddAlamatKTP->Text)));
            $kelurahan=addslashes($this->txtAddKelurahan->Text);
            $kecamatan=addslashes($this->txtAddKecamatan->Text);	
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
            $idsmt=$this->Pengguna->getDataUser('semester_masuk');
            $ta=$this->Pengguna->getDataUser('tahun_masuk');
            $idkelas=$this->cmbAddKelas->Text;
            $photo_profile=$this->hiddenAddFoto->Value;
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
			$str ="INSERT INTO formulir_pendaftaran (no_formulir,nama_mhs,tempat_lahir,tanggal_lahir,jk,idagama,nama_ibu_kandung,idwarga,nik,idstatus,alamat_kantor,alamat_rumah,kelurahan,kecamatan,telp_kantor,telp_rumah,telp_hp,idjp,pendidikan_terakhir,jurusan,kota,provinsi,tahun_pa,jenis_slta,asal_slta,status_slta,nomor_ijazah,kjur1,kjur2,waktu_mendaftar,ta,idsmt,idkelas,daftar_via) VALUES ('$no_formulir','$nama_mhs','$tempat_lahir','$tgl_lahir','$jk',$idagama,'$nama_ibu_kandung','$idwarga','$no_ktp','$idstatus','$alamat_kantor','$alamat_rumah','$kelurahan','$kecamatan','$telp_kantor','$telp_rumah','$telp_hp',$idjp,'$pendidikan_terakhir','$jurusan','$kota','$provinsi','$tahun_pa','$jenisslta','$asal_slta','$statusslta','$nomor_ijazah','$kjur1','$kjur2','$waktu_mendaftar',$ta,$idsmt,'$idkelas','WEB')";		
            $this->DB->query('BEGIN');
			if ($this->DB->insertRecord($str)) {
                $photo_profile=$this->hiddenAddFoto->Value;
                $userpassword=md5($this->Pengguna->getDataUser('no_pin'));
                $str = "INSERT INTO profiles_mahasiswa (idprofile,no_formulir,email,userpassword,theme,photo_profile) VALUES (NULL,$no_formulir,'$email','$userpassword','cube','$photo_profile')";
                $this->DB->insertRecord($str);
                $ket="Input Via WEB";
                $userid=1;
                $str = 'INSERT INTO bipend (idbipend,tahun,no_faktur,tgl_bayar,no_formulir,gelombang,dibayarkan,ket,userid) VALUES ';
                $str .= "(NULL,".$_SESSION['tahun_masuk'].",'$no_formulir','$waktu_mendaftar','$no_formulir','1','$dibayarkan','$ket','$userid')";				
                $this->DB->insertRecord($str);
                $_SESSION['foto']=$photo_profile;
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }
			$this->redirect('FormulirPendaftaran',true);
		}
    }
	public function updateData ($sender,$param) {
        $this->idProcess='edit';
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
            $kjur1=$this->cmbEditKjur1->Text;
            $kjur2=$this->cmbEditKjur2->Text;
            $waktu_mendaftar=date('Y-m-d H:m:s');            
            $ta=$this->Pengguna->getDataUser('tahun_masuk');
            $idsmt=$this->Pengguna->getDataUser('semester_masuk');
            $idkelas=$this->cmbEditKelas->Text;
         	$photo_profile=$this->hiddenEditFoto->Value;
            
            $str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=$idagama,nama_ibu_kandung='$nama_ibu_kandung',idwarga='$idwarga',nik='$no_ktp',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',kelurahan='$kelurahan',kecamatan='$kecamatan',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=$idjp,pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='$jenisslta',asal_slta='$asal_slta',status_slta='$statusslta',nomor_ijazah='$nomor_ijazah',kjur1='$kjur1',kjur2='$kjur2',waktu_mendaftar='$waktu_mendaftar',ta=$ta,idsmt=$idsmt,idkelas='$idkelas',daftar_via='WEB' WHERE no_formulir='$no_formulir'";
            $this->DB->query('BEGIN');
			if ($this->DB->updateRecord($str)) {
                $email=$this->txtEditEmail->Text;                
                $str = "UPDATE profiles_mahasiswa SET email='$email',photo_profile='$photo_profile' WHERE no_formulir=$no_formulir";
                $this->DB->updateRecord($str);
                $_SESSION['foto']=$photo_profile;
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }			
			$this->redirect('FormulirPendaftaran',true);
		}
	}
    public function uploadAddFoto ($sender,$param) {
		if ($sender->getHasFile()) {
            $this->setup->totalDelete($_SESSION['currentPageFormulirPendaftaran']['temp_file']);
            $this->lblAddTipeFileError->Text='';
            $mime=$sender->getFileType();
            if($mime!="image/png" && $mime!="image/jpg" && $mime!="image/jpeg"){
                $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>File ini bukan tipe gambar</p>
                        </div>'; 
                $this->lblAddTipeFileError->Text=$error;
                return;
            }         

            if($mime=="image/png")	{
                if(!(imagetypes() & IMG_PNG)) {
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing png support in gd library.</p>
                        </div>'; 
                    $this->lblAddTipeFileError->Text=$error;                    
                    return;
                }
            }
            if(($mime=="image/jpg" || $mime=="image/jpeg")){
                if(!(imagetypes() & IMG_JPG)){                    
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing jpeg support in gd library.</p>
                        </div>'; 
                    $this->lblAddTipeFileError->Text=$error;
                    return;
                }
            }
            $filename=substr(hash('sha512',rand()),0,8);
            $name=$sender->FileName;
            $part=$this->setup->cleanFileNameString($name);            
            $path="resources/photomhs/$filename-$part";
            $sender->saveAs($path);            
            chmod(BASEPATH."/$path",0644); 
            $this->hiddenAddFoto->Value=$path;
            $this->imgAddFoto->ImageUrl=$path;  
            $_SESSION['currentPageFormulirPendaftaran']['temp_file']=$path;
        }else {                    
            //error handling
            switch ($sender->ErrorCode){
                case 1:
                    $err="file size too big (php.ini).";
                break;
                case 2:
                    $err="file size too big (form).";
                break;
                case 3:
                    $err="file upload interrupted.";
                break;
                case 4:
                    $err="no file chosen.";
                break;
                case 6:
                    $err="internal problem (missing temporary directory).";
                break;
                case 7:
                    $err="unable to write file on disk.";
                break;
                case 8:
                    $err="file type not accepted.";
                break;
            }
            $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>'.$err.'</p>
                        </div>';   
            $this->lblAddTipeFileError->Text=$error;
            return;   
        }
    }
    public function uploadEditFoto ($sender,$param) {
		if ($sender->getHasFile()) {
            $this->setup->totalDelete($_SESSION['currentPageFormulirPendaftaran']['temp_file']);
            $this->lblEditTipeFileError->Text='';
            $mime=$sender->getFileType();
            if($mime!="image/png" && $mime!="image/jpg" && $mime!="image/jpeg"){
                $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>File ini bukan tipe gambar</p>
                        </div>'; 
                $this->lblEditTipeFileError->Text=$error;
                return;
            }         

            if($mime=="image/png")	{
                if(!(imagetypes() & IMG_PNG)) {
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing png support in gd library.</p>
                        </div>'; 
                    $this->lblEditTipeFileError->Text=$error;                    
                    return;
                }
            }
            if(($mime=="image/jpg" || $mime=="image/jpeg")){
                if(!(imagetypes() & IMG_JPG)){                    
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing jpeg support in gd library.</p>
                        </div>'; 
                    $this->lblEditTipeFileError->Text=$error;
                    return;
                }
            }
            $filename=substr(hash('sha512',rand()),0,8);
            $name=$sender->FileName;
            $part=$this->setup->cleanFileNameString($name);            
            $path="resources/photomhs/$filename-$part";
            $sender->saveAs($path);            
            chmod(BASEPATH."/$path",0644); 
            $this->hiddenEditFoto->Value=$path;
            $this->imgEditFoto->ImageUrl=$path;  
            $_SESSION['currentPageFormulirPendaftaran']['temp_file']=$path;
        }else {                    
            //error handling
            switch ($sender->ErrorCode){
                case 1:
                    $err="file size too big (php.ini).";
                break;
                case 2:
                    $err="file size too big (form).";
                break;
                case 3:
                    $err="file upload interrupted.";
                break;
                case 4:
                    $err="no file chosen.";
                break;
                case 6:
                    $err="internal problem (missing temporary directory).";
                break;
                case 7:
                    $err="unable to write file on disk.";
                break;
                case 8:
                    $err="file type not accepted.";
                break;
            }
            $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>'.$err.'</p>
                        </div>';   
            $this->lblEditTipeFileError->Text=$error;
            return;   
        }
    }
}