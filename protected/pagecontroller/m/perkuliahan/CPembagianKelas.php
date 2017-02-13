<?php
prado::using ('Application.MainPageM');
class CPembagianKelas extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPembagianKelas=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembagianKelas'])||$_SESSION['currentPagePembagianKelas']['page_name']!='m.perkuliahan.PembagianKelas') {                
				$_SESSION['currentPagePembagianKelas']=array('page_name'=>'m.perkuliahan.PembagianKelas','page_num'=>0,'search'=>false,'iddosen'=>'none','nama_hari'=>'none');												
			}
            $_SESSION['currentPagePembagianKelas']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            
            $kjur=$_SESSION['kjur'];	
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$kjur;			
			$this->tbCmbPs->dataBind();	
            
            $tahun=$_SESSION['ta'];
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTA->DataSource=$ta;					
			$this->tbCmbTA->Text=$tahun;						
			$this->tbCmbTA->dataBind();
            
            $idsmt=$_SESSION['semester'];
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$idsmt;
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $nama_hari=$this->TGL->getNamaHari();
            $nama_hari['none']='Keseluruhan';
            $this->cmbNamaHari->DataSource=$nama_hari;
            $this->cmbNamaHari->Text=$_SESSION['currentPagePembagianKelas']['nama_hari'];
            $this->cmbNamaHari->dataBind();
            
            $str = "SELECT DISTINCT(vpp.iddosen) AS iddosen,vpp.nama_dosen,vpp.nidn FROM kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE vpp.idpengampu_penyelenggaraan=km.idpengampu_penyelenggaraan AND vpp.tahun=$tahun AND vpp.idsmt=$idsmt AND vpp.kjur=$kjur";
            $this->DB->setFieldTable(array('iddosen','nidn','nama_dosen'));
            $r = $this->DB->getRecord($str);	
            $daftar_dosen=array('none'=>'Keseluruhan');
            while (list($k,$v)=each($r)) { 
                $iddosen=$v['iddosen'];
                $daftar_dosen[$iddosen]=$v['nama_dosen'] . '['.$v['nidn'].']';
            }
            $this->cmbDosen->DataSource=$daftar_dosen;
            $this->cmbDosen->Text=$_SESSION['currentPagePembagianKelas']['iddosen'];
            $this->cmbDosen->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();		
            
		}			
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPagePembagianKelas']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPagePembagianKelas']['search']);
	}	
    public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function changeDosen ($sender,$param) {		
        $_SESSION['currentPagePembagianKelas']['iddosen']=$this->cmbDosen->Text;        
        $this->populateData();
	}
    public function changeNamaHari ($sender,$param) {		
        $_SESSION['currentPagePembagianKelas']['nama_hari']=$this->cmbNamaHari->Text;        
        $this->populateData();
	}
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePembagianKelas']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePembagianKelas']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePembagianKelas']['search']=true;
		$this->populateData($_SESSION['currentPagePembagianKelas']['search']);
	}
	public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        
        if ($search) {
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nidn' :
                    $clausa=" AND vpp.nidn='$txtsearch'";  
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
                case 'nama_matakuliah' :
                    $clausa="AND vpp.nmatkul LIKE '%$txtsearch%'";  
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
                case 'nama_dosen' :
                    $clausa="AND vpp.nama_dosen LIKE '%$txtsearch%'";   
                    $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'km.idkelas_mhs');
                break;
            }
        }else{
            $iddosen = $_SESSION['currentPagePembagianKelas']['iddosen'];
            $str_dosen = $iddosen == 'none' ? '':" AND vpp.iddosen=$iddosen";
            $nama_hari= $_SESSION['currentPagePembagianKelas']['nama_hari'];
            $str_nama_hari= $nama_hari == 'none' ? '':" AND km.hari=$nama_hari";
            $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.nama_dosen,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$str_nama_hari $str_dosen";
            $jumlah_baris=$this->DB->getCountRowsOfTable(" kelas_mhs km,v_pengampu_penyelenggaraan vpp WHERE km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$str_nama_hari $str_dosen",'km.idkelas_mhs');
        }      
        
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePembagianKelas']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePembagianKelas']['page_num']=0;}
        $str = "$str ORDER BY hari ASC,idkelas ASC,nama_dosen ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('idkelas_mhs','kmatkul','nmatkul','nama_dosen','idkelas','nidn','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));
		$r = $this->DB->getRecord($str,$offset+1);	
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
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function viewRecord ($sender,$param) {
        $idkelas_mhs=$this->getDataKeyField($sender, $this->RepeaterS);
        $str = "SELECT iddosen FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) WHERE km.idkelas_mhs=$idkelas_mhs";
		$this->DB->setFieldTable(array('iddosen'));
		$r = $this->DB->getRecord($str);	
        $_SESSION['currentPagePembagianKelas']['iddosen']=$r[1]['iddosen'];	 
        $this->redirect ('perkuliahan.DetailPembagianKelas',true);
	}	
    public function printOut ($sender,$param) {		
        $this->createObj('reportakademik');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        $idkelas_mhs = $this->getDataKeyField($sender,$this->RepeaterS);
        $dataReport=$this->Demik->getInfoKelas($idkelas_mhs);
		switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :               
                $dataReport['namakelas']=$this->DMaster->getNamaKelasByID($dataReport['idkelas']).'-'.chr($dataReport['nama_kelas']+64);
                $dataReport['hari']=$this->Page->TGL->getNamaHari($dataReport['hari']);
                
                $dataReport['nama_prodi']=$_SESSION['daftar_jurusan'][$dataReport['kjur']];
                $dataReport['nama_tahun'] = $this->DMaster->getNamaTA($dataReport['tahun']);
                $dataReport['nama_semester'] = $this->setup->getSemester($dataReport['idsmt']);               
                
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);  
                
                $messageprintout="Daftar Hadir Mahasiswa : <br/>";
                $this->report->printDaftarHadirMahasiswa();
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode excel pdf belum kami support.";
            break;
        }                
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Hadir Mahasiswa';
        $this->modalPrintOut->show();
	}
}

?>