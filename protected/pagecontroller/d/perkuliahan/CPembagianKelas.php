<?php
prado::using ('Application.MainPageD');
class CPembagianKelas extends MainPageD {	
public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPembagianKelas=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembagianKelas'])||$_SESSION['currentPagePembagianKelas']['page_name']!='d.perkuliahan.PembagianKelas') {                
				$_SESSION['currentPagePembagianKelas']=array('page_name'=>'d.perkuliahan.PembagianKelas','page_num'=>0,'search'=>false,'nama_hari'=>'none');												
			}
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

            $this->populateData();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();					
		}			
	}
    public function changeDosenPengampu ($sender,$param) {
		$_SESSION['currentPagePembagianKelas']['iddosen']=$this->cmbAddNamaDosen->Text;	 
        $this->redirect ('perkuliahan.PembagianKelas',true);
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
	public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];
        $iddosen=$this->Pengguna->getDataUser('iddosen');
        $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.sks,vpp.semester,vpp.nidn,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND vpp.iddosen=$iddosen ORDER BY hari ASC,idkelas ASC,nama_dosen ASC";
        $this->DB->setFieldTable(array('idkelas_mhs','kmatkul','nmatkul','sks','semester','nidn','idkelas','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));
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