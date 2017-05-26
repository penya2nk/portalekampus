<?php
prado::using ('Application.MainPageMHS');
class CProfiles extends MainPageMHS {    
	public function onLoad($param) {		
		parent::onLoad($param);		
        $this->showProfiles=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageCache'])||$_SESSION['currentPageCache']['page_name']!='m.settings.Profiles') {
				$_SESSION['currentPageCache']=array('page_name'=>'m.settings.Profiles','page_num'=>0);												
			}            
            $this->populateData ();
		}
        
	}   
    public function populateData () {
        $this->cmbTheme->DataSource=$this->setup->getListThemes();
        $this->cmbTheme->Text=$_SESSION['theme'];
        $this->cmbTheme->DataBind();
        
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');
        
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.nama_ibu_kandung,fp.idwarga,fp.nik,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,kelurahan,kecamatan,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt,pm.photo_profile FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','nama_ibu_kandung','idwarga','nik','idstatus','alamat_kantor','alamat_rumah','kelurahan','kecamatan','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt','photo_profile'));
        $r=$this->DB->getRecord($str);
        $dataMhs=$r[1];								
        
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
        
        $this->txtEditNoKTP->Text=$dataMhs['nik'];
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
        
        $this->literalKelasMHS->Text=$this->DMaster->getNamaKelasByID($dataMhs['idkelas']);
        $daftar_jurusan=$this->DMaster->getListProgramStudi(2);
        $this->literalKjur1->Text=$daftar_jurusan[$dataMhs['kjur1']];
        $this->literalKjur2->Text=$daftar_jurusan[$dataMhs['kjur2']];
		$this->literalTahunMasuk->Text=$this->DMaster->getNamaTA($dataMhs['ta']);
        $this->literalSemesterMasuk->Text=$this->setup->getSemester($dataMhs['idsmt']);
        
        $this->imgPhotoUser->ImageUrl=$dataMhs['photo_profile'];
			
    }
    
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            $theme=$this->cmbTheme->Text;
            $_SESSION['theme']=$theme;
            $userid=$this->Pengguna->getDataUser('userid');
            $str = "UPDATE profiles_mahasiswa SET theme='$theme' WHERE nim=$userid";            
            $this->DB->updateRecord($str);            
           
            $this->redirect('settings.Profiles',true);
        }
    }
    public function saveDataPassword ($sender,$param) {
        if ($this->IsValid) {
            $userid=$this->Pengguna->getDataUser('userid');
            if ($this->txtPassword->Text != '') {  
                $password=md5($this->txtPassword->Text);
                $str = "UPDATE profiles_mahasiswa SET userpassword='$password' WHERE nim='$userid'";
                $this->DB->updateRecord($str);
            }
            $this->redirect('settings.Profiles',true);
        }
    }
    
    public function saveDataFP ($sender,$param) {
		if ($this->IsValid) {
			$no_formulir=$this->txtEditNoFormulir->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtEditNamaMhs->Text)));			
			$tempat_lahir=strtoupper(trim($this->txtEditTempatLahir->Text));						
			$tgl_lahir=date ('Y-m-d',$this->txtEditTanggalLahir->TimeStamp);
			$jk=$this->rdEditPria->Checked===true?'L':'P';
            $idagama=$this->cmbEditAgama->Text;
            $nama_ibu_kandung=addslashes($this->txtEditNamaIbuKandung->Text);
			$idwarga=$this->rdEditWNI->Checked===true?'WNI':'WNA';
            $no_ktp=strtoupper(trim(addslashes($this->txtEditNoKTP->Text)));
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
            	
            $str ="UPDATE formulir_pendaftaran SET nama_mhs='$nama_mhs',tempat_lahir='$tempat_lahir',tanggal_lahir='$tgl_lahir',jk='$jk',idagama=$idagama,nama_ibu_kandung='$nama_ibu_kandung',idwarga='$idwarga',nik='$no_ktp',idstatus='$idstatus',alamat_kantor='$alamat_kantor',alamat_rumah='$alamat_rumah',kelurahan='$kelurahan',kecamatan='$kecamatan',telp_kantor='$telp_kantor',telp_rumah='$telp_rumah',telp_hp='$telp_hp',idjp=$idjp,pendidikan_terakhir='$pendidikan_terakhir',jurusan='$jurusan',kota='$kota',provinsi='$provinsi',tahun_pa='$tahun_pa',jenis_slta='$jenisslta',asal_slta='$asal_slta',status_slta='$statusslta',nomor_ijazah='$nomor_ijazah' WHERE no_formulir='$no_formulir'";
            $this->DB->query('BEGIN');
			if ($this->DB->updateRecord($str)) {
                $email=$this->txtEditEmail->Text;                
                $str = "UPDATE profiles_mahasiswa SET email='$email' WHERE no_formulir=$no_formulir";
                $this->DB->updateRecord($str);
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }			
            $this->redirect('settings.Profiles',true);
        }
	}
    public function uploadPhotoProfile ($sender,$param) {
		if ($sender->getHasFile()) {
            $this->lblTipeFileError->Text='';
            $mime=$sender->getFileType();
            if($mime!="image/png" && $mime!="image/jpg" && $mime!="image/jpeg"){
                $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>File ini bukan tipe gambar</p>
                        </div>'; 
                $this->lblTipeFileError->Text=$error;
                return;
            }         

            if($mime=="image/png")	{
                if(!(imagetypes() & IMG_PNG)) {
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing png support in gd library.</p>
                        </div>'; 
                    $this->lblTipeFileError->Text=$error;                    
                    return;
                }
            }
            if(($mime=="image/jpg" || $mime=="image/jpeg")){
                if(!(imagetypes() & IMG_JPG)){                    
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing jpeg support in gd library.</p>
                        </div>'; 
                    $this->lblTipeFileError->Text=$error;
                    return;
                }
            }
            $filename=substr(hash('sha512',rand()),0,8);
            $name=$sender->FileName;
            $part=$this->setup->cleanFileNameString($name);            
            $path="resources/photomhs/$filename-$part";
            $sender->saveAs($path);            
            chmod(BASEPATH."/$path",0644); 
            $this->imgPhotoUser->ImageUrl=$path; 
            $no_formulir=$this->Pengguna->getDataUser('no_formulir');
            $this->DB->updateRecord("UPDATE profiles_mahasiswa SET photo_profile='$path' WHERE no_formulir='$no_formulir'");
            $_SESSION['foto']=$path;
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
            $this->lblTipeFileError->Text=$error;
            return;   
        }
    }
}