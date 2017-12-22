<?php
prado::using ('Application.MainPageMHS');
class CJadwalPerkuliahan extends MainPageMHS {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showJadwalPerkuliahan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageJadwalPerkuliahan'])||$_SESSION['currentPageJadwalPerkuliahan']['page_name']!='m.perkuliahan.JadwalPerkuliahan') {                
				$_SESSION['currentPageJadwalPerkuliahan']=array('page_name'=>'m.perkuliahan.JadwalPerkuliahan','page_num'=>0,'search'=>false,'iddosen'=>'none');												
			}
            $_SESSION['currentPageJadwalPerkuliahan']['search']=false;            
            
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTA->DataSource=$ta;					
			$this->tbCmbTA->Text=$_SESSION['ta'];						
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();
            
		}			
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageJadwalPerkuliahan']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageJadwalPerkuliahan']['search']);
	}	
    
    public function getInfoToolbar() { 
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	}
	public function populateData($search=false) {
        $datamhs=$this->Pengguna->getDataUser();  
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$datamhs['kjur'];        
        $idkelas=$datamhs['idkelas'];
               
        $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND km.idkelas='$idkelas' ORDER BY hari ASC,idkelas ASC,nama_dosen ASC";
        
        $this->DB->setFieldTable(array('idkelas_mhs','kmatkul','nmatkul','nama_dosen','idkelas','nidn','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));
		$r = $this->DB->getRecord($str);	
        $result = array();
        while (list($k,$v)=each($r)) {  
            $kmatkul=$v['kmatkul'];          
            $v['kode_matkul']=$this->Demik->getKMatkul($kmatkul); 
            $v['namakelas']=$this->DMaster->getNamaKelasByID($v['idkelas']).'-'.chr($v['nama_kelas']+64) . ' ['.$v['nidn'].']';
            $v['jumlah_peserta_kelas']=$this->DB->getCountRowsOfTable('kelas_mhs_detail WHERE idkelas_mhs='.$v['idkelas_mhs'],'idkelas_mhs');
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        
        $nim=$datamhs['nim'];
        $str = "SELECT vkm.idkelas,vkm.nama_kelas,vkm.hari,vkm.jam_masuk,vkm.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM krsmatkul km, krs k,kelas_mhs_detail kmd,kelas_mhs vkm,v_pengampu_penyelenggaraan vpp, ruangkelas rk  WHERE km.idkrs=k.idkrs AND kmd.idkrsmatkul=km.idkrsmatkul AND vkm.idkelas_mhs=kmd.idkelas_mhs AND vkm.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND rk.idruangkelas=vkm.idruangkelas AND k.nim=$nim AND k.idsmt=$idsmt AND k.tahun=$ta";
        $this->DB->setFieldTable(array('idkelas','kmatkul','nmatkul','nama_dosen','idkelas','nidn','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));
		$r = $this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {  
            $kmatkul=$v['kmatkul'];          
            $v['kode_matkul']=$this->Demik->getKMatkul($kmatkul); 
            $v['namakelas']=$this->DMaster->getNamaKelasByID($v['idkelas']).'-'.chr($v['nama_kelas']+64) . ' ['.$v['nidn'].']';            
            $result[$k]=$v;
        }
        $this->RepeaterJadwalSaya->DataSource=$result;
		$this->RepeaterJadwalSaya->dataBind(); 
	}
	public function viewRecord ($sender,$param) {
        $idkelas_mhs=$this->getDataKeyField($sender, $this->RepeaterS);
        $str = "SELECT iddosen FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) WHERE km.idkelas_mhs=$idkelas_mhs";
		$this->DB->setFieldTable(array('iddosen'));
		$r = $this->DB->getRecord($str);	
        $_SESSION['currentPageJadwalPerkuliahan']['iddosen']=$r[1]['iddosen'];	 
        $this->redirect ('perkuliahan.DetailJadwalPerkuliahan',true);
	}	
}