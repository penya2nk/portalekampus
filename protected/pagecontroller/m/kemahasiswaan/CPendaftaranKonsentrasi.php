<?php
prado::using ('Application.MainPageM');
class CPendaftaranKonsentrasi extends MainPageM {
	public function onLoad($param) {		
		parent::onLoad($param);						
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showPendaftaranKonsentrasi=true;
                        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePendaftaranKonsentrasi'])||$_SESSION['currentPagePendaftaranKonsentrasi']['page_name']!='m.kemahasiswaan.PendaftaranKonsentrasi') {
				$_SESSION['currentPagePendaftaranKonsentrasi']=array('page_name'=>'m.kemahasiswaan.PendaftaranKonsentrasi','page_num'=>0,'search'=>false,'idkonsentrasi'=>'none','DataMHS'=>array());												
			}
            $_SESSION['currentPagePendaftaranKonsentrasi']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            
			$this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$_SESSION['kjur'];
			$this->tbCmbPs->Enabled=true;
			$this->tbCmbPs->dataBind();	
			$this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->populateKonsentrasi();
			$this->populateData();
		}		
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];        
        $_SESSION['currentPagePendaftaranKonsentrasi']['idkonsentrasi']='none';
        $this->populateKonsentrasi();
		$this->populateData();
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
    public function filterKonsentrasi ($sender,$param) {
        $id=$this->getDataKeyField($sender, $this->RepeaterKonsentrasi);
        $_SESSION['currentPagePendaftaranKonsentrasi']['idkonsentrasi']=$id;
        $this->populateKonsentrasi();
        $this->populateData();
    }
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePendaftaranKonsentrasi']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePendaftaranKonsentrasi']['search']);
	}		
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePendaftaranKonsentrasi']['search']=true;
        $this->populateData($_SESSION['currentPagePendaftaranKonsentrasi']['search']);
	} 
    public function populateKonsentrasi () {			
        $datakonsentrasi=$this->DMaster->getListKonsentrasiProgramStudi();        
        $r=array();
        $i=1;        
        while (list($k,$v)=each($datakonsentrasi)) {                        
            if ($v['kjur']==$_SESSION['kjur']){
                $idkonsentrasi=$v['idkonsentrasi'];
                $jumlah = $this->DB->getCountRowsOfTable("pendaftaran_konsentrasi WHERE idkonsentrasi=$idkonsentrasi",'nim');
                $v['jumlah_mhs']=$jumlah > 10000 ? 'lebih dari 10.000' : $jumlah;
                $r[$i]=$v;
                $i+=1;
            }
        }        
        $this->RepeaterKonsentrasi->DataSource=$r;
        $this->RepeaterKonsentrasi->DataBind();
    }
    public function resetKonsentrasi ($sender,$param) {
		$_SESSION['currentPagePendaftaranKonsentrasi']['idkonsentrasi']='none';
        $this->redirect('kemahasiswaan.PendaftaranKonsentrasi',true);
	} 
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {         
            $item->lblStatusPendaftaran->Text=$this->DMaster->getStatusPendaftaranKonsentrasi($item->DataItem['status_daftar']);
            switch ($item->DataItem['status_daftar']) {
                case 0 :
                    $cssclass='label label-info';
                break;
                case 1 :
                    $cssclass='label label-success';
                    $item->btnRepeaterApproved->Enabled=false;                                        
                    $item->btnRepeaterApproved->CssClass="table-link default";
                break;
            }
            $item->lblStatusPendaftaran->CssClass=$cssclass;
        }
    }
	public function populateData ($search=false) {			
        $kjur=$_SESSION['kjur'];        
        if ($search) {
            $str = "SELECT vdm.nim,vdm.nama_mhs,pk.jumlah_sks,pk.kjur,pk.idkonsentrasi,pk.status_daftar FROM v_datamhs vdm,pendaftaran_konsentrasi pk WHERE pk.nim=vdm.nim";			
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa=" AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,pendaftaran_konsentrasi pk WHERE pk.nim=vdm.nim $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;                
                case 'nama' :
                    $clausa=" AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,pendaftaran_konsentrasi pk WHERE pk.nim=vdm.nim $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{           
            $idkonsentrasi=$_SESSION['currentPagePendaftaranKonsentrasi']['idkonsentrasi'];
            $str_konsentrasi = $idkonsentrasi == 'none'?'':" AND pk.idkonsentrasi=$idkonsentrasi";            
            $jumlah_baris=$this->DB->getCountRowsOfTable("pendaftaran_konsentrasi pk WHERE kjur=$kjur $str_konsentrasi",'nim');		            
            $str = "SELECT vdm.nim,vdm.nama_mhs,pk.jumlah_sks,pk.kjur,pk.idkonsentrasi,pk.status_daftar FROM v_datamhs vdm,pendaftaran_konsentrasi pk WHERE pk.nim=vdm.nim AND pk.kjur='$kjur' $str_konsentrasi";			
        }		
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePendaftaranKonsentrasi']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePendaftaranKonsentrasi']['page_num']=0;}
        $str = "$str ORDER BY status_daftar ASC,tanggal_daftar DESC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('nim','nama_mhs','jumlah_sks','kjur','idkonsentrasi','status_daftar'));
		$r = $this->DB->getRecord($str,$offset+1);	        
        
        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }     
    public function approvedFromRepeater($sender,$param) {
        $nim=$this->getDataKeyField($sender,$this->RepeaterS);
        $idkonsentrasi=$sender->CommandParameter;
        $this->DB->query('BEGIN');
        $str = "UPDATE pendaftaran_konsentrasi SET status_daftar=1 WHERE nim='$nim'";        
        if ($this->DB->updateRecord($str)) {            
            $str = "UPDATE register_mahasiswa SET idkonsentrasi=$idkonsentrasi WHERE nim='$nim'";        
            $this->DB->updateRecord($str);
            $this->DB->query('COMMIT');
            $this->redirect('kemahasiswaan.PendaftaranKonsentrasi',true);
        }else{
            $this->DB->query('ROLLBACK');
        }
        
    }
}