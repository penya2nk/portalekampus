<?php
prado::using ('Application.MainPageM');
class CPerwalian extends MainPageM {	
    private $DataMHS;
	public function onLoad ($param) {
		parent::onLoad($param);
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showPerwalian=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPagePerwalian'])||$_SESSION['currentPagePerwalian']['page_name']!='m.kemahasiswaan.perwalian') {
				$_SESSION['currentPagePerwalian']=array('page_name'=>'m.kemahasiswaan.perwalian','page_num'=>0,'search'=>false,'iddosen_wali'=>'none');												
			}
            $_SESSION['currentPagePerwalian']['search']=false;
            $daftar_dw=$this->DMaster->getListDosenWali();
            $daftar_dw['none']='BELUM PUNYA DOSEN WALI';
            $this->cmbDosenWali->DataSource=$daftar_dw;
            $this->cmbDosenWali->Text=$_SESSION['currentPagePerwalian']['iddosen_wali'];
            $this->cmbDosenWali->dataBind();	  

            $this->populateData();
		
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePerwalian']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePerwalian']['search']);
	}
    public function changeDosenWali($sender,$param) {
        $_SESSION['currentPagePerwalian']['iddosen_wali']=$this->cmbDosenWali->Text;
        $this->populateData();
    }
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePerwalian']['search']=true;
		$this->populateData($_SESSION['currentPagePerwalian']['search']);
	}
	protected function populateData ($search=false) {
        $iddosen_wali=$_SESSION['currentPagePerwalian']['iddosen_wali'];
        $str_dw = ($iddosen_wali == 'none') ? " WHERE (iddosen_wali='' OR iddosen_wali=0)" : " WHERE iddosen_wali=$iddosen_wali";
		if ($search) {       
            $str = "SELECT nim,nirm,nama_mhs,tahun_masuk FROM v_datamhs vdm$str_dw";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_record=$this->DB->getCountRowsOfTable("v_datamhs vdm$str_dw $clausa",'vdm.nim'); 
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_record=$this->DB->getCountRowsOfTable("v_datamhs vdm$str_dw $clausa",'vdm.nim'); 
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_record=$this->DB->getCountRowsOfTable("v_datamhs vdm$str_dw $clausa",'vdm.nim'); 
                    $str = "$str $clausa";
                break;
            }
        }else{
            $jumlah_record=$this->DB->getCountRowsOfTable("v_datamhs vdm$str_dw",'vdm.nim');
            $str = "SELECT nim,nirm,nama_mhs,tahun_masuk FROM v_datamhs vdm$str_dw";
        }
		$this->RepeaterS->VirtualItemCount=$jumlah_record;		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePerwalian']['page_num'];
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePerwalian']['page_num']=0;}
		$str = "$str ORDER BY vdm.tahun_masuk DESC,vdm.nama_mhs ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable (array('nim','nirm','nama_mhs','tahun_masuk'));
		$r=$this->DB->getRecord($str,$offset+1);		
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
    public function gantiPA($sender,$param) {	
        $nim=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->idProcess='add';
        
        $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,idkelas,k_status FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE vdm.nim='$nim'";
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status','idsmt','tahun','tasmt','sah'));
        $r=$this->DB->getRecord($str);	           
        $datamhs=$r[1];
        
        $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
        $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
        $this->DataMHS=$datamhs;
        
        $this->hiddennim->Value=$nim;
        $daftar_dw=$this->DMaster->getListDosenWali();
        $this->cmbAddDW->DataSource=$daftar_dw;
        $this->cmbAddDW->Text=$datamhs['iddosen_wali'];
        $this->cmbAddDW->dataBind();
	}
    public function getDataMHS($idx) {		        
        return $this->DataMHS[$idx];
    }
	
	public function saveData($sender,$param) {		
		if ($this->IsValid) {
			$iddosen_wali=$this->cmbAddDW->Text;
			$nim=$this->hiddennim->Value;
			$str="UPDATE register_mahasiswa SET iddosen_wali='$iddosen_wali' WHERE nim='$nim'";		
			$this->DB->updateRecord($str);
			$this->redirect('kemahasiswaan.Perwalian',true);
		}
	}
	
}
?>