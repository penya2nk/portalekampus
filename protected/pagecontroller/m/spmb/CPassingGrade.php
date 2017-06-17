<?php
prado::using ('Application.MainPageM');
class CPassingGrade extends MainPageM {
    public $DataUjian;
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showSubMenuSPMBUjianPMB=true;
		$this->showPassingGradePMB=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePassingGrade'])||$_SESSION['currentPagePassingGrade']['page_name']!='m.spmb.PassingGrade') {
				$_SESSION['currentPagePassingGrade']=array('page_name'=>'m.spmb.PassingGrade','idjadwal_ujian'=>'none');												
			}            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $this->populateJadwalUjian();
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            $this->populateData ();	

		}	
	}
	public function getDataMHS($idx) {
        return $this->Demik->getDataMHS($idx);
    }
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateJadwalUjian();
		$this->populateData();
	}	
	public function getInfoToolbar() {        
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="Tahun Masuk $tahunmasuk";
		return $text;
	}	
    public function changeJadwalUjian($sender,$param) {					
		$_SESSION['currentPagePassingGrade']['idjadwal_ujian']=$this->cmbJadwalUjian->Text;
		$this->populateData();
	}	
    private function populateJadwalUjian () {
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE tahun_masuk='$tahun_masuk' ORDER BY tanggal_ujian ASC";                
        $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','namaruang','kapasitas','status'));
        $r = $this->DB->getRecord($str);	
        $result = array('none'=>' ');
        while (list($k,$v)=each($r)) {  
            $result[$v['idjadwal_ujian']]=$v['nama_kegiatan'] . ' # '.$this->TGL->tanggal('l, d F Y',$v['tanggal_ujian']) .' # '.$v['jam_mulai'].'-'.$v['jam_akhir'].'';            
        }
        $this->cmbJadwalUjian->DataSource=$result;					
        $this->cmbJadwalUjian->Text=$_SESSION['currentPagePassingGrade']['idjadwal_ujian'];						
        $this->cmbJadwalUjian->dataBind();
    }
	public function populateData () {	
        $idjadwal_ujian=$_SESSION['currentPagePassingGrade']['idjadwal_ujian'];
        $str = "SELECT pg.idpassing_grade,ps.kjur,ps.nama_ps,ps.konsentrasi,js.njenjang,pg.nilai FROM passinggrade pg,program_studi ps,jenjang_studi js WHERE ps.kjur=pg.kjur AND js.kjenjang=ps.kjenjang AND idjadwal_ujian='$idjadwal_ujian' ORDER BY pg.kjur ASC";
        $this->DB->setFieldTable(array('idpassing_grade','kjur','nama_ps','konsentrasi','njenjang','nilai'));				
		$r = $this->DB->getRecord($str);
        
        $result=array();
        while (list($k,$v)=each($r)){
            $v['nama_ps']=$v['konsentrasi']==''?$v['nama_ps']:$v['nama_ps'].' KONS. '.$v['konsentrasi'];
            $result[$k]=$v;    
        }
		$this->gridPassingGrade->DataSource=$result;
		$this->gridPassingGrade->dataBind();	        
	}
	
	public function reloadPassingGrade ($sender,$param) {
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $idjadwal_ujian=$_SESSION['currentPagePassingGrade']['idjadwal_ujian'];
        $this->DB->query('BEGIN');
        if ($this->DB->deleteRecord("passinggrade WHERE idjadwal_ujian='$idjadwal_ujian'")){
            $str = "INSERT INTO passinggrade (idpassing_grade,idjadwal_ujian,kjur,tahun_masuk,nilai) SELECT NULL,$idjadwal_ujian,kjur,$tahun_masuk,0 FROM program_studi WHERE kjur!=0";
            $this->DB->insertRecord($str);
            $this->DB->query('COMMIT');
            $this->redirect('spmb.PassingGrade',true);
        }else{
            $this->DB->query('ROLLBACK');
        }
        
	}    
    public function onItemCreatedTargetFisik($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {   
            $item->ColumnNilai->TextBox->CssClass='form-control';                                
            $item->ColumnNilai->TextBox->Width='60px'; 
            $item->ColumnNilai->TextBox->Attributes->OnKeyUp='formatangka(this,true)';
        }
    }
    public function editItemNilai($sender,$param) {                   
        $this->gridPassingGrade->EditItemIndex=$param->Item->ItemIndex;
        $this->populateData();        
    }
    public function cancelItemNilai($sender,$param) {                
        $this->gridPassingGrade->EditItemIndex=-1;
        $this->populateData(); 
    }
     public function saveItemNilai($sender,$param) {                
        $item=$param->Item;
        $id=$this->gridPassingGrade->DataKeys[$item->ItemIndex];
        $nilai=$item->ColumnNilai->TextBox->Text > 100 ? 100:$item->ColumnNilai->TextBox->Text; 
        $this->DB->query('BEGIN');
        $str = "UPDATE passinggrade SET nilai=$nilai WHERE idpassing_grade=$id";
        if ($this->DB->updateRecord($str)) {
            $str = "SELECT kjur,idjadwal_ujian FROM passinggrade pg WHERE idpassing_grade='$id'";
            $this->DB->setFieldTable(array('kjur','idjadwal_ujian'));				
            $r = $this->DB->getRecord($str);
            $kjur=$r[1]['kjur'];
            $idjadwal_ujian=$r[1]['idjadwal_ujian'];
            
            $str = "UPDATE nilai_ujian_masuk num JOIN peserta_ujian_pmb pup ON (num.no_formulir=pup.no_formulir) JOIN formulir_pendaftaran fp ON (fp.no_formulir=pup.no_formulir) SET num.passing_grade_1=$nilai WHERE  pup.idjadwal_ujian=$idjadwal_ujian AND fp.kjur1=$kjur";
            $this->DB->updateRecord($str);
            
            $str = "UPDATE nilai_ujian_masuk num JOIN peserta_ujian_pmb pup ON (num.no_formulir=pup.no_formulir) JOIN formulir_pendaftaran fp ON (fp.no_formulir=pup.no_formulir) SET num.passing_grade_2=$nilai WHERE  pup.idjadwal_ujian=$idjadwal_ujian AND fp.kjur2=$kjur";
            $this->DB->updateRecord($str);
            
            $this->DB->query('COMMIT');
        }else{
            $this->DB->query('ROLLBACK');
        }
        $this->gridPassingGrade->EditItemIndex=-1;
        $this->populateData ();
     }
}

?>