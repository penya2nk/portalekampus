<?php
prado::using ('Application.MainPageK');
prado::using ('Application.MainController');
class CKombiPerTA Extends MainPageK {	
    public static $TotalBiaya=0;
    public static $TotalKeseluruhan=0;
	public function onLoad($param) {
		parent::onLoad($param);				
		$this->createObj('Finance');
        $this->showDMaster=true;
        $this->showKombiPerTA=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKombiPerTA'])||$_SESSION['currentPageKombiPerTA']['page_name']!='k.dmaster.KombiPerTA') {
				$_SESSION['currentPageKombiPerTA']=array('page_name'=>'k.dmaster.KombiPerTA','kelas'=>'A','periode_pembayaran'=>'none');												
			}
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $kelas=$this->DMaster->removeIdFromArray($this->DMaster->getListKelas(),'none');            
			$this->tbCmbKelas->DataSource=$kelas;
			$this->tbCmbKelas->Text=$_SESSION['currentPageKombiPerTA']['kelas'];			
			$this->tbCmbKelas->dataBind();	
            
            $this->cmbPeriodePembayaran->Text=$_SESSION['currentPageKombiPerTA']['periode_pembayaran'];			
			$this->populateData ();	
            $this->setInfoToolbar();
		
		}			
		
	}
    public function setInfoToolbar() {                
        $tahun_masuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		        		        
        $nama_kelas=$this->DMaster->getNamaKelasByID($_SESSION['currentPageKombiPerTA']['kelas']);                    
		$this->lblModulHeader->Text="Tahun Masuk $tahun_masuk Kelas $nama_kelas";        
	}
    public function changeTbKelas ($sender,$param) {				
		$_SESSION['currentPageKombiPerTA']['kelas']=$this->tbCmbKelas->Text;		        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function changeTbTahunMasuk($sender,$param) {    				
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;		        
        $this->setInfoToolbar();
		$this->populateData();
	}    
    public function changePeriodePembayaran($sender,$param) {    				
		$_SESSION['currentPageKombiPerTA']['periode_pembayaran']=$this->cmbPeriodePembayaran->Text;		        
		$this->populateData();
	}
	protected function populateData () {		
		$ta=$_SESSION['tahun_masuk'];		
		$kelas=$_SESSION['currentPageKombiPerTA']['kelas'];	        
		if ($ta == 'none' || $ta == '' || $kelas=='none' || $ta=='' || $this->DB->checkRecordIsExist('tahun','ta',$ta)==false) {									
			$result=array();			
		}else {							
			$total_kombi1=$this->DB->getCountRowsOfTable("kombi_per_ta WHERE tahun='$ta' AND idkelas='$kelas'");
			$total_kombi2=$this->DB->getCountRowsOfTable('kombi');
			if ($total_kombi1 != $total_kombi2) {			
				$result=$this->Finance->getList("kombi_per_ta WHERE tahun='$ta' AND idkelas='$kelas'",array('idkombi'));
				if (isset($result[1])) {
					$str="SELECT idkombi FROM kombi WHERE idkombi NOT IN (SELECT idkombi FROM kombi_per_ta WHERE tahun='$ta' AND idkelas='$kelas')";
					$this->DB->setFieldTable(array('idkombi'));
					$result=$this->DB->getRecord($str);
					while (list($k,$v)=each($result)) {
						$str = "INSERT INTO kombi_per_ta (idkombi_per_ta,idkelas,idkombi,tahun,biaya) VALUES ";
						$str = $str . "(NULL,'$kelas',".$v['idkombi'].",$ta,0)";
						$this->DB->insertRecord ($str);
					}	
				}else {
					$result=$this->getLogic('DMaster')->getList("kombi",array('idkombi'));
					while (list($k,$v)=each($result)) {
						$str = "INSERT INTO kombi_per_ta (idkombi_per_ta,idkelas,idkombi,tahun,biaya) VALUES ";
						$str = $str . "(NULL,'$kelas',".$v['idkombi'].",$ta,0)";
						$this->DB->insertRecord ($str);
					}		
				}
			}
            $periode_pembayaran=$_SESSION['currentPageKombiPerTA']['periode_pembayaran'];            
            if($periode_pembayaran=='semester_sekali') {
                $str_periode_pembayaran=" AND k.periode_pembayaran!='none'";
            }else{  
                $str_periode_pembayaran=$periode_pembayaran=='none' ?'':" AND k.periode_pembayaran='$periode_pembayaran'";
            }
            $str = "SELECT kpt.idkombi_per_ta,k.idkombi,k.nama_kombi,kpt.biaya,k.periode_pembayaran FROM kombi_per_ta kpt,kombi k WHERE  k.idkombi=kpt.idkombi AND tahun=$ta AND kpt.idkelas='$kelas'$str_periode_pembayaran ORDER BY periode_pembayaran,nama_kombi ASC";
            $this->DB->setFieldTable(array('idkombi_per_ta','idkombi','nama_kombi','biaya','periode_pembayaran'));
            $r=$this->DB->getRecord($str);            
			while (list($k,$v)=each($r)) {
                $v['biaya_alias']=$this->Finance->toRupiah($v['biaya']);
                $v['nama_kombi']=  strtoupper($v['nama_kombi']);
                $v['periode_pembayaran']=  strtoupper($v['periode_pembayaran']);
				$result[$k]=$v;
			}									
		}		
		$this->GridS->DataSource=$result;
		$this->GridS->dataBind();
	}
	public function editItem($sender,$param) {                   
        $this->GridS->EditItemIndex=$param->Item->ItemIndex;
        $this->populateData ();        
    }
    public function cancelItem($sender,$param) {                
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();        
    }		
    public function deleteItem($sender,$param) {                
        $id=$this->GridS->DataKeys[$param->Item->ItemIndex];        
        $this->DB->updateRecord("UPDATE kombi_per_ta SET biaya=0 WHERE idkombi_per_ta=$id");
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();
    }  
    public function saveItem($sender,$param) {                        
        $item=$param->Item;
        $id=$this->GridS->DataKeys[$item->ItemIndex];   
        $biaya=$this->Finance->toInteger(addslashes($item->ColumnBiaya->TextBox->Text));                         
        $str = "UPDATE kombi_per_ta SET biaya='$biaya' WHERE idkombi_per_ta=$id";
        $this->DB->updateRecord($str);       
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();
    }
}
class TotalPrice extends MainController
{   
	public function render($writer)
	{	
        $this->createObj('Finance');
        $writer->write("SEMESTERAN : " .$this->Finance->toRupiah(CKombiPerTA::$TotalBiaya));	
	}
}
class TotalKeseluruhan extends MainController
{   
	public function render($writer)
	{	
        $this->createObj('Finance');
        $writer->write("KESELURUHAN : ".$this->Finance->toRupiah(CKombiPerTA::$TotalKeseluruhan));	
	}
}