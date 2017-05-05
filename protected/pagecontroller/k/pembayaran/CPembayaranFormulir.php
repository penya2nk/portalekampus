<?php
prado::using ('Application.MainPageK');
class CPembayaranFormulir Extends MainPageK {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showMenuPembayaran=true;
        $this->showPembayaranFormulir=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembayaranFormulir'])||$_SESSION['currentPagePembayaranFormulir']['page_name']!='k.pembayaran.PembayaranFormulir') {
				$_SESSION['currentPagePembayaranFormulir']=array('page_name'=>'k.pembayaran.PembayaranFormulir','page_num'=>0,'search'=>false,'semester_masuk'=>1,'DataMHS'=>array());												
			}
            $_SESSION['currentPagePembayaranFormulir']['search']=false; 
          
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $semester=array('1'=>'GANJIL','2'=>'GENAP');  				
			$this->tbCmbSemesterMasuk->DataSource=$semester;
			$this->tbCmbSemesterMasuk->Text=$_SESSION['currentPagePembayaranFormulir']['semester_masuk'];
			$this->tbCmbSemesterMasuk->dataBind();            

            $this->populateData();
            $this->setInfoToolbar();
		}	
	}	
    public function setInfoToolbar() {                
        $kjur=$_SESSION['kjur'];        
        $tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
        $semester = $this->setup->getSemester($_SESSION['currentPagePembayaranFormulir']['semester_masuk']);		
		$this->lblModulHeader->Text="T.A $tahunmasuk Semester $semester ";        
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
		$_SESSION['currentPagePembayaranFormulir']['semester_masuk']=$this->tbCmbSemesterMasuk->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePembayaranFormulir']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePembayaranFormulir']['search']);
	}		
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePembayaranFormulir']['search']=true;
		$this->populateData($_SESSION['currentPagePembayaranFormulir']['search']);
	}
	public function populateData($search=false) {		
		$tahun_masuk=$_SESSION['tahun_masuk'];
		$semester_masuk=$_SESSION['currentPagePembayaranFormulir']['semester_masuk'];
        if ($search) {
            $this->lblModulHeader->Text=' DARI HASI PENCARIAN';
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {                
                case 'no_faktur' :
                    $clausa=" AND t.no_faktur='$txtsearch'";
                    $str = "SELECT t.no_transaksi,t.no_faktur,t.tanggal,t.no_formulir,commited,CONCAT (t.tahun,t.idsmt) AS tasmt FROM transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1$clausa",'t.no_transaksi');
                break;
                case 'no_formulir' :
                    $clausa=" AND t.no_formulir='$txtsearch'";
                    $str = "SELECT t.no_transaksi,t.no_faktur,t.tanggal,t.no_formulir,commited,CONCAT (t.tahun,t.idsmt) AS tasmt FROM transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1$clausa";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1$clausa",'t.no_transaksi');
                break;
            }
        }else{
            $str = "SELECT t.no_transaksi,t.no_faktur,t.tanggal,t.no_formulir,commited,CONCAT (t.tahun,t.idsmt) AS tasmt FROM transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1 AND pin.tahun_masuk='$tahun_masuk' AND t.idsmt='$semester_masuk' AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk";
            $jumlah_baris=$this->DB->getCountRowsOfTable("transaksi t JOIN pin ON (t.no_formulir=pin.no_formulir) JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) WHERE pin.no_formulir=t.no_formulir AND td.idkombi=1 AND pin.tahun_masuk='$tahun_masuk' AND t.idsmt='$semester_masuk' AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk",'t.no_transaksi');
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePembayaranFormulir']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;   
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePembayaranFormulir']['page_num']=0;}
        $this->DB->setFieldTable(array('no_transaksi','no_faktur','tanggal','no_formulir','commited','tasmt'));
        $str = "$str ORDER BY t.date_added DESC LIMIT $offset,$limit";	
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
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function setDataBound($sender,$param) {				
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {			

		}		
	}	
	
    public function cekNomorFormulir ($sender,$param) {		
        $noformulir=addslashes($param->Value);		
        if ($noformulir != '') {
            try {               
                $str = "SELECT no_formulir FROM pin WHERE no_formulir='$noformulir'";
                $this->DB->setFieldTable(array('no_formulir'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                
                    throw new Exception ("Nomor Formulir ($noformulir) tidak terdaftar di Portal, silahkan ganti dengan yang lain.");		
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
	public function Go($param,$sender) {	
        if ($this->IsValid) {            
            $no_formulir=addslashes($this->txtNoFormulir->Text);
            $this->redirect('pembayaran.DetailPembayaranFormulir',true,array('id'=>$no_formulir));
        }
	}
	
}
?>