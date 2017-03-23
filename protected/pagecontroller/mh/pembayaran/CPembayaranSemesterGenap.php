<?php
prado::using ('Application.MainPageMHS');
class CPembayaranSemesterGenap Extends MainPageMHS {		
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showPembayaranSemesterGenap=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembayaranSemesterGenap'])||$_SESSION['currentPagePembayaranSemesterGenap']['page_name']!='mh.pembayaran.PembayaranSemesterGenap') {
				$_SESSION['currentPagePembayaranSemesterGenap']=array('page_name'=>'mh.pembayaran.PembayaranSemesterGenap','page_num'=>0,'search'=>false);												
			}
            $_SESSION['currentPagePembayaranSemesterGenap']['search']=false; 
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');;
            $this->tbCmbTA->Text=$_SESSION['ta'];
            $this->tbCmbTA->dataBind();
            
            $this->populateData();
            $this->setInfoToolbar();
		}	
	}	
    public function setInfoToolbar() {        
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);        		
		$this->labelModuleHeader->Text="T.A $ta";        
	}
    public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePembayaranSemesterGenap']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePembayaranSemesterGenap']['search']);
	}		
	public function populateData($search=false) {		
		$ta=$_SESSION['ta'];
		$semester=2;
        $nim=$this->Pengguna->getDataUser('nim');
        
        $str = "SELECT no_transaksi,tanggal,idkelas,commited FROM transaksi WHERE tahun='$ta' AND idsmt='$semester' AND nim='$nim'";
        $jumlah_baris=$this->DB->getCountRowsOfTable("transaksi WHERE tahun='$ta' AND idsmt='$semester' AND nim='$nim'",'no_transaksi');

        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePembayaranSemesterGenap']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;   
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePembayaranSemesterGenap']['page_num']=0;}
        $this->DB->setFieldTable(array('no_transaksi','tanggal','idkelas','commited'));
        $str = "$str ORDER BY date_added DESC LIMIT $offset,$limit";	
        $r = $this->DB->getRecord($str,$offset+1);	        
        $result=array();		
		while (list($k,$v)=each($r)) {
			$no_transaksi=$v['no_transaksi'];				
			$str2 = "SELECT SUM(dibayarkan) AS dibayarkan FROM v_transaksi t WHERE no_transaksi=$no_transaksi";			
			$this->DB->setFieldTable(array('dibayarkan'));
			$r2=$this->DB->getRecord($str2);				
			$dibayarkan=$r2[1]['dibayarkan'];						
			$v['dibayarkan']=$this->Finance->toRupiah($dibayarkan);													
			$v['tanggal']=$this->TGL->tanggal('d/m/Y',$v['tanggal']);
			$result[$k]=$v;
		}
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
	}
	public function setDataBound($sender,$param) {				
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {			

		}		
	}	
	
    
	
}
?>