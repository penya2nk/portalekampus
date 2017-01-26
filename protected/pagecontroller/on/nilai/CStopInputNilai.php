<?php
prado::using ('Application.MainPageON');
class CStopInputNilai extends MainPageON{	
	public $pnlInputNim = false;
	public $pnlInputPenyelenggaraan = false;
	public function onLoad ($param) {
		parent::onLoad($param);	
		$this->showSubMenuAkademikNilai=true;
        $this->showStopInputNilai=true;    
		$this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageStopInputNilai'])||$_SESSION['currentPageStopInputNilai']['page_name']!='on.nilai.StopInputNilai') {					
                $_SESSION['currentPageStopInputNilai']=array('page_name'=>'on.nilai.StopInputNilai','page_num'=>0,'search'=>false,'iddosen'=>'none','nama_hari'=>'none');												
            }
            $_SESSION['currentPageNilaiFinal']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	

            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
            $this->tbCmbTA->DataSource=$ta;					
            $this->tbCmbTA->Text=$_SESSION['ta'];						
            $this->tbCmbTA->dataBind();

            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
            $this->tbCmbSemester->DataSource=$semester;
            $this->tbCmbSemester->Text=$_SESSION['semester'];
            $this->tbCmbSemester->dataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            $this->populateData();

		}
		
	}	
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageStopInputNilai']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageStopInputNilai']['search']);
	}	
    public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageStopInputNilai']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageStopInputNilai']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageStopInputNilai']['search']=true;
		$this->populateData($_SESSION['currentPageStopInputNilai']['search']);
	}
	protected function populateData ($search=false){
		$ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        
        if ($search) {
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nidn' :
                    $clausa=" AND vpp.nidn='$txtsearch'";  
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas,km.isi_nilai FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
                case 'kmatkul' :
                    $clausa="AND vpp.kmatkul LIKE '%$txtsearch%'";  
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas,km.isi_nilai FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
                case 'nmatkul' :
                    $clausa="AND vpp.nmatkul LIKE '%$txtsearch%'";  
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas,km.isi_nilai FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
                case 'nama_dosen' :
                    $clausa="AND vpp.nama_mhs LIKE '%$txtsearch%'";   
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas,km.isi_nilai FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
            }
        }else{
            $iddosen = $_SESSION['currentPageStopInputNilai']['iddosen'];
            $str_dosen = $iddosen == 'none' ? '':" AND vpp.iddosen=$iddosen";
            $nama_hari= $_SESSION['currentPageStopInputNilai']['nama_hari'];
            $str_nama_hari= $nama_hari == 'none' ? '':" AND km.hari=$nama_hari";
            $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas,km.isi_nilai FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$str_nama_hari $str_dosen";
            $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$str_nama_hari $str_dosen",'km.idkelas_mhs');
        }      
        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageStopInputNilai']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageStopInputNilai']['page_num']=0;}
        $str = "$str ORDER BY hari ASC,idkelas ASC,nama_dosen ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('idkelas_mhs','kmatkul','nmatkul','nama_dosen','idkelas','nidn','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas','isi_nilai'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result = array();
        while (list($k,$v)=each($r)) {  
            $kmatkul=$v['kmatkul'];
            $v['kode_matkul']=$this->Nilai->getKMatkul($kmatkul); 
            $v['namakelas']=$this->DMaster->getNamaKelasByID($v['idkelas']).'-'.chr($v['nama_kelas']+64) . ' ['.$v['nidn'].']';
            $v['jumlah_peserta_kelas']=$this->DB->getCountRowsOfTable('kelas_mhs_detail WHERE idkelas_mhs='.$v['idkelas_mhs'],'idkelas_mhs');
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function doVerified($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$verified=$sender->CommandParameter;
		$str = "UPDATE kelas_mhs SET isi_nilai=$verified WHERE idkelas_mhs=$id";	
		$this->DB->updateRecord($str);
		$this->redirect('nilai.StopInputNilai', true);
	}
}

?>