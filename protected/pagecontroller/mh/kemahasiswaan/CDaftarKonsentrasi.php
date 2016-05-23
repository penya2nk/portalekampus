<?php
prado::using ('Application.MainPageMHS');
class CDaftarKonsentrasi extends MainPageMHS {
	public function onLoad($param) {		
		parent::onLoad($param);		                    
        $this->showDaftarKonsentrasi=true;    
        $this->createObj('Nilai');
        
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPageDaftarKonsentrasi'])||$_SESSION['currentPageDaftarKonsentrasi']['page_name']!='mh.akademik.DaftarKonsentrasi') {
				$_SESSION['currentPageDaftarKonsentrasi']=array('page_name'=>'mh.akademik.DaftarKonsentrasi','page_num'=>0,'search'=>false);												
			}    
            $this->Nilai->setDataMHS($this->Pengguna->getDataUser());        
            $this->cmbKonsentrasiProdi->DataSource=$this->DMaster->getListKonsentrasiProgramStudi($this->Pengguna->getDataUser('kjur'));
            $this->cmbKonsentrasiProdi->DataBind();
            
            $this->populateData();
		}        
	}   
    public function populateData () {
        $nim=$this->Pengguna->getDataUser('nim');
        $str = "SELECT idkonsentrasi,jumlah_sks,tahun,idsmt,status_daftar FROM pendaftaran_konsentrasi WHERE nim='$nim'";
        $this->DB->setFieldTable(array('idkonsentrasi','jumlah_sks','tahun','idsmt','status_daftar'));
        $r=$this->DB->getRecord($str);
        
        if (isset($r[1])){
            $this->cmbKonsentrasiProdi->Text=$r[1]['idkonsentrasi'];
            $this->cmbKonsentrasiProdi->Enabled=false;
            $this->btnDaftarKonsentrasi->Enabled=false;            
        }
    }
    public function mendaftarKonsentrasi ($sender,$param) {
        if ($this->IsValid) {
            $nim=$this->Pengguna->getDataUser('nim');
            $kjur=$this->Pengguna->getDataUser('kjur');
            $jumlah_sks=$this->hiddenJumlahSKS->Value;
            $idkonsentrasi=$this->cmbKonsentrasiProdi->Text;
            $ta=$this->setup->getSettingValue('default_ta');
            $semester=$this->setup->getSettingValue('default_semester');
            $str = "INSERT INTO pendaftaran_konsentrasi (nim,kjur,idkonsentrasi,jumlah_sks,tahun,idsmt,tanggal_daftar,status_daftar) VALUES ('$nim',$kjur,$idkonsentrasi,$jumlah_sks,$ta,$semester,NOW(),0)";
            $this->DB->insertRecord($str);
            
            $this->redirect('kemahasiswaan.DaftarKonsentrasi',true);
        }
    }
}
?>