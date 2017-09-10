<?php
prado::using ('Application.MainPageM');
class CRekapitulasiDPA extends MainPageM {	
    private $DataMHS;
	public function onLoad ($param) {
		parent::onLoad($param);
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showPerwalian=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageRekapitulasiDPA'])||$_SESSION['currentPageRekapitulasiDPA']['page_name']!='m.kemahasiswaan.RekapitulasiDPA') {
				$_SESSION['currentPageRekapitulasiDPA']=array('page_name'=>'m.kemahasiswaan.RekapitulasiDPA','page_num'=>0,'kjur'=>'none','tahun_masuk'=>'none','kelas'=>'none','status'=>'none');												
			}
            $daftar_prodi=$_SESSION['daftar_jurusan'];                        
            $daftar_prodi['none']='KESELURUHAN';
			$this->tbCmbPs->DataSource=$daftar_prodi;
			$this->tbCmbPs->Text=$_SESSION['currentPageRekapitulasiDPA']['kjur'];			
			$this->tbCmbPs->dataBind();
            
            $daftar_ta=$this->DMaster->getListTA();	
            $daftar_ta['none']='KESELURUHAN';
			$this->tbCmbTahunMasuk->DataSource=$daftar_ta;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['currentPageRekapitulasiDPA']['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $kelas=$this->DMaster->getListKelas();
            $kelas['none']='KESELURUHAN';
			$this->tbCmbKelas->DataSource=$kelas;
			$this->tbCmbKelas->Text=$_SESSION['currentPageRekapitulasiDPA']['kelas'];			
			$this->tbCmbKelas->dataBind();	
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
            
            $daftar_status=$this->DMaster->getListStatusMHS ();
            $daftar_status['none']='KESELURUHAN';
            $this->cmbStatus->DataSource=$daftar_status;
            $this->cmbStatus->Text=$_SESSION['currentPageRekapitulasiDPA']['status'];
            $this->cmbStatus->dataBind();
            
            $this->populateData();
		}
	}
    public function getInfoToolbar() {        
        $kjur=$_SESSION['currentPageRekapitulasiDPA']['kjur'];        		
        $ps=$kjur=='none'?'':'Program Studi '.$_SESSION['daftar_jurusan'][$kjur];
        $tahun_masuk=$_SESSION['currentPageRekapitulasiDPA']['tahun_masuk'];
		$tahunmasuk=$tahun_masuk=='none'?'':' Tahun Masuk '.$this->DMaster->getNamaTA($_SESSION['currentPageRekapitulasiDPA']['tahun_masuk']);		
		$idkelas=$_SESSION['currentPageRekapitulasiDPA']['kelas'];
		$kelas=$idkelas=='none'?'':' Kelas '.$this->DMaster->getNamaKelasByID($_SESSION['currentPageRekapitulasiDPA']['kelas']);		
        $text="$ps $tahunmasuk $kelas";
		return $text;
	}
    public function changeTbPs ($sender,$param) {		
        $_SESSION['currentPageRekapitulasiDPA']['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['currentPageRekapitulasiDPA']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
    public function changeTbKelas ($sender,$param) {				
		$_SESSION['currentPageRekapitulasiDPA']['kelas']=$this->tbCmbKelas->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
    public function changeStatus ($sender,$param) {
		$_SESSION['currentPageRekapitulasiDPA']['status']=$this->cmbStatus->Text;
		$this->populateData();
	}
	protected function populateData ($search=false) {
        $kjur=$_SESSION['currentPageRekapitulasiDPA']['kjur'];
        $str_kjur=$kjur=='none'?'':"AND rm.kjur=$kjur";
        
        $tahun_masuk=$_SESSION['currentPageRekapitulasiDPA']['tahun_masuk'];
        $str_tahun_masuk=$tahun_masuk=='none'?'':"AND rm.tahun=$tahun_masuk";
        
        $idkelas=$_SESSION['currentPageRekapitulasiDPA']['kelas'];
        $str_kelas=$idkelas=='none'?'':"AND rm.idkelas='$idkelas'";
        
        $status=$_SESSION['currentPageRekapitulasiDPA']['status'];
        $str_status=$status == 'none'? '' : " AND rm.k_status='$status'";
            
        $daftar_dw=$this->DMaster->removeIdFromArray($this->DMaster->getListDosenWali(),'none');
        $result=array();
        $i=1;
        $this->DB->setFieldTable(array('jk','jumlah_jk'));
        while (list($iddosen_wali,$nama_dw)=each($daftar_dw)){
            $str = "SELECT fp.jk, COUNT(rm.nim) AS jumlah_jk FROM formulir_pendaftaran fp,register_mahasiswa rm WHERE fp.no_formulir=rm.no_formulir  AND rm.iddosen_wali=$iddosen_wali $str_kjur $str_tahun_masuk $str_kelas $str_status GROUP BY fp.jk ORDER BY fp.jk ASC";
            $r = $this->DB->getRecord($str);
            $pria=0;
            $wanita=0;
            $jumlah=0;
            foreach ($r as $v) {
                switch ($v['jk']) {
                    case 'L' :
                        $pria=$v['jumlah_jk'];
                    break;
                    case 'P' :
                        $wanita=$v['jumlah_jk'];
                    break;
                }
            }
            
            $result[$i]=array('no'=>$i,'iddosen_wali'=>$iddosen_wali,'nama_dw'=>$nama_dw,'pria'=>$pria,'wanita'=>$wanita,'jumlah'=>$pria+$wanita);
            $i+=1;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
	}    
}
?>