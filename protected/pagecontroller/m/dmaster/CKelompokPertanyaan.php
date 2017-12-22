<?php
prado::using ('Application.MainPageM');
class CKelompokPertanyaan extends MainPageM {		    	
	public function onLoad($param) {
		parent::onLoad($param);	
        $this->showSubMenuKuesioner=true;
        $this->showKelompokPertanyaan=true;    
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageKelompokPertanyaan'])||$_SESSION['currentPageKelompokPertanyaan']['page_name']!='m.dmaster.KelompokPertanyaan') {
				$_SESSION['currentPageKelompokPertanyaan']=array('page_name'=>'m.dmaster.KelompokPertanyaan','page_num'=>0,'search'=>false,'idkonsentrasi'=>'none','semester'=>'none');
			}
            $_SESSION['currentPageKelompokPertanyaan']['search']=false;            
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
			$this->populateData();            
		}
	}     
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKelompokPertanyaan']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageKelompokPertanyaan']['search']);
	}    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageKelompokPertanyaan']['search']=true;
        $this->populateData($_SESSION['currentPageKelompokPertanyaan']['search']);
	}    
	protected function populateData ($search=false) {								
        $jumlah_baris=$this->DB->getCountRowsOfTable('kelompok_pertanyaan','idkelompok_pertanyaan');		            
        $str = 'SELECT idkelompok_pertanyaan,orders,idkategori,nama_kelompok,orders,create_at,update_at FROM kelompok_pertanyaan';			
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKelompokPertanyaan']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageKelompokPertanyaan']['page_num']=0;}
        $str = "$str ORDER BY (orders+0) ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('idkelompok_pertanyaan','idkategori','nama_kelompok','orders','create_at','update_at'));
		$r = $this->DB->getRecord($str,$offset+1);	
        
        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}		    
    public function addProcess ($sender,$param) {
        $this->idProcess='add';       
    }    
    public function saveData ($sender,$param) {
		if ($this->Page->isValid) {	            
            $nama_kelompok=addslashes(strtoupper($this->txtAddNamaKelompok->Text));	            
            $urutan = addslashes($this->txtAddUrutan->Text);
            $str="INSERT INTO kelompok_pertanyaan (idkelompok_pertanyaan,idkategori,nama_kelompok,orders,create_at,update_at) VALUES (NULL,1,'$nama_kelompok','$urutan',NOW(),NOW())";									            
            $this->DB->insertRecord($str);
			$this->redirect('dmaster.KelompokPertanyaan',true);
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;        
        
        $str = "SELECT nama_kelompok,orders FROM kelompok_pertanyaan WHERE idkelompok_pertanyaan=$id";
        $this->DB->setFieldTable(array('nama_kelompok','orders'));
        $r=$this->DB->getRecord($str);
        
        $this->txtEditNamaKelompok->Text=$r[1]['nama_kelompok'];
        $this->txtEditUrutan->Text=$r[1]['orders'];
    }
    public function updateData ($sender,$param) {
		if ($this->Page->isValid) {			
            $id=$this->hiddenid->Value;
            $nama_kelompok=addslashes(strtoupper($this->txtEditNamaKelompok->Text));
            $urutan = addslashes($this->txtEditUrutan->Text);
			$str = "UPDATE kelompok_pertanyaan SET nama_kelompok='$nama_kelompok',orders='$urutan',update_at=NOW() WHERE idkelompok_pertanyaan=$id";
			$this->DB->updateRecord($str);			
			$this->redirect('dmaster.KelompokPertanyaan',true);
		}
	}
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);        
        $this->DB->deleteRecord("kelompok_pertanyaan WHERE idkelompok_pertanyaan=$id");
        $this->redirect('dmaster.KelompokPertanyaan',true);
        
    }        
}