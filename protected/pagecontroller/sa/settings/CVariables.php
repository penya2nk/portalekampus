<?php
prado::using ('Application.MainPageSA');
class CVariables extends MainPageSA {    
	public function onLoad($param) {		
		parent::onLoad($param);				        
		$this->showVariable=true;       
		if (!$this->IsPostBack&&!$this->IsCallBack) {	           
            if (!isset($_SESSION['currentPageVariables'])||$_SESSION['currentPageVariables']['page_name']!='sa.settings.Variables') {
				$_SESSION['currentPageVariables']=array('page_name'=>'sa.settings.Variables','page_num'=>0);												
			}            
            $this->populateData (); 
		}
	}    
    public function populateData () { 
        $ta=$this->DMaster->getListTA();  
        $this->cmbDefaultTahunPendaftaran->DataSource=$ta;        
        $this->cmbDefaultTahunPendaftaran->Text=$this->setup->getSettingValue('default_tahun_pendaftaran');
        $this->cmbDefaultTahunPendaftaran->dataBind();
        
        $this->cmbDefaultTahunAkademik->DataSource=$ta;        
        $this->cmbDefaultTahunAkademik->Text=$this->setup->getSettingValue('default_ta');
        $this->cmbDefaultTahunAkademik->dataBind();
        
        $semester=$this->setup->getSemester();        
        $this->cmbDefaultSemester->DataSource=$semester;        
        $this->cmbDefaultSemester->Text=$this->setup->getSettingValue('default_semester');
        $this->cmbDefaultSemester->dataBind();
                
        $daftar_prodi=$this->DMaster->getListProgramStudi(2);        
        $this->cmbDefaultProdi->DataSource=$daftar_prodi;        
        $this->cmbDefaultProdi->Text=$this->setup->getSettingValue('default_kjur');
        $this->cmbDefaultProdi->dataBind();

        $this->txtJumlahBarisRepeater->Text=$this->setup->getSettingValue('default_pagesize');
        $this->txtMinimalNilaiKelulusan->Text=$this->setup->getSettingValue('minimal_nilai_kelulusan');
        
        $this->chklogger->Checked=$this->setup->getSettingValue('jslogger');
        
        //KRS
        $this->txtKRSJumlahSKSSetelahCuti->Text=$this->setup->getSettingValue('jumlah_sks_krs_setelah_cuti');  
        $this->txtKRSJumlahSKSMhsBaru->Text=$this->setup->getSettingValue('jumlah_sks_krs_mhs_baru');  
        
        //Transkrip Nilai        
        $this->txtTranskripNilaiNamaJabatan->Text=$this->setup->getSettingValue('nama_jabatan_transkrip');
        $daftardosen=$this->DMaster->removeIdFromArray($this->DMaster->getDaftarDosen(),'none');
        $this->cmbNamaPenandatangan->Text=$this->setup->getSettingValue('id_penandatangan_transkrip');
        $this->cmbNamaPenandatangan->DataSource=$daftardosen;
        $this->cmbNamaPenandatangan->DataBind();
        
        //Kartu Hasil Studi
        $this->txtKHSNamaJabatan->Text=$this->setup->getSettingValue('nama_jabatan_khs');        
        $this->cmbNamaPenandatanganKHS->Text=$this->setup->getSettingValue('id_penandatangan_khs');
        $this->cmbNamaPenandatanganKHS->DataSource=$daftardosen;
        $this->cmbNamaPenandatanganKHS->DataBind();
        
        //DPNA
        $this->txtDPNANamaJabatan->Text=$this->setup->getSettingValue('nama_jabatan_dpna');        
        $this->cmbNamaPenandatanganDPNA->Text=$this->setup->getSettingValue('id_penandatangan_dpna');
        $this->cmbNamaPenandatanganDPNA->DataSource=$daftardosen;
        $this->cmbNamaPenandatanganDPNA->DataBind();
        
        
    }
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            switch ($sender->getId()) {
                case 'btnSaveSettingUmum' :
                    $ta= $this->cmbDefaultTahunAkademik->Text;
                    $str = "UPDATE setting SET value='$ta' WHERE setting_id=1";            
                    $this->DB->updateRecord($str);

                    $semester= $this->cmbDefaultSemester->Text;
                    $str = "UPDATE setting SET value='$semester' WHERE setting_id=2";            
                    $this->DB->updateRecord($str);

                    $kjur= $this->cmbDefaultProdi->Text;
                    $str = "UPDATE setting SET value='$kjur' WHERE setting_id=6";            
                    $this->DB->updateRecord($str);
                    
                    $jumlah_baris= $this->txtJumlahBarisRepeater->Text;
                    $str = "UPDATE setting SET value='$jumlah_baris' WHERE setting_id=3";            
                    $this->DB->updateRecord($str);
                    
                    $minimal_nilai_kelulusan= $this->txtMinimalNilaiKelulusan->Text;
                    $str = "UPDATE setting SET value='$minimal_nilai_kelulusan' WHERE setting_id=55";            
                    $this->DB->updateRecord($str);
                    
                    $default_tahun_pendaftaran= $this->cmbDefaultTahunPendaftaran->Text;
                    $str = "UPDATE setting SET value='$default_tahun_pendaftaran' WHERE setting_id=56";            
                    $this->DB->updateRecord($str);
                    
                    $jslogger= $this->chklogger->Checked;
                    $str = "UPDATE setting SET value='$jslogger' WHERE setting_id=8";            
                    $this->DB->updateRecord($str);
                    
                break;
                case 'btnSaveKRS' :
                    $jumlah_sks_krs_setelah_cuti= $this->txtKRSJumlahSKSSetelahCuti->Text;
                    $str = "UPDATE setting SET value='$jumlah_sks_krs_setelah_cuti' WHERE setting_id=60";            
                    $this->DB->updateRecord($str);

                    $jumlah_sks_krs_mhs_baru= $this->txtKRSJumlahSKSMhsBaru->Text;
                    $str = "UPDATE setting SET value='$jumlah_sks_krs_mhs_baru' WHERE setting_id=61";            
                    $this->DB->updateRecord($str);
                break;
                case 'btnSaveTranskripNilai' :                    
                    $iddosen=$this->cmbNamaPenandatangan->Text;
                    $str = "UPDATE setting SET value='$iddosen' WHERE setting_id=20";                                
                    $this->DB->updateRecord($str);
                    
                    $nama_jabatan= $this->txtTranskripNilaiNamaJabatan->Text;
                    $str = "UPDATE setting SET value='$nama_jabatan' WHERE setting_id=21";            
                    $this->DB->updateRecord($str);
                    
                    
                    $str = "SELECT CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen,d.nipy,d.nidn,ja.nama_jabatan FROM dosen d LEFT JOIN jabatan_akademik ja ON (d.idjabatan=ja.idjabatan) WHERE d.iddosen='$iddosen'";
                    $this->DB->setFieldTable(array('nama_dosen','nipy','nidn','nama_jabatan'));			        
                    $r = $this->DB->getRecord($str);                
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_dosen']}' WHERE setting_id=22";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_jabatan']}' WHERE setting_id=23";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nipy']}' WHERE setting_id=24";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nidn']}' WHERE setting_id=25";                                
                    $this->DB->updateRecord($str);
                    
                break;
                case 'btnSaveKHS' :
                    $iddosen=$this->cmbNamaPenandatanganKHS->Text;
                    $str = "UPDATE setting SET value='$iddosen' WHERE setting_id=30";                                
                    $this->DB->updateRecord($str);
                    
                    $nama_jabatan= $this->txtKHSNamaJabatan->Text;
                    $str = "UPDATE setting SET value='$nama_jabatan' WHERE setting_id=31";            
                    $this->DB->updateRecord($str);
                    
                    
                    $str = "SELECT CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen,d.nipy,d.nidn,ja.nama_jabatan FROM dosen d LEFT JOIN jabatan_akademik ja ON (d.idjabatan=ja.idjabatan) WHERE d.iddosen='$iddosen'";
                    $this->DB->setFieldTable(array('nama_dosen','nipy','nidn','nama_jabatan'));			        
                    $r = $this->DB->getRecord($str);                
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_dosen']}' WHERE setting_id=32";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_jabatan']}' WHERE setting_id=33";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nipy']}' WHERE setting_id=34";                                
                    $this->DB->updateRecord($str);                                        
                    
                    $str = "UPDATE setting SET value='{$r[1]['nidn']}' WHERE setting_id=35";                                
                    $this->DB->updateRecord($str);                    
                    
                break;
                case 'btnSaveDPNA' :
                    $iddosen=$this->cmbNamaPenandatanganDPNA->Text;
                    $str = "UPDATE setting SET value='$iddosen' WHERE setting_id=40";                                
                    $this->DB->updateRecord($str);
                    
                    $nama_jabatan= $this->txtDPNANamaJabatan->Text;
                    $str = "UPDATE setting SET value='$nama_jabatan' WHERE setting_id=41";            
                    $this->DB->updateRecord($str);
                                        
                    $str = "SELECT CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen,d.nipy,d.nidn,ja.nama_jabatan FROM dosen d LEFT JOIN jabatan_akademik ja ON (d.idjabatan=ja.idjabatan) WHERE d.iddosen='$iddosen'";
                    $this->DB->setFieldTable(array('nama_dosen','nipy','nidn','nama_jabatan'));			        
                    $r = $this->DB->getRecord($str);                
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_dosen']}' WHERE setting_id=42";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nama_jabatan']}' WHERE setting_id=43";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nipy']}' WHERE setting_id=44";                                
                    $this->DB->updateRecord($str);
                    
                    $str = "UPDATE setting SET value='{$r[1]['nidn']}' WHERE setting_id=45";                                
                    $this->DB->updateRecord($str);
                break;
            }
            $this->setup->loadSetting(true);            
            $this->redirect('settings.Variables',true);
        }
    }
}