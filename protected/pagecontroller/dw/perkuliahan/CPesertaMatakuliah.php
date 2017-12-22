<?php
prado::using ('Application.MainPageM');
class CPesertaMatakuliah extends MainPageM {	
	public function onLoad($param) {		
		parent::onLoad($param);				
         $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPenyelenggaraan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePesertaMatakuliah'])||$_SESSION['currentPagePesertaMatakuliah']['page_name']!='m.perkuliahan.PesertaMatakuliah') {
				$_SESSION['currentPagePesertaMatakuliah']=array('page_name'=>'m.perkuliahan.PesertaMatakuliah','page_num'=>0,'search'=>false,'InfoMatkul'=>array());
			}  
            $_SESSION['currentPagePesertaMatakuliah']['search']=false;            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();            
            try {                     
                $id=addslashes($this->request['id']);
                $iddosen_wali=$this->iddosen_wali;
                $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');                
                $this->hiddenid->Value=$id;
                $infomatkul=$this->Demik->getInfoMatkul($id,'penyelenggaraan'); 
                if (!isset($infomatkul['idpenyelenggaraan'])) {                                                
                    throw new Exception ("Kode penyelenggaraan dengan id ($id) tidak terdaftar.");		
                }
                $this->Demik->InfoMatkul['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($id," AND iddosen_wali=$iddosen_wali");
                $kjur=$infomatkul['kjur'];        
                $ps=$_SESSION['daftar_jurusan'][$kjur];
                $ta=$this->DMaster->getNamaTA($infomatkul['tahun']);
                $semester=$this->setup->getSemester($infomatkul['idsmt']);
                $text="Program Studi $ps TA $ta Semester $semester";
                
                $this->lblModulHeader->Text=$text;
                $_SESSION['currentPagePesertaMatakuliah']['InfoMatkul']=$infomatkul;                
                $this->tbCmbPs->Enabled=false;
                $this->tbCmbTA->Enabled=false;
                $this->tbCmbSemester->Enabled=false;
                $this->populateData();		
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
                $ex->getMessage();
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
    public function checkKodeMatkul ($sender,$param) {
		$this->idProcess=$sender->getId()=='viewpeserta'?'add':'edit';
        $kmatkul=$param->Value;		
        if ($kmatkul != '') {
            try {   
                $kmatkul=$this->Demik->getIDKurikulum($_SESSION['kjur']).'_'.$kmatkul;                
                $ta=$_SESSION['ta'];
                $idsmt=$_SESSION['semester'];
                $str = "SELECT idpenyelenggaraan FROM penyelenggaraan WHERE kmatkul='$kmatkul' AND  idsmt='$idsmt' AND tahun='$ta'";
                $this->DB->setFieldTable (array('idpenyelenggaraan'));			
                $r= $this->DB->getRecord($str);
                if (isset($r[1])) {                                                    
                    $this->redirect('perkuliahan.PesertaMatakuliah', true, array('id'=>$r[1]['idpenyelenggaraan']));
                }else{
                    throw new Exception ("Kode matakuliah ($kmatkul) tidak diselenggarakan silahkan ganti dengan yang lain.");		
                }                               
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePesertaMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePesertaMatakuliah']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePesertaMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPagePesertaMatakuliah']['search']);
	}
    public function showPesertaMatkul ($sender,$param) {
        if ($this->IsValid){
            
        }
    }
    public function populateData ($search=false) {    
        $iddosen_wali=$this->iddosen_wali;
        $id=$_SESSION['currentPagePesertaMatakuliah']['InfoMatkul']['idpenyelenggaraan'];
        $str = "SELECT vkm.nim,vdm.nama_mhs,vdm.jk,vdm.tahun_masuk,km.batal,k.sah FROM v_krsmhs vkm,krs k, krsmatkul km WHERE k.nim=vdm.nim AND km.idpenyelenggaraan='$id' AND vdm.iddosen_wali=$iddosen_wali";
        if ($search) {            
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,krs k, krsmatkul km WHERE k.nim=vdm.nim AND idpenyelenggaraan='$id' AND vdm.iddosen_wali=$iddosen_wali $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,krs k, krsmatkul km WHERE k.nim=vdm.nim AND km.idpenyelenggaraan='$id' AND vdm.iddosen_wali=$iddosen_wali $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,krs k, krsmatkul km WHERE k.nim=vdm.nim AND km.idpenyelenggaraan='$id' AND vdm.iddosen_wali=$iddosen_wali $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                        
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_krsmhs vkm,krs k, krsmatkul km WHERE k.nim=vdm.nim AND km.idpenyelenggaraan='$id' AND vdm.iddosen_wali=$iddosen_wali",'vdm.nim');
        }		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePesertaMatakuliah']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPagePesertaMatakuliah']['page_num']=0;}		
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