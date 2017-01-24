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
                $_SESSION['currentPageStopInputNilai']=array('page_name'=>'on.nilai.StopInputNilai','page_num'=>0,'search'=>false);												
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
//			if (isset($_SESSION['processNilai'])) {
//				$this->disableToolbars();
//				$this->idProcess='view';
//				$this->prepareTransaction ();
//			}else {
//				$this->tbCmbTa->DataSource=$this->Demik->removeNone($_SESSION['tahun_akademik']);
//				$this->tbCmbTa->Text=$_SESSION['ta'];
//				$this->tbCmbTa->dataBind();		
//					
//				$this->tbCmbPs->DataSource=$this->Demik->removeNone($_SESSION['daftar_jurusan']);
//				$this->tbCmbPs->Text=$_SESSION['kjur'];
//				$this->tbCmbPs->dataBind();				
//					
//				$this->tbCmbSmt->DataSource=$this->Demik->removeNone($_SESSION['daftar_semester']);
//				$this->tbCmbSmt->Text=$_SESSION['semester'];
//				$this->tbCmbSmt->dataBind();	
//				if (!isset($_SESSION['currentPage'])||$_SESSION['currentPage']['page_name']!='a_m_akademik_nilai') {
//					$_SESSION['currentPage']=array('page_name'=>'a_m_akademik_nilai','page_num'=>0);												
//				}
				$this->populateData();
//				$this->tbCmbKelas->Enabled=false;
//			}
		}
		
	}	
	public function changeTbTa ($sender,$param) {
		$_SESSION['currentPage']['page_num']=0;
		$_SESSION['ta']=$this->tbCmbTa->Text;
		$this->populateData();
	}	
	public function changeTbPs ($sender,$param) {
		$_SESSION['currentPage']['page_num']=0;
		$_SESSION['kjur']=$this->tbCmbPs->Text;
		$this->populateData();
	}	
	public function changeTbSmt ($sender,$param) {
		$_SESSION['currentPage']['page_num']=0;
		$_SESSION['semester']=$this->tbCmbSmt->Text;
		$this->populateData();
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPage']['page_num']=$param->NewPageIndex;
		$this->populateData($this->getStrSearch());
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}			
	public function goSearch ($sender,$param){
		if ($this->IsValid) {						
			$this->populateData($this->getStrSearch());							
		}
	}	
	public function getStrSearch() {
		$str='';
		$id=$this->txtAddBerdasarkan->Text;			
		switch ($this->cmbBerdasarkan->Text) {
			case 'kmatkul' :
				$kmatkul=$_SESSION['kjur'].'_'.$id;
				$str=" AND p.kmatkul='$kmatkul'";
			break;
			case 'nmatkul' :			
				$str=" AND m.nmatkul LIKE '%$id%'";
			break;
			case 'dosen' :			
				$str=" AND d.nama_dosen LIKE '%$id%'";
			break;
		}
		return $str;
	}
	protected function populateData ($strsearch='') {
		$ta=$_SESSION['ta'];
		$kjur=$_SESSION['kjur'];
		$semester=$_SESSION['semester'];		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPage']['page_num'];
		$str = "pengampu_penyelenggaraan pp,penyelenggaraan p,dosen d,matakuliah m WHERE p.idpenyelenggaraan=pp.idpenyelenggaraan AND pp.iddosen=d.iddosen AND p.kmatkul=m.kmatkul AND p.idsmt=$semester AND p.tahun=$ta AND p.kjur='$kjur'$strsearch";		
		$this->RepeaterS->VirtualItemCount=$this->DB->getCountRowsOfTable($str,'idpengampu_penyelenggaraan');
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPage']['page_num']=0;}
		$str = "SELECT pp.idpengampu_penyelenggaraan,d.nama_dosen,m.kmatkul,m.nmatkul,m.sks,m.semester,pp.verified FROM pengampu_penyelenggaraan pp,penyelenggaraan p,dosen d,matakuliah m WHERE p.idpenyelenggaraan=pp.idpenyelenggaraan AND pp.iddosen=d.iddosen AND p.kmatkul=m.kmatkul AND p.idsmt=$semester AND p.tahun=$ta AND p.kjur='$kjur'$strsearch ORDER BY m.semester ASC,p.kmatkul ASC LIMIT $offset,$limit";	
		$this->DB->setFieldTable (array('idpengampu_penyelenggaraan','kmatkul','nmatkul','sks','semester','nama_dosen','verified'));			
		$r = $this->DB->getRecord($str,$offset+1);			
		$str_jumlah_kelas = "kelas_mhs km WHERE km.idpengampu_penyelenggaraan=";	
		$result=array();	
		while (list ($k,$v) = each($r)) {			
			$v['kmatkul']=$this->Nilai->getKMatkul ($v['kmatkul']);	
			$idpengampu_penyelenggaraan=$v['idpengampu_penyelenggaraan'];			
			$v['jumlahKelas']=$this->DB->getCountRowsOfTable($str_jumlah_kelas.$idpengampu_penyelenggaraan,'idkelas_mhs');
			$result[$k]=$v;					
		}		
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function doVerified($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$verified=$sender->CommandParameter;
		$str = "UPDATE pengampu_penyelenggaraan SET verified=$verified WHERE idpengampu_penyelenggaraan=$id";	
		$this->DB->updateRecord($str);
		$this->redirect('nilai.StopInputNilai', true);
	}
}

?>