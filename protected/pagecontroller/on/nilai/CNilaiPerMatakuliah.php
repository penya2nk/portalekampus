<?php
prado::using ('Application.MainPageON');
class CNilaiPerMatakuliah extends MainPageON {	
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuAkademikNilai=true;
        $this->showNilaiPerMahasiswa=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageNilaiPerMatakuliah'])||$_SESSION['currentPageNilaiPerMatakuliah']['page_name']!='on.perkuliahan.NilaiPerMatakuliah') {
				$_SESSION['currentPageNilaiPerMatakuliah']=array('page_name'=>'on.perkuliahan.NilaiPerMatakuliah','page_num'=>0,'search'=>false,'InfoMatkul'=>array());
			}  
            $_SESSION['currentPageNilaiPerMatakuliah']['search']=false;            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();            
            try {                     
                $id=addslashes($this->request['id']);
                $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');                
                $this->hiddenid->Value=$id;
                $infomatkul=$this->Demik->getInfoMatkul($id,'penyelenggaraan'); 
                if (!isset($infomatkul['idpenyelenggaraan'])) {                                                
                    throw new Exception ("Kode penyelenggaraan dengan id ($id) tidak terdaftar.");		
                }
                $kjur=$infomatkul['kjur'];        
                $ps=$_SESSION['daftar_jurusan'][$kjur];
                $ta=$this->DMaster->getNamaTA($infomatkul['tahun']);
                $semester=$this->setup->getSemester($infomatkul['idsmt']);
                $text="Program Studi $ps TA $ta Semester $semester";
                
                $this->lblModulHeader->Text=$text;
                $_SESSION['currentPageNilaiPerMatakuliah']['InfoMatkul']=$infomatkul;                
                $this->tbCmbPs->Enabled=false;
                $this->tbCmbTA->Enabled=false;
                $this->tbCmbSemester->Enabled=false;
                $this->populateDataPeserta();		
            } catch (Exception $ex) {
                $this->idProcess='view';        
                
                $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
                $this->tbCmbPs->Text=$_SESSION['kjur'];			
                $this->tbCmbPs->dataBind();	
                
                $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
                $this->tbCmbTA->DataSource=$ta;					
                $this->tbCmbTA->Text=$_SESSION['ta'];						
                $this->tbCmbTA->dataBind();

                $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
                $this->tbCmbSemester->DataSource=$semester;
                $this->tbCmbSemester->Text=$_SESSION['semester'];
                $this->tbCmbSemester->dataBind();
                $this->lblModulHeader->Text=$this->getInfoToolbar();
                $this->populateData();
                $this->errormessage->Text='Error: '.$ex->getMessage();
            }
		}		
	}	
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function changeTbTA ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
	}	
	public function changeTbSemester ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();		
	}
    public function changeTbPs ($sender,$param) {		
        $this->idProcess='view';
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();        
	}    
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNilaiPerMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageNilaiPerMatakuliah']['search']);
	}
    public function searchRecord ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['currentPageNilaiPerMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPageNilaiPerMatakuliah']['search']);
	}       
    public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$this->Demik->getIDKurikulum($kjur);
        
        if ($search) {
            $txtsearch=  addslashes($this->txtKriteria->Text);           
            switch ($this->cmbKriteria->Text) {                                
                case 'kmatkul' :
                    $clausa="AND kmatkul LIKE '%$txtsearch%'";
                break;
                case 'nmatkul' :
                    $clausa="AND nmatkul LIKE '%$txtsearch%'";
                break;
                case 'nidn' :
                    $clausa="AND nidn='$txtsearch'";
                break;
                case 'nama_dosen' :
                    $clausa="AND nama_dosen LIKE '%$txtsearch%'";
                break;
            }            			
        }
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,nama_dosen,iddosen FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur $clausa ORDER BY semester ASC,kmatkul ASC";        
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','nama_dosen','iddosen'));			
        $r= $this->DB->getRecord($str);
        
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($v['idpenyelenggaraan']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}
    public function populateDataPeserta ($search=false) {      
        $id=$_SESSION['currentPageNilaiPerMatakuliah']['InfoMatkul']['idpenyelenggaraan'];
        $str = "SELECT vkm.nim,vdm.nama_mhs,vdm.jk,vdm.tahun_masuk,vkm.batal,vkm.sah FROM v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id'";
        if ($search) {            
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                        
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id'",'vdm.nim');
        }		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageNilaiPerMatakuliah']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageNilaiPerMatakuliah']['page_num']=0;}		
        $str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('nim','nama_mhs','jk','tahun_masuk','batal','sah'));	
		$r=$this->DB->getRecord($str,$offset+1);
        $result=array();
        while (list($k,$v)=each($r)) {
            $status='belum disahkan';
            if ($v['sah']==1 && $v['batal']==0) {
                $status='sah';
            }elseif($v['sah']==1 && $v['batal']==1){
                $status='batal';
            }
            $v['status']=$status;
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}

}
?>