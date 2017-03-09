<?php
prado::using ('Application.MainPageD');
class CEvaluasiHasilBelajar extends MainPageD {
	public function onLoad($param) {		
		parent::onLoad($param);				
		$this->showSubMenuAkademikNilai=true;
        $this->showEvaluasiHasilBelajar=true;
        
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageEvaluasiHasilBelajar'])||$_SESSION['currentPageEvaluasiHasilBelajar']['page_name']!='d.nilai.EvaluasiHasilBelajar') {
				$_SESSION['currentPageEvaluasiHasilBelajar']=array('page_name'=>'d.nilai.EvaluasiHasilBelajar','page_num'=>0,'search'=>false);
			}  
            $_SESSION['currentPageEvaluasiHasilBelajar']['search']=false;
            $_SESSION['currentPageDetailEvaluasiHasilBelajar']=array();
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

			$this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();           
				
            $this->populateData();
            $this->setInfoToolbar();
		}
	}	
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$_SESSION['ta'];		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$ta='T.A '.$this->DMaster->getNamaTA($_SESSION['ta']);		        
		$this->lblModulHeader->Text="Program Studi $ps $ta Semester $semester";
        
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;				
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}		
	public function populateData() {
        $iddosen=$this->Pengguna->getDataUser('iddosen');
		$ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];		

        $str = "SELECT vpp.idpengampu_penyelenggaraan,km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.kmatkul,vpp.nmatkul,vpp.sks,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND vpp.iddosen=$iddosen ORDER BY hari ASC,idkelas ASC";
       			
		$this->DB->setFieldTable(array('idpengampu_penyelenggaraan','idkelas_mhs','kmatkul','nmatkul','sks','idkelas','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {            
            $v['namakelas']=$this->DMaster->getNamaKelasByID($v['idkelas']).'-'.chr($v['nama_kelas']+64);
            $v['jumlah_peserta_kelas']=$this->DB->getCountRowsOfTable('kelas_mhs_detail WHERE idkelas_mhs='.$v['idkelas_mhs'],'idkelas_mhs');
            $result[$k]=$v;
        }      
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
	}
    public function printOut ($sender,$param) {	
        $idkelas_mhs = $this->getDataKeyField($sender,$this->RepeaterS);
        $this->createObj('reportnilai');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :
                $this->Nilai->getInfoKelas($idkelas_mhs);
                
                $dataReport=$this->Nilai->InfoKelas;
                print_R($dataReport);
                $dataReport['namakelas']=$this->DMaster->getNamaKelasByID($dataReport['idkelas']).'-'.chr($dataReport['nama_kelas']+64);
                $dataReport['hari']=$this->TGL->getNamaHari($dataReport['hari']);
                $dataReport['nama_semester'] = $this->setup->getSemester($dataReport['idsmt']);
                $dataReport['linkoutput']=$this->linkOutput; 
                            
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                
                $messageprintout="Format Evaluasi Hasil Belajar: <br/>";  
                $this->report->printFormatEvaluasiHasilBelajar($this->Nilai);              
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode PDF belum kami support.";
            break;

		}		
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Format Evaluasi Hasil Belajr';
        $this->modalPrintOut->show();
	}
}