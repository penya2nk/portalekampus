<?php
prado::using ('Application.MainPageM');
class CDetailPendaftaranOnline extends MainPageM {
     
	public function onLoad($param) {		
		parent::onLoad($param);					
        $this->showPendaftaranOnline=true;
        $this->createObj('Akademik');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageDetailPendaftaranOnline'])||$_SESSION['currentPageDetailPendaftaranOnline']['page_name']!='m.spmb.DetailPendaftaranOnline') {
				$_SESSION['currentPageDetailPendaftaranOnline']=array('page_name'=>'m.spmb.DetailPendaftaranOnline','page_num'=>0,'DataMHS'=>array(),'activeviewindex'=>0);												
			}

            $this->MVDetailPendaftaranOnline->ActiveViewIndex=$_SESSION['currentPageDetailPendaftaranOnline']['activeviewindex'];             
		}		
	}  
    public function changeView ($sender,$param) {                
        try {
            $no_pendaftaran=addslashes($this->request['id']);
            $str  = "SELECT no_pendaftaran, no_formulir,nama_mhs,tempat_lahir,tanggal_lahir,jk,email,telp_hp,kjur1,kjur2,idkelas,ta,idsmt,waktu_mendaftar,file_bukti_bayar FROM formulir_pendaftaran_temp WHERE no_pendaftaran='$no_pendaftaran'";
            $this->DB->setFieldTable(array('no_pendaftaran','no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','email','telp_hp','kjur1','kjur2','idkelas','ta','idsmt','waktu_mendaftar','file_bukti_bayar'));
            $r=$this->DB->getRecord($str);	           
            if (!isset($r[1])) {
                unset($_SESSION['currentPageDetailPendaftaranOnline']);
                throw new Exception ("Mahasiswa Dengan No. Pendaftaran ($no_pendaftaran) tidak terdaftar di Portal.");
            }
            $datamhs=$r[1];
            $datamhs['no_formulir']=$datamhs['no_formulir']>0?$datamhs['no_formulir']:'N.A';
            $datamhs['nama_ps_1']=$_SESSION['daftar_jurusan'][$datamhs['kjur1']];
            $datamhs['nama_ps_2']=($r[1]['kjur2']>0)?$this->DMaster->getNamaProgramStudiByID($r[1]['kjur2']):'N.A';
            $datamhs['waktu_mendaftar']=$this->TGL->tanggal('d F Y H:m:s',$datamhs['waktu_mendaftar']);
                        
            $this->Demik->setDataMHS($datamhs);
            $_SESSION['currentPageDetailPendaftaranOnline']['DataMHS']=$datamhs;
            $activeview = $_SESSION['currentPageDetailPendaftaranOnline']['activeviewindex'];                
            if ($activeview == $this->MVDetailPendaftaranOnline->ActiveViewIndex) {
                switch ($activeview) {
                    case 0 : 
                        
                    break;                  
                }
            }else{
                $_SESSION['currentPageDetailPendaftaranOnline']['activeviewindex']=$this->MVDetailPendaftaranOnline->ActiveViewIndex;
                $this->redirect('spmb.DetailPendaftaranOnline',true,array('id'=>$datamhs['no_formulir']));
            }       
            
        }catch (Exception $ex) {
            $this->idProcess='view';	                
            $this->errorMessage->Text=$ex->getMessage();
        }          
    }
    public function getDataMHS($idx) {	
        $datamhs=$_SESSION['currentPageDetailPendaftaranOnline']['DataMHS'];
        return $datamhs[$idx];
    }
    public function resetPassword ($sender,$param) {        
        $data=$this->Pengguna->createHashPassword(1234);
        $salt=$data['salt'];
        $password=$data['password'];         
        $no_pendaftaran=$_SESSION['currentPageDetailPendaftaranOnline']['DataMHS']['no_pendaftaran'];

        $str = "UPDATE formulir_pendaftaran_temp SET salt='$salt',userpassword='$password' WHERE no_pendaftaran='$no_pendaftaran'";
        $this->DB->updateRecord($str);
        $this->lblInfo->Text='Reset Password Mahasiswa';
        $this->lblMessageInfo->Text='Password mahasiswa sukses direset menjadi 1234';        
        $this->modalMessage->show();
    }
    public function resetProfiles ($sender,$param) {
        $password_default = md5(1234);
        $no_formulir=$_SESSION['currentPageDetailPendaftaranOnline']['DataMHS']['no_formulir'];
        $str = "REPLACE INTO profiles_mahasiswa SET no_formulir='$no_formulir',userpassword='$password_default',theme='cube',photo_profile='resources/photomhs/no_photo.png'";
        $this->DB->updateRecord($str);
        $this->lblInfo->Text='Reset Profiles Mahasiswa';
        $this->lblMessageInfo->Text='Profiles mahasiswa sukses direset.';
        $this->modalMessage->show();
    }
    public function changeTheme ($sender,$param) {
        if ($this->IsValid) {
            $theme=  addslashes($sender->Text);
            $no_formulir=$_SESSION['currentPageDetailPendaftaranOnline']['DataMHS']['no_formulir'];
            $str = "UPDATE profiles_mahasiswa SET theme='$theme' WHERE no_formulir='$no_formulir'";            
            $this->DB->updateRecord($str);            
           
            $this->redirect('spmb.DetailPendaftaranOnline',true,array('id'=>$no_formulir));
        }
    }
    public function checkNoFormulir ($sender,$param) {					
		$no_formulir=$param->Value;		
        if ($no_formulir != '') {
            try {                  
                $str = "SELECT nama_mhs FROM formulir_pendaftaran WHERE no_formulir='$no_formulir'";
                $this->DB->setFieldTable(array('nama_mhs'));
                $r = $this->DB->getRecord($str);
                if (isset($r[1])) {  
                    $nama_mhs=$r[1]['nama_mhs'];
                    throw new Exception ("No. Formulir  ($no_formulir) sudah terdaftar atas nama $nama_mhs");                                                   
                }                
                if (!$this->DB->checkRecordIsExist ('no_formulir','pin',$no_formulir)) {
                     throw new Exception ("No. Formulir  ($no_formulir) tidak terdaftar di PIN.");   
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
	}	
    public function changePs($sender,$param) {        
        if ($sender->Text == 'none') {
            $this->cmbKjur2->Enabled=false;	
            $this->cmbKjur2->Text='none';
        }else{			            
            $this->cmbKjur2->Enabled=true;

            $jurusan=$this->DMaster->removeKjur($_SESSION['daftar_jurusan'],$sender->Text);									            
            $this->cmbKjur2->DataSource=$jurusan;
            $this->cmbKjur2->dataBind();
        }					
	}
    public function copyFormulir ($sender,$param) {
        if ($this->IsValid) {
            $old_no_formulir=$this->hiddennopendaftaran->Value;
            $no_formulir=  addslashes($this->txtNoFormulir->Text);            
            $kjur1=$this->cmbKjur1->Text;            
            $kjur2=($this->cmbKjur2->Text == 'none' || $this->cmbKjur2->Text == '')?0:$this->cmbKjur2->Text;
            
            $str = "SELECT tahun_masuk,semester_masuk,idkelas FROM pin WHERE no_formulir=$no_formulir";
            $this->DB->setFieldTable(array('tahun_masuk','semester_masuk','idkelas'));
            $r=$this->DB->getRecord($str);
            $tahun_masuk=$r[1]['tahun_masuk'];
            $semester_masuk=$r[1]['semester_masuk'];
            $idkelas=$r[1]['idkelas'];
            $this->DB->query('BEGIN');            
            $str = "INSERT INTO formulir_pendaftaran (no_formulir,nama_mhs,tempat_lahir,tanggal_lahir,jk,idagama,nama_ibu_kandung,idwarga,nik,idstatus,alamat_kantor,alamat_rumah,kelurahan,kecamatan,telp_kantor,telp_rumah,telp_hp,idjp,pendidikan_terakhir,jurusan,kota,provinsi,tahun_pa,jenis_slta,asal_slta,status_slta,nomor_ijazah,kjur1,kjur2,idkelas,daftar_via,ta,idsmt,waktu_mendaftar) SELECT $no_formulir,nama_mhs,tempat_lahir,tanggal_lahir,jk,idagama,nama_ibu_kandung,idwarga,nik,idstatus,alamat_kantor,alamat_rumah,kelurahan,kecamatan,telp_kantor,telp_rumah,telp_hp,idjp,pendidikan_terakhir,jurusan,kota,provinsi,tahun_pa,jenis_slta,asal_slta,status_slta,nomor_ijazah,$kjur1,$kjur2,'$idkelas',daftar_via,$tahun_masuk,$semester_masuk,NOW() FROM formulir_pendaftaran WHERE no_formulir=$old_no_formulir";
            if ($this->DB->insertRecord($str)) {                
                //kartu ujian
                $str = "INSERT INTO kartu_ujian (no_formulir,no_ujian,tgl_ujian,tgl_selesai_ujian,isfinish,idtempat_spmb)SELECT $no_formulir,$no_formulir,NOW(),NOW(),isfinish,idtempat_spmb FROM kartu_ujian WHERE no_formulir=$old_no_formulir";
                $this->DB->insertRecord($str);                
                //jawaban_ujian
                $str = "INSERT INTO jawaban_ujian (idjawabanujian,idsoal,idjawaban,no_formulir)SELECT NULL,idsoal,idjawaban,$no_formulir FROM jawaban_ujian WHERE no_formulir=$old_no_formulir";
                $this->DB->insertRecord($str);
                //nilai_ujian
                $str = "INSERT INTO nilai_ujian_masuk (idnilai_ujian_masuk,no_formulir,jumlah_soal,jawaban_benar,jawaban_salah,soal_tidak_terjawab,passing_grade_1,passing_grade_2,nilai)SELECT NULL,$no_formulir,jumlah_soal,jawaban_benar,jawaban_salah,soal_tidak_terjawab,passing_grade_1,passing_grade_2,nilai FROM nilai_ujian_masuk WHERE no_formulir=$old_no_formulir";
                $this->DB->insertRecord($str);
                //profiles mahasiswa
                $str = "INSERT INTO profiles_mahasiswa (idprofile,no_formulir,email,nim,userpassword,theme,photo_profile)SELECT NULL,$no_formulir,email,nim,userpassword,theme,photo_profile FROM profiles_mahasiswa WHERE no_formulir=$old_no_formulir";
                $this->DB->insertRecord($str);

                $waktu_mendaftar="$tahun_masuk-08-05 ".date("H:m:s");          
                $no_pendaftaran=$tahun_masuk.mt_rand(1000,9999).$semester_masuk;
                $data=$this->Pengguna->createHashPassword($no_pendaftaran);
                $salt=$data['salt'];
                $password=$data['password'];       
                $str  = "INSERT INTO formulir_pendaftaran_temp (no_pendaftaran,nama_mhs,tempat_lahir,tanggal_lahir,jk,email,telp_hp,kjur1,kjur2,idkelas,ta,idsmt,salt,userpassword,waktu_mendaftar) SELECT $no_pendaftaran,nama_mhs,tempat_lahir,tanggal_lahir,jk,email,telp_hp,kjur1,kjur2,'$idkelas','$tahun_masuk','$semester_masuk','$salt','$password','$waktu_mendaftar' FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE fp.no_formulir=pm.no_formulir AND fp.no_formulir=$old_no_formulir";
                $this->DB->insertRecord($str);
                
                $this->DB->query('COMMIT');
            }else{
                $this->DB->query('ROLLBACK');
            }
            
            $this->redirect('spmb.DetailPendaftaranOnline',true,array('id'=>$no_formulir));
        }
    }
}