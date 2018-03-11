<?php
prado::using ('Application.MainPageK');
class CPendaftaranOnline extends MainPageK {
    public $DataUjian;
	public function onLoad($param) {
		parent::onLoad($param);			
		$this->showPendaftaranOnline=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePendaftaranOnline'])||$_SESSION['currentPagePendaftaranOnline']['page_name']!='k.spmb.PIN') {
				$_SESSION['currentPagePendaftaranOnline']=array('page_name'=>'k.spmb.PIN','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false,'display_record'=>'all','kelas'=>'A');												
			}
            $_SESSION['currentPagePendaftaranOnline']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $kelas=$this->DMaster->getListKelas();
			$this->tbCmbKelas->DataSource=$this->DMaster->removeIdFromArray($kelas,'none');
			$this->tbCmbKelas->Text=$_SESSION['currentPagePendaftaranOnline']['kelas'];			
			$this->tbCmbKelas->dataBind();	
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->cmbDisplayRecord->Text=$_SESSION['currentPagePendaftaranOnline']['display_record'];
            $this->lblModulHeader->Text=$this->getInfoToolbar();            
            $this->populateData ();	
		}	
	}   
	public function getInfoToolbar() {                
        $nama_kelas=$this->DMaster->getNamaKelasByID($_SESSION['currentPagePendaftaranOnline']['kelas']);
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="Kelas $nama_kelas Tahun Masuk $tahunmasuk";
		return $text;
	}
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}	
    public function changeTbKelas ($sender,$param) {				
		$_SESSION['currentPagePendaftaranOnline']['kelas']=$this->tbCmbKelas->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePendaftaranOnline']['search']=true;
		$this->populateData($_SESSION['currentPagePendaftaranOnline']['search']);
	}	
    public function changeDisplay($sender,$param) {        
        $_SESSION['currentPagePendaftaranOnline']['display_record']=$this->cmbDisplayRecord->Text;
        $this->populateData();
    }
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePendaftaranOnline']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePendaftaranOnline']['search']);
	}		
	public function populateData ($search=false) {
        $idkelas=$_SESSION['currentPagePendaftaranOnline']['kelas'];
        $tahun_masuk=$_SESSION['tahun_masuk'];   
		$str_display='';		
        if ($search) {        
            $str = "SELECT no_pendaftaran,no_formulir,nama_mhs,telp_hp,email,kjur1,kjur2,idkelas,waktu_mendaftar FROM formulir_pendaftaran_temp";
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {
                case 'no_registrasi' :
                    $clausa=" WHERE no_pendaftaran='$txtsearch'"; 
                break;
                case 'nama_mhs' :
                    $clausa=" WHERE nama_mhs LIKE '%$txtsearch%'";                    
                break;
            }
            $str="$str $clausa";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("formulir_pendaftaran_temp$clausa",'no_pendaftaran');		
        }else{            
            if ($_SESSION['currentPagePendaftaranOnline']['display_record']=='terdaftar'){
                $str_display='AND no_formulir > 0';
            }elseif ($_SESSION['currentPagePendaftaranOnline']['display_record']=='belum_terdaftar'){
                $str_display='AND no_formulir=0';
            }
            $str = "SELECT no_pendaftaran,no_formulir,nama_mhs,telp_hp,email,kjur1,kjur2,idkelas,waktu_mendaftar FROM formulir_pendaftaran_temp WHERE ta=$tahun_masuk $str_display";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("formulir_pendaftaran_temp WHERE ta=$tahun_masuk $str_display",'no_pendaftaran');		
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePendaftaranOnline']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePendaftaranOnline']['page_num']=0;}
		
		$str = "$str  $str_display ORDER BY waktu_mendaftar DESC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_pendaftaran','no_formulir','nama_mhs','telp_hp','email','kjur1','kjur2','idkelas','waktu_mendaftar'));
        $r = $this->DB->getRecord($str,$offset+1);
        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();	
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS); 
	} 
    
    public function printOut($sender,$param) {
        $this->createObj('reportspmb');
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
                $messageprintout="";
                $dataReport['tahun_masuk']=$_SESSION['tahun_masuk'];
                $dataReport['pilihan']=$_SESSION['currentPagePendaftaranOnline']['display_record'];
                $dataReport['linkoutput']=$this->linkOutput;
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                $this->report->printPIN(); 
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar PIN '.strtoupper($_SESSION['currentPagePendaftaranOnline']['display_record']);
        $this->modalPrintOut->show();
     }
}