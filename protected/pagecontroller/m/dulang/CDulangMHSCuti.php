<?php
prado::using ('Application.MainPageM');
class CDulangMHSCuti Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSCuti=true;                
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSCuti'])||$_SESSION['currentPageDulangMHSCuti']['page_name']!='m.dulang.DulangMHSCuti') {
				$_SESSION['currentPageDulangMHSCuti']=array('page_name'=>'m.dulang.DulangMHSCuti','page_num'=>0,'search'=>false,'tahun_masuk'=>$_SESSION['tahun_masuk'],'iddosen_wali'=>'none','DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSCuti']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	

            $tahun_masuk=$this->getAngkatan ();			            
            $this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
            $this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSCuti']['tahun_masuk'];						
            $this->tbCmbTahunMasuk->dataBind();

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
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
    }
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$tahunmasuk=$_SESSION['currentPageDulangMHSCuti']['tahun_masuk'] == 'none'?'':'Tahun Masuk '.$this->DMaster->getNamaTA($_SESSION['currentPageDulangMHSCuti']['tahun_masuk']);		        
		$this->lblModulHeader->Text="Program Studi $ps T.A $ta Semester $semester $tahunmasuk";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDulangMHSCuti']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDulangMHSCuti']['search']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;		        
		$_SESSION['currentPageDulangMHSCuti']['tahun_masuk']=$_SESSION['ta'];
		$this->tbCmbTahunMasuk->DataSource=$this->getAngkatan();
		$this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSCuti']['tahun_masuk'];
		$this->tbCmbTahunMasuk->dataBind();		
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['currentPageDulangMHSCuti']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
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
    public function searchRecord ($sender,$param){
        $_SESSION['currentPageDulangMHSCuti']['search']=true;
        $this->populateData($_SESSION['currentPageDulangMHSCuti']['search']);
    }
    public function populateData($search=false) {
        $ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];
		$tahun_masuk=$_SESSION['currentPageDulangMHSCuti']['tahun_masuk'];
        $iddosen_wali=$_SESSION['currentPageDulangMHSCuti']['iddosen_wali'];
        $str_dw = $iddosen_wali=='none'?'':" AND vdm.iddosen_wali=$iddosen_wali";
        $str_tahun_masuk=$tahun_masuk=='none'?'':" AND vdm.tahun_masuk=$tahun_masuk";      
        if ($search) {
            $str = "SELECT d.iddulang,vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal,d.tahun,d.idsmt FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='C'";
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {
                case 'no_formulir' :
                    $clausa="AND vdm.no_formulir='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='C' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nim' :
                    $clausa="AND d.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='C' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='C' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='C' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                            
            $str = "SELECT d.iddulang,vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal,d.tahun,d.idsmt FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' AND d.k_status='C' $str_dw $str_tahun_masuk";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' AND d.k_status='C' $str_dw $str_tahun_masuk",'vdm.nim');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDulangMHSCuti']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDulangMHSCuti']['page_num']=0;}
		$str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('iddulang','no_formulir','nim','nirm','nama_mhs','iddosen_wali','tanggal','tahun','idsmt'));
		$result=$this->DB->getRecord($str,$offset+1);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}	
    public function printOut ($sender,$param) {		
        $this->createObj('reportakademik');
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
                $messageprintout="Daftar Mahasiswa Daftar Ulang Status NON-AKTIF: <br/>";
                $dataReport['ta']=$_SESSION['ta'];
                $dataReport['nama_tahun']=$this->DMaster->getNamaTA($dataReport['ta']);
                $dataReport['idsmt']=$_SESSION['semester'];
                $dataReport['nama_semester']=$this->setup->getSemester($_SESSION['semester']);
                $dataReport['kjur']=$_SESSION['kjur'];
                $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
                $dataReport['linkoutput']=$this->linkOutput;                
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                
                $this->report->printDulangCUTI($this->DMaster);
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
            break;
        } 
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Ulang Mahasiswa CUTI';
        $this->modalPrintOut->show();
    }
}