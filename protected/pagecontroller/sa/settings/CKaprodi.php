<?php
prado::using ('Application.MainPageSA');
class CKaprodi extends MainPageSA {	
	public function onLoad($param) {
		parent::onLoad($param);	
        $this->showSubMenuSettingAkademik=true;
		$this->showKaprodi=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {				
            if (!isset($_SESSION['currentPageKaprodi'])||$_SESSION['currentPageKaprodi']['page_name']!='sa.settings.Kaprodi') {                
				$_SESSION['currentPageKaprodi']=array('page_name'=>'sa.settings.Kaprodi','page_num'=>0,'search'=>false,'DaftarDosen'=>array());												
			}
            $_SESSION['currentPageKaprodi']['search']=false;
            $_SESSION['currentPageKaprodi']['DaftarDosen']=$this->DMaster->getDaftarDosen();
			$this->populateData();
		}
	}
	
	public function populateData () {
		$str = "SELECT ps.kjur,ps.iddosen,d.nidn,d.nipy,CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen FROM program_studi ps LEFT JOIN dosen d ON (d.iddosen=ps.iddosen) WHERE ps.kjur != 0 ORDER BY ps.kjur ASC";
        $this->DB->setFieldTable(array('kjur','iddosen','nidn','nipy','nama_dosen')); 
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            if ($v['iddosen'] <= 0) {
                $v['nidn']='N.A';
                $v['nipy']='N.A';
            }
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}
	public function itemBound ($sender,$param) {
		$item=$param->item;
		if ($item->itemType==='Item' || $item->itemType === 'AlternatingItem') {
			$item->cmbFrontDosen->DataSource=$_SESSION['currentPageKaprodi']['DaftarDosen'];			
            $item->cmbFrontDosen->Text=$item->DataItem['iddosen'];
			$item->cmbFrontDosen->dataBind();									
		}
	}
	public function ubahKaprodi ($sender,$param) {
		$kjur=$this->getDataKeyField($sender,$this->RepeaterS);
		$iddosen=$sender->Text;				
		$str = "UPDATE program_studi SET iddosen='$iddosen' WHERE kjur='$kjur'";
		$this->DB->updateRecord($str);
        $this->redirect('settings.Kaprodi',true);
	}
	
	
}

?>
