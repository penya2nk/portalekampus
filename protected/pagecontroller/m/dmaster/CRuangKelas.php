<?php
prado::using ('Application.MainPageM');
class CRuangKelas Extends MainPageM {
	public function onLoad($param) {
		parent::onLoad($param);
		$this->showSubMenuDMasterPerkuliahan=true;
		$this->showRuangKelas=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageRuangKelas'])||$_SESSION['currentPageRuangKelas']['page_name']!='sa.dmaster.RuangKelas') {
				$_SESSION['currentPageRuangKelas']=array('page_name'=>'sa.dmaster.RuangKelas','page_num'=>0,'search'=>false);
			}
			$this->populateData();
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageRuangKelas']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
	protected function populateData() {
		$jumlah_baris=$this->DB->getCountRowsOfTable('ruangkelas','idruangkelas');;
		$str = "SELECT idruangkelas,namaruang,kapasitas FROM ruangkelas";
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageRuangKelas']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageRuangKelas']['page_num']=0;}
        $str = "$str LIMIT $offset,$limit";
        $this->DB->setFieldTable (array('idruangkelas','namaruang','kapasitas'));
		$r = $this->DB->getRecord($str,$offset+1);

        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function checkRuangKelas($sender,$param) {
        $this->idProcess=$sender->getId()=='addRuangkelas'?'add':'edit';
        $ruangkelas=$param->Value;		
        if ($ruangkelas != '') {
            try {   
                if ($this->hiddennamaruang->Value!=$ruangkelas) {                                                            
                    if ($this->DB->checkRecordIsExist('namaruang','ruangkelas',$ruangkelas)) {                                
                        throw new Exception ("Nama Ruang ($ruangkelas) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
	}
	public function saveData($sender,$param) {
		if ($this->Page->IsValid) {
			$namaruang=addslashes(strtoupper($this->txtAddNamaRuang->Text));
			$kapasitas=addslashes($this->txtAddKapasitas->Text);
			$str = "INSERT INTO ruangkelas (namaruang,kapasitas) VALUES ('$namaruang','$kapasitas')";
			$this->DB->insertRecord($str);
			$this->redirect('dmaster.RuangKelas',true);
		}
	}
	public function editRecord ($sender,$param) {
		$idruangkelas=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->idProcess='edit';
		$result = $this->DMaster->getList("ruangkelas WHERE idruangkelas=$idruangkelas",array('namaruang','kapasitas'));
		$this->hiddenid->Value=$idruangkelas;
		$this->hiddennamaruang->Value=$result[1]['namaruang'];
		$this->txtEditNamaRuang->Text=$result[1]['namaruang'];
		$this->txtEditKapasitas->Text=$result[1]['kapasitas'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $idruangkelas=$this->hiddenid->Value;
			$namaruang=addslashes(strtoupper($this->txtEditNamaRuang->Text));
			$kapasitas=  addslashes($this->txtEditKapasitas->Text);
			$str = "UPDATE ruangkelas SET namaruang='$namaruang',kapasitas='$kapasitas' WHERE idruangkelas=$idruangkelas";
			$this->DB->updateRecord($str);
			$this->redirect('dmaster.RuangKelas',true);
		}
	}
    public function deleteRecord ($sender,$param) {
		$idruangkelas=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist ('idruangkelas','kelas_mhs',$idruangkelas)) {
            $this->lblHeaderMessageError->Text='Menghapus Ruang Kelas';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus ruang kelas dengan ID ($idruangkelas) karena sedang digunakan di kelas mahasiswa.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("ruangkelas WHERE idruangkelas=$idruangkelas");
            $this->redirect('dmaster.RuangKelas',true);
        }
		
	}
}
?>
