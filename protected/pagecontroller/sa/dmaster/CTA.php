<?php
prado::using ('Application.MainPageSA');
class CTA extends MainPageSA {	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showSubMenuDMasterPerkuliahan=true;
        $this->showTA=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageTA'])||$_SESSION['currentPageTA']['page_name']!='sa.dmaster.TA') {
				$_SESSION['currentPageTA']=array('page_name'=>'sa.dmaster.TA','page_num'=>0,'search'=>false);
			}
			$this->populateData ();			
		}
	}
	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageTA']['page_num']=$param->NewPageIndex;	
		$this->populateData ();	
	}
	
	protected function populateData () {
        $jumlah_baris=$this->DB->getCountRowsOfTable('ta','tahun');
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageTA']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageTA']['page_num']=0;}

		$this->DB->setFieldTable (array('tahun','tahun_akademik'));
		$str = "SELECT * FROM ta ORDER BY tahun DESC LIMIT $offset,$limit";
		$result = $this->DB->getRecord($str,$offset+1);		
		$this->RepeaterS->DataSource=$result;		
		$this->RepeaterS->dataBind();
        
         $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);  
	}
	public function checkTA($sender,$param) {
        $this->idProcess=$sender->getId()=='addTahun'?'add':'edit';
        $tahun=$param->Value;		
		try {
			if ($this->hiddentahun->Value != $tahun) {
				if ($this->DB->checkRecordIsExist ('tahun','ta',$tahun)){
                    throw new Exception ("T.A $tahun sudah tidak tersedia, silahkan ganti dengan yang lain.");					
                }
			}
		}catch (Exception $e) {
			$param->IsValid=false;
            $sender->ErrorMessage=$e->getMessage();
		}	
	}	
	
	public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {
			$ta1=$this->txtAddTahun->Text;
			$ta2=$this->txtAddTahun->Text+1;
			$str = "INSERT INTO ta (tahun,tahun_akademik) VALUES ($ta1,'$ta1/$ta2')";
			$this->DB->insertRecord($str);	
            if ($this->Application->Cache) { 
                $dataitem=$this->DMaster->getList('ta',array('tahun','tahun_akademik'),'tahun',null,1);
                $dataitem['none']='Daftar Tahun Akademik';    
                $this->Application->Cache->set('listta',$dataitem);
            }
			$this->Redirect('dmaster.TA',true);
		}
	}
    public function editRecord ($sender,$param) {
		$tahun=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->idProcess='edit';
		$result = $this->DMaster->getList("ta WHERE tahun=$tahun",array('tahun','tahun_akademik'));		
		$this->hiddentahun->Value=$result[1]['tahun'];
		$this->txtEditTahun->Text=$result[1]['tahun'];		
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
			$ta1=$this->txtEditTahun->Text;
			$ta2=$this->txtEditTahun->Text+1;
			$str = "UPDATE ta SET tahun=$ta1,tahun_akademik='$ta1/$ta2' WHERE tahun=".$this->hiddentahun->Value;
			$this->DB->updateRecord($str);
            if ($this->Application->Cache) { 
                $dataitem=$this->DMaster->getList('ta',array('tahun','tahun_akademik'),'tahun',null,1);
                $dataitem['none']='Daftar Tahun Akademik';    
                $this->Application->Cache->set('listta',$dataitem);
            }
            $this->Redirect('dmaster.TA',true);
		}
	}
	public function deleteRecord ($sender,$param) {
		$tahun=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist ('ta','formulir_pendaftaran',$tahun)) {
            $this->lblHeaderMessageError->Text='Menghapus T.A';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus T.A ($tahun) karena sedang digunakan di pengampu penyelenggaraan.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("ta WHERE tahun=$tahun");
            if ($this->Application->Cache) { 
                $dataitem=$this->DMaster->getList('ta',array('tahun','tahun_akademik'),'tahun',null,1);
                $dataitem['none']='Daftar Tahun Akademik';    
                $this->Application->Cache->set('listta',$dataitem);
            }
            $this->Redirect('dmaster.TA',true);
        }
	}
	
}