<?php
prado::using ('Application.MainPageM');
class CDetailPendaftaranKonsentrasi Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showPendaftaranKonsentrasi=true;                
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            $this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
            try {
                $nim=$this->request['id'];                
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,vdm.idkelas,vdm.k_status FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE vdm.nim='$nim'";
                $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','idkelas','k_status'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                
                    throw new Exception ("Mahasiswa dengan NIM ($nim) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
                }
                $datamhs=$r[1];
                $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
                $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
                $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
                $_SESSION['currentPagePendaftaranKonsentrasi']['DataMHS']=$datamhs;
                $this->Nilai->setDataMHS($datamhs);
                $this->Nilai->getTranskripNilaiKurikulum();
                $this->hiddenJumlahSKS->Value=$this->Nilai->getTotalSKSAdaNilai();
                
                $str = "SELECT idkonsentrasi,jumlah_sks,tahun,idsmt,status_daftar FROM pendaftaran_konsentrasi WHERE nim='$nim'";
                $this->DB->setFieldTable(array('idkonsentrasi','jumlah_sks','tahun','idsmt','status_daftar'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                
                    throw new Exception ("Mahasiswa dengan NIM ($nim) belum memilih konsentrasi.");		
                }
                if ($r[1]['status_daftar']==1) {
                    throw new Exception ("Mahasiswa dengan NIM ($nim) sudah terdaftar di konsentrasi ".$datamhs['nama_konsentrasi']);
                }                
                $this->cmbKonsentrasiProdi->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListKonsentrasiProgramStudi($datamhs['kjur']),'none');
                $this->cmbKonsentrasiProdi->Text=$r[1]['idkonsentrasi'];
                $this->cmbKonsentrasiProdi->DataBind();
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}	
	}
    
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }
    
    public function mendaftarKonsentrasi ($sender,$param) {
        if ($this->IsValid) {
            $nim=$_SESSION['currentPagePendaftaranKonsentrasi']['DataMHS']['nim'];            
            $jumlah_sks=$this->hiddenJumlahSKS->Value;
            $idkonsentrasi=$this->cmbKonsentrasiProdi->Text;
            $str = "UPDATE pendaftaran_konsentrasi SET idkonsentrasi=$idkonsentrasi,jumlah_sks=$jumlah_sks WHERE nim='$nim'";
            $this->DB->updateRecord($str);            
            $this->redirect('kemahasiswaan.DetailPendaftaranKonsentrasi',true,array('id'=>$nim));
        }
    }
    public function approved($sender,$param) {
        $nim=$_SESSION['currentPagePendaftaranKonsentrasi']['DataMHS']['nim'];
        $idkonsentrasi=$this->cmbKonsentrasiProdi->Text;
        $this->DB->query('BEGIN');
        $str = "UPDATE pendaftaran_konsentrasi SET status_daftar=1 WHERE nim='$nim'";        
        if ($this->DB->updateRecord($str)) {            
            $str = "UPDATE register_mahasiswa SET idkonsentrasi=$idkonsentrasi WHERE nim='$nim'";        
            $this->DB->updateRecord($str);
            $this->DB->query('COMMIT');
            $this->redirect('kemahasiswaan.PendaftaranKonsentrasi',true);
        }else{
            $this->DB->query('ROLLBACK');
        }
        
    }
}
?>