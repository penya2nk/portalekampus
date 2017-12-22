<?php
prado::using ('Application.MainPageM');
class CDetailFormulir extends MainPageM {
     
	public function onLoad($param) {		
		parent::onLoad($param);					
        $this->showFormulirPendaftaran=true;
        $this->createObj('Akademik');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageDetailFormulir'])||$_SESSION['currentPageDetailFormulir']['page_name']!='m.spmb.DetailFormulir') {
				$_SESSION['currentPageDetailFormulir']=array('page_name'=>'m.spmb.DetailFormulir','page_num'=>0,'DataMHS'=>array(),'activeviewindex'=>0);												
			}

            $this->MVDetailFormulir->ActiveViewIndex=$_SESSION['currentPageDetailFormulir']['activeviewindex'];             
		}		
	}  
    public function changeView ($sender,$param) {                
        try {
            $no_formulir=addslashes($this->request['id']);
            $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.jk,fp.tempat_lahir,fp.tanggal_lahir,fp.alamat_rumah,fp.kjur1,fp.kjur2,fp.ta AS tahun_masuk,fp.idkelas,ke.nkelas,pm.theme,fp.waktu_mendaftar FROM formulir_pendaftaran fp LEFT JOIN profiles_mahasiswa pm ON pm.no_formulir=fp.no_formulir LEFT JOIN kelas ke ON (fp.idkelas=ke.idkelas)  WHERE fp.no_formulir='$no_formulir'";
            $this->DB->setFieldTable(array('no_formulir','nama_mhs','jk','tempat_lahir','tanggal_lahir','alamat_rumah','kjur1','kjur2','telp_hp','tahun_masuk','idkelas','nkelas','theme','waktu_mendaftar'));
            $r=$this->DB->getRecord($str);	           
            if (!isset($r[1])) {
                unset($_SESSION['currentPageDetailFormulir']);
                throw new Exception ("Mahasiswa Dengan NIM ($no_formulir) tidak terdaftar di Portal.");
            }
            $datamhs=$r[1];
            $datamhs['nama_ps1']=$_SESSION['daftar_jurusan'][$datamhs['kjur1']];
            $datamhs['nama_ps2']=$_SESSION['daftar_jurusan'][$datamhs['kjur2']];
            $datamhs['waktu_mendaftar']=$this->TGL->tanggal('d F Y',$datamhs['waktu_mendaftar']);
            //theme
            $this->cmbTheme->DataSource=$this->setup->getListThemes();
            $this->cmbTheme->Text=$datamhs['theme'];
            $this->cmbTheme->DataBind();
            
            $this->Demik->setDataMHS($datamhs);
            $_SESSION['currentPageDetailFormulir']['DataMHS']=$datamhs;
            $activeview = $_SESSION['currentPageDetailFormulir']['activeviewindex'];                
            if ($activeview == $this->MVDetailFormulir->ActiveViewIndex) {
                switch ($activeview) {
                    case 0 : 
                        $this->hiddennoformulir->Value=$no_formulir;
                        $jurusan=$this->DMaster->removeKjur($_SESSION['daftar_jurusan'],'none');									            
                        $this->cmbKjur1->DataSource=$jurusan;
                        $this->cmbKjur1->dataBind();
                    break;                  
                }
            }else{
                $_SESSION['currentPageDetailFormulir']['activeviewindex']=$this->MVDetailFormulir->ActiveViewIndex;
                $this->redirect('spmb.DetailFormulir',true,array('id'=>$datamhs['no_formulir']));
            }       
            
        }catch (Exception $ex) {
            $this->idProcess='view';	                
            $this->errorMessage->Text=$ex->getMessage();
        }          
    }
    public function getDataMHS($idx) {		
        return $this->Demik->getDataMHS($idx);
    }
    public function resetPassword ($sender,$param) {
        $password_default = md5(1234);
        $no_formulir=$_SESSION['currentPageDetailFormulir']['DataMHS']['no_formulir'];
        $str = "UPDATE profiles_mahasiswa SET userpassword='$password_default' WHERE no_formulir='$no_formulir'";
        $this->DB->updateRecord($str);
        $this->lblInfo->Text='Reset Password Mahasiswa';
        $this->lblMessageInfo->Text='Password mahasiswa sukses direset menjadi 1234';        
        $this->modalMessage->show();
    }
    public function changeTheme ($sender,$param) {
        if ($this->IsValid) {
            $theme=  addslashes($sender->Text);
            $no_formulir=$_SESSION['currentPageDetailFormulir']['DataMHS']['no_formulir'];
            $str = "UPDATE profiles_mahasiswa SET theme='$theme' WHERE no_formulir='$no_formulir'";            
            $this->DB->updateRecord($str);            
           
            $this->redirect('spmb.DetailFormulir',true,array('id'=>$no_formulir));
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
            $old_no_formulir=$this->hiddennoformulir->Value;
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
                
                $this->DB->query('COMMIT');
            }else{
                $this->DB->query('ROLLBACK');
            }
            
            $this->redirect('spmb.DetailFormulir',true,array('id'=>$no_formulir));
        }
    }
}