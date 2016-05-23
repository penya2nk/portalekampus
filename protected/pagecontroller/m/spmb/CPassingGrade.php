<?php
prado::using ('Application.MainPageM');
class CPassingGrade extends MainPageM {
    public $DataUjian;
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showSubMenuSPMBUjianPMB=true;
		$this->showPassingGradePMB=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePassingGrade'])||$_SESSION['currentPagePassingGrade']['page_name']!='m.spmb.PassingGrade') {
				$_SESSION['currentPagePassingGrade']=array('page_name'=>'m.spmb.PassingGrade');												
			}            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            $this->populateData ();	

		}	
	}
	public function getDataMHS($idx) {
        return $this->Demik->getDataMHS($idx);
    }
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}	
	public function getInfoToolbar() {        
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="Tahun Masuk $tahunmasuk";
		return $text;
	}		
	public function populateData () {	
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $str = "SELECT pg.takjur,ps.nama_ps,js.njenjang,pg.nilai FROM passinggrade pg,program_studi ps,jenjang_studi js WHERE ps.kjur=pg.kjur AND js.kjenjang=ps.kjenjang AND pg.tahun_masuk=$tahun_masuk ORDER BY pg.kjur ASC";
        $this->DB->setFieldTable(array('takjur','nama_ps','njenjang','nilai'));				
		$r = $this->DB->getRecord($str);
        
		$this->gridPassingGrade->DataSource=$r;
		$this->gridPassingGrade->dataBind();	        
	}
	
	public function reloadPassingGrade ($sender,$param) {
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $this->DB->query('BEGIN');
        if ($this->DB->deleteRecord("passinggrade WHERE tahun_masuk=$tahun_masuk")){
            $str = "INSERT INTO passinggrade (takjur,kjur,tahun_masuk,nilai) SELECT CONCAT($tahun_masuk,'',kjur),kjur,$tahun_masuk,0 FROM program_studi WHERE kjur!=0";
            $this->DB->insertRecord($str);
            $this->DB->query('COMMIT');
            $this->redirect('spmb.PassingGrade',true);
        }else{
            $this->DB->query('ROLLBACK');
        }
        
	}    
    public function onItemCreatedTargetFisik($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {   
            $item->ColumnNilai->TextBox->CssClass='form-control';                                
            $item->ColumnNilai->TextBox->Width='60px'; 
            $item->ColumnNilai->TextBox->Attributes->OnKeyUp='formatangka(this,true)';
        }
    }
    public function editItemNilai($sender,$param) {                   
        $this->gridPassingGrade->EditItemIndex=$param->Item->ItemIndex;
        $this->populateData();        
    }
    public function cancelItemNilai($sender,$param) {                
        $this->gridPassingGrade->EditItemIndex=-1;
        $this->populateData(); 
    }
     public function saveItemNilai($sender,$param) {                
        $item=$param->Item;
        $id=$this->gridPassingGrade->DataKeys[$item->ItemIndex];
        $nilai=$item->ColumnNilai->TextBox->Text > 100 ? 100:$item->ColumnNilai->TextBox->Text; 
        $str = "UPDATE passinggrade SET nilai=$nilai WHERE takjur=$id";
        $this->DB->updateRecord($str);
        $this->gridPassingGrade->EditItemIndex=-1;
        $this->populateData ();
     }
}

?>