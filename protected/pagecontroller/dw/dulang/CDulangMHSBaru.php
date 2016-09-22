<?php
prado::using ('Application.MainPageDW');
class CDulangMHSBaru Extends MainPageDW {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSBaru=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSBaru'])||$_SESSION['currentPageDulangMHSBaru']['page_name']!='dw.dulang.DulangMHSBaru') {
				$_SESSION['currentPageDulangMHSBaru']=array('page_name'=>'dw.dulang.DulangMHSBaru','page_num'=>0,'search'=>false,'semester_masuk'=>1,'DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSBaru']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $semester=array('1'=>'GANJIL','2'=>'GENAP');  				
			$this->tbCmbSemesterMasuk->DataSource=$semester;
			$this->tbCmbSemesterMasuk->Text=$_SESSION['currentPageDulangMHSBaru']['semester_masuk'];
			$this->tbCmbSemesterMasuk->dataBind();  
            
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
        $tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
        $semester = $this->setup->getSemester($_SESSION['currentPageDulangMHSBaru']['semester_masuk']);		
		$this->lblModulHeader->Text="Program Studi $ps Tahun Masuk $tahunmasuk Semester $semester ";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDulangMHSBaru']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDulangMHSBaru']['search']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
    public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function changeTbSemesterMasuk ($sender,$param) {		
		$_SESSION['currentPageDulangMHSBaru']['semester_masuk']=$this->tbCmbSemesterMasuk->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function populateData($search=false) {
        $iddosen_wali=$this->iddosen_wali;
        if ($search) {
            $str = "SELECT k.idkrs,k.tgl_krs,vdm.no_formulir,k.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.kjur,vdm.tahun_masuk,vdm.semester_masuk,vdm.idkelas,k.sah,k.tgl_disahkan,0 AS boolpembayaran FROM krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND iddosen_wali=$iddosen_wali";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{
            $kjur=$_SESSION['kjur']; 
            $tahun_masuk=$_SESSION['tahun_masuk'];
            $semester_masuk=$_SESSION['currentPageDulangMHSBaru']['semester_masuk'];                        
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND iddosen_wali=$iddosen_wali AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur'";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND iddosen_wali=$iddosen_wali AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND kjur='$kjur'",'vdm.nim');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDulangMHSBaru']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDulangMHSBaru']['page_num']=0;}
		$str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','iddosen_wali','tanggal'));
		$result=$this->DB->getRecord($str);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}	
}
?>