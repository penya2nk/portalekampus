<?php
prado::using ('Application.MainPageDW');
class CPKRS extends MainPageDW {		
	/**
	* state totalSKS
	*/
	public $totalSks=0;	
	/**
	* state jumlah Matkul
	*/
	public $jumlahMatkul=0;	
	/**
	* state dataKrs
	*/
	public $dataKrs;
	
	public function onLoad($param) {
		parent::onLoad($param);	
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPKRS = true;
		$this->createObj('KRS');
			
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPagePKRS'])||$_SESSION['currentPagePKRS']['page_name']!='dw.perkuliahan.PKRS') {					
                $_SESSION['currentPagePKRS']=array('page_name'=>'dw.perkuliahan.PKRS','page_num'=>0,'DataKRS'=>array(),'DataMHS'=>array());												
            }
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
            $this->tbCmbTA->Text=$_SESSION['ta'];
            $this->tbCmbTA->dataBind();			

            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
            $this->tbCmbSemester->DataSource=$semester;
            $this->tbCmbSemester->Text=$_SESSION['semester'];
            $this->tbCmbSemester->dataBind();

            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

            $this->setInfoToolbar();
            $this->populateData();	
            				
		}	
	}
	public function setInfoToolbar() {   
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);		
		$this->lblModulHeader->Text="T.A $ta Semester $semester";        
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePKRS']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePKRS']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePKRS']['search']=true;
		$this->populateData($_SESSION['currentPagePKRS']['search']);
	}
	private function populateData ($search=false) {
		$iddosen_wali=$this->iddosen_wali;
		$ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
         $str = "SELECT p.nim,vdm.nama_mhs,vdm.jk,vdm.tahun_masuk,vp.nmatkul,vp.sks,p.tambah,p.hapus,p.batal,p.sah,p.tanggal FROM pkrs p,v_datamhs vdm,v_penyelenggaraan vp WHERE p.nim=vdm.nim AND p.idpenyelenggaraan=vp.idpenyelenggaraan AND vp.idsmt='$idsmt' AND vp.tahun='$ta' AND vdm.iddosen_wali=$iddosen_wali";
          
        if ($search) {
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";                    
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";                    
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";                    
                break;
            }
            $jumlah_baris = $this->DB->getCountRowsOfTable("pkrs p,v_datamhs vdm,penyelenggaraan vp WHERE p.nim=vdm.nim AND p.idpenyelenggaraan=vp.idpenyelenggaraan AND vp.idsmt='$idsmt' AND vp.tahun='$ta' AND vdm.iddosen_wali=$iddosen_wali $clausa");
        }else{
            $jumlah_baris = $this->DB->getCountRowsOfTable("pkrs p,v_datamhs vdm,penyelenggaraan vp WHERE p.nim=vdm.nim AND p.idpenyelenggaraan=vp.idpenyelenggaraan AND vp.idsmt='$idsmt' AND vp.tahun='$ta' AND vdm.iddosen_wali=$iddosen_wali");
        }        
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePKRS']['page_num']=0;}
        $str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";	
        $this->DB->setFieldTable(array('nim','nama_mhs','jk','tahun_masuk','nmatkul','sks','tambah','hapus','batal','sah','tanggal'));
        $r = $this->DB->getRecord($str,$offset+1);
        $result=array();
        while (list($k,$v)=each($r)) {
            $ket = 'N.A';
            if ($v['tambah'] == 1) {
                $ket = 'TAMBAH';
            }if ($v['hapus'] == 1) {
                $ket = 'HAPUS';
            }if ($v['batal'] == 1) {
                $ket = 'BATAL';
            }if ($v['sah'] == 1) {
                $ket = 'SAH';
            }
            $v['ket']=$ket;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
        
	}
	
	public function checkNIM ($sender,$param) {		
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $nim=addslashes($param->Value);
        try {
            if ($nim != '') {
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {
                    throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                }
                $datamhs=$r[1];
                if ($datamhs['iddosen_wali'] != $this->iddosen_wali) {
                    throw new Exception("Mahasiswa Dengan NIM ($nim) diluar perwalian Anda.");
                }
                $this->KRS->setDataMHS($datamhs);
                $this->KRS->getKRS($ta,$idsmt);
                $datakrs=$this->KRS->DataKRS['krs'];
                if (!isset($datakrs['idkrs'])) {                    
                    $ta=$this->DMaster->getNamaTA($ta);		
                    $semester = $this->setup->getSemester($idsmt);
                    throw new Exception ("Mahasiswa Dengan NIM ($nim) belum melakukan KRS di T.A ($ta) Semester $semester !!!");
                }
                if (!$datakrs['sah']) {  
                    throw new Exception ("KRS Mahasiswa Dengan NIM ($nim) di T.A ($ta) Semester $semester belum disahkan !!!");
                }
                $kelas=$this->KRS->getKelasMhs();																	            
                $datamhs['nkelas']=($kelas['nkelas']=='')?'Belum ada':$kelas['nkelas'];	
                $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];

                $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
                $datamhs['nama_dosen']=$nama_dosen;
                
                $_SESSION['currentPagePKRS']['DataMHS']=$datamhs;

                $_SESSION['currentPagePKRS']['DataKRS']=$this->KRS->DataKRS;
                
            }
        }catch(Exception $e) {			
            $sender->ErrorMessage=$e->getMessage();				
            $param->IsValid=false;			
		}

	}	
    
	public function isiPKRS ($sender,$param) {	
		if ($this->IsValid) {
            $this->createObj('Finance');
            $this->createObj('Nilai');
            $krs=$_SESSION['currentPagePKRS']['DataKRS']['krs'];          
            $krs['tahun_masuk']=$_SESSION['currentPagePKRS']['DataMHS']['tahun_masuk'];
            $krs['semester_masuk']=$_SESSION['currentPagePKRS']['DataMHS']['semester_masuk'];
            $krs['kjur']=$_SESSION['currentPagePKRS']['DataMHS']['kjur'];
            $this->Nilai->setDataMHS($krs);            
            $idkrs=$krs['idkrs'];
            $tahun=$krs['tahun'];
            $idsmt=$krs['idsmt'];            
            if ($idsmt==3) {  
                $this->Finance->setDataMHS($krs);
                $maxSKS=$this->Finance->getSKSFromSP($tahun,$idsmt);
                $this->Nilai->getKHSBeforeCurrentSemester($tahun,$idsmt);
                $krs['ipstasmtbefore']=$this->Nilai->getIPS();
            }else{
               $datadulangbefore=$this->Nilai->getDataDulangBeforeCurrentSemester($idsmt,$tahun);
               if ($datadulangbefore['k_status']=='C') {
                   $maxSKS=$this->setup->getSettingValue('jumlah_sks_krs_setelah_cuti'); 
                   $krs['ipstasmtbefore']='N.A (Status Cuti)';
               }else{
                   $maxSKS=$this->Nilai->getMaxSKS($tahun,$idsmt);
                   $krs['ipstasmtbefore']=$this->Nilai->getIPS();
               }               
            }            
            $krs['maxSKS']=$maxSKS;
            $_SESSION['currentPagePKRS']['DataKRS']['krs']=$krs;
            $this->redirect ('perkuliahan.DetailPKRS',true,array('id'=>$idkrs));
        }		
	}	
}

?>