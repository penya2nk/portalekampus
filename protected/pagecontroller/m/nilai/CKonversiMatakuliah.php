<?php
prado::using ('Application.MainPageM');
class CKonversiMatakuliah extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);					
        $this->showKonversiMatakuliah=true;
        $this->showSubMenuAkademikNilai=true;
        $this->createObj('Nilai');
		$this->Pengguna->moduleForbiden('spmb','ks');			
		if (!$this->IsPostBack && !$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKonversiMatakuliah'])||$_SESSION['currentPageKonversiMatakuliah']['page_name']!='m.spmb.KonversiMatakuliah') {
				$_SESSION['currentPageKonversiMatakuliah']=array('page_name'=>'m.spmb.KonversiMatakuliah','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false,'daftarmatkul'=>array(),'kjur'=>$_SESSION['kjur']);												
			}
            $_SESSION['currentPageKonversiMatakuliah']['search']=false;
            
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

            $daftar_prodi=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');                                    
			$this->tbCmbPs->DataSource=$daftar_prodi;
			$this->tbCmbPs->Text=$_SESSION['currentPageKonversiMatakuliah']['kjur'];			
			$this->tbCmbPs->dataBind();
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();

			$this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();
		}					
	}
	public function getInfoToolbar() {        
        $kjur=$_SESSION['currentPageKonversiMatakuliah']['kjur'];        		
        $ps=$_SESSION['daftar_jurusan'][$kjur];
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="$ps Tahun Masuk $tahunmasuk";
		return $text;
	}
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
        $_SESSION['currentPageKonversiMatakuliah']['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageKonversiMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPageKonversiMatakuliah']['search']);
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKonversiMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageKonversiMatakuliah']['search']);
	}		
	public function populateData ($search=false) {			
		$kjur=$_SESSION['currentPageKonversiMatakuliah']['kjur'];
		$tahun_masuk=$_SESSION['tahun_masuk'];		
        if ($search) {
            $txtsearch=$this->txtKriteria->Text;
            $str = "SELECT iddata_konversi,nama,alamat,no_telp FROM data_konversi2 WHERE perpanjangan=0";
            switch ($this->cmbKriteria->Text) {                                
                case 'nama' :
                    $cluasa="AND nama LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("data_konversi2 WHERE perpanjangan=0 $cluasa",'iddata_konversi');
                    $str = "$str $cluasa";
                break;
            }            			
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("data_konversi2 WHERE kjur='$kjur' AND tahun='$tahun_masuk' AND perpanjangan=0",'iddata_konversi');
			$str = "SELECT dk2.iddata_konversi,dk2.nama,dk2.alamat,dk2.no_telp,dk.nim FROM data_konversi2 dk2 LEFT JOIN data_konversi dk ON (dk2.iddata_konversi=dk.iddata_konversi) WHERE dk2.kjur='$kjur' AND dk2.tahun='$tahun_masuk' AND dk2.perpanjangan=0";
        }			
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKonversiMatakuliah']['page_num'];
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageKonversiMatakuliah']['page_num']=0;}
		$str = $str . " ORDER BY nama ASC LIMIT $offset,$limit";		
		$this->DB->setFieldTable(array('iddata_konversi','nama','alamat','no_telp','nim'));
		$r = $this->DB->getRecord($str,$offset+1);
		$result=array();        
        while (list($k,$v)=each($r)) {
            $iddata_konversi=$v['iddata_konversi'];
            $v['jumlahmatkul']=$this->DB->getCountRowsOfTable("nilai_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $v['jumlahsks']=$this->DB->getSumRowsOfTable('sks',"v_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $v['nim_alias']=$v['nim']=='' ? 'N.A' : $v['nim'];
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS); 
	}	
    public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';        
        
    }
}
