<?php
prado::using ('Application.MainPageSA');
class CProdi extends MainPageSA {	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showSubMenuLembaga=true;
        $this->showProdi=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageProdi'])||$_SESSION['currentPageProdi']['page_name']!='sa.dmaster.Prodi') {
				$_SESSION['currentPageProdi']=array('page_name'=>'sa.dmaster.Prodi','page_num'=>0,'search'=>false);
			}
            $this->DB->insertRecord("REPLACE INTO program_studi SET kjur=0,kode_epsbed=0,nama_ps=0,nama_ps_alias=0,kjenjang=0,konsentrasi=0,idkur=0,iddosen=0");
            $this->populateData ();			
		}
	}
	protected function populateData ($search=false) {        
        $str = "SELECT ps.kjur,ps.kode_epsbed,ps.nama_ps,ps.nama_ps_alias,js.njenjang,ps.konsentrasi,ps.idkur,CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen FROM program_studi ps LEFT JOIN jenjang_studi js ON (js.kjenjang=ps.kjenjang) LEFT JOIN dosen d ON (d.iddosen=ps.iddosen) WHERE kjur!=0 ORDER BY kjur ASC";	
       				
        $this->DB->setFieldTable(array('kjur','kode_epsbed','nama_ps','nama_ps_alias','njenjang','konsentrasi','idkur','nama_dosen'));
		$r = $this->DB->getRecord($str);       
        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}
	public function addProcess ($sender,$param) {
        $this->idProcess='add';	 
        $this->cmbAddJenjang->dataSource=$this->DMaster->getListJenjang ();
        $this->cmbAddJenjang->dataBind();                   

        $daftar_dosen=$this->DMaster->getDaftarDosen();
        $this->cmbAddKaprodi->DataSource=$daftar_dosen;
        $this->cmbAddKaprodi->DataBind();
    }
    public function checkPS ($sender,$param) {    
        $this->idProcess=$sender->getId()=='addkodeps'?'add':'edit';
        $kjur=$param->Value;
        if ($kjur != '') {
            try {   
                if ($this->hiddenid->Value!=$kjur) {                                                            
                    if ($this->DB->checkRecordIsExist('kjur','program_studi',$kjur)){                                
                        throw new Exception ("Kode P.S ($kjur) sudah tidak tersedia silahkan ganti dengan yang lain.");        
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }   
        }
    }
    public function checkKodePSForlap ($sender,$param) {    
        $this->idProcess=$sender->getId()=='addkodepsforlap'?'add':'edit';
        $kjur=$param->Value;
        if ($kjur != '') {
            try {   
                if ($this->hiddenkodepsforlap->Value!=$kjur) {                                                            
                    if ($this->DB->checkRecordIsExist('kode_epsbed','program_studi',$kjur)){                                
                        throw new Exception ("Kode P.S di Forlap ($kjur) sudah tidak tersedia silahkan ganti dengan yang lain.");        
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }   
        }
    }
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            $kjur=addslashes($this->txtAddKodePS->Text);
            $kjur_forlap=addslashes($this->txtAddKodePSForlap->Text);
            $nama_ps=addslashes($this->txtAddNama->Text);
            $akronim_ps=addslashes($this->txtAddNamaAkronim->Text);
            $kjenjang=addslashes($this->cmbAddJenjang->Text);
            $konsentrasi=addslashes($this->txtAddKonsentrasi->Text);
            $iddosen=$this->cmbAddKaprodi->Text;

            $str = "INSERT INTO program_studi SET kjur=$kjur,kode_epsbed='$kjur_forlap',nama_ps='$nama_ps',nama_ps_alias='$akronim_ps',kjenjang='$kjenjang',konsentrasi='$konsentrasi',iddosen=$iddosen";
            $this->DB->insertRecord($str);
            if ($this->Application->Cache) {                        
                $str = 'SELECT ps.kjur,ps.kode_epsbed,ps.nama_ps,ps.kjenjang,js.njenjang,konsentrasi FROM program_studi ps,jenjang_studi js WHERE js.kjenjang=ps.kjenjang AND ps.kjur!=0';
                $this->DB->setFieldTable(array('kjur','kode_epsbed','nama_ps','njenjang','konsentrasi'));
                $dataitem = $this->DB->getRecord($str);                
                $this->Application->Cache->set('listprodi',$dataitem);            
            }
            $this->Redirect('dmaster.Prodi',true);
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';  
        $kjur=$this->getDataKeyField($sender,$this->RepeaterS);  
        $this->hiddenid->Value=$kjur;
        $str = "SELECT kjur,kode_epsbed,nama_ps,nama_ps_alias,kjenjang,konsentrasi,iddosen FROM program_studi WHERE kjur=$kjur";
        $this->DB->setFieldTable(array('kjur','kode_epsbed','nama_ps','nama_ps_alias','kjenjang','konsentrasi','iddosen'));
        $r=$this->DB->getRecord($str);

        $this->txtEditKodePS->Text=$r[1]['kjur'];
        $this->txtEditKodePSForlap->Text=$r[1]['kode_epsbed'];
        $this->hiddenkodepsforlap->Value=$r[1]['kode_epsbed'];
        $this->txtEditNama->Text=$r[1]['nama_ps'];
        $this->txtEditNamaAkronim->Text=$r[1]['nama_ps_alias'];       
        $this->txtEditKonsentrasi->Text=$r[1]['konsentrasi'];

        $this->cmbEditJenjang->dataSource=$this->DMaster->getListJenjang ();
        $this->cmbEditJenjang->Text=$r[1]['kjenjang'];
        $this->cmbEditJenjang->dataBind();       

        $daftar_dosen=$this->DMaster->getDaftarDosen();
        $this->cmbEditKaprodi->DataSource=$daftar_dosen;
        $this->cmbEditKaprodi->DataBind();            
    }
    public function updateData ($sender,$param) {
        if ($this->IsValid) {
            $id=$this->hiddenid->Value;
            $kjur=addslashes($this->txtEditKodePS->Text);
            $kjur_forlap=addslashes($this->txtEditKodePSForlap->Text);
            $nama_ps=addslashes($this->txtEditNama->Text);
            $akronim_ps=addslashes($this->txtEditNamaAkronim->Text);
            $kjenjang=addslashes($this->cmbEditJenjang->Text);
            $konsentrasi=addslashes($this->txtEditKonsentrasi->Text);
            $iddosen=$this->cmbEditKaprodi->Text;

            $str = "UPDATE program_studi SET kjur=$kjur,kode_epsbed='$kjur_forlap',nama_ps='$nama_ps',nama_ps_alias='$akronim_ps',kjenjang='$kjenjang',konsentrasi='$konsentrasi',iddosen=$iddosen WHERE kjur=$id";
            $this->DB->insertRecord($str);
            if ($this->Application->Cache) {                        
                $str = 'SELECT ps.kjur,ps.kode_epsbed,ps.nama_ps,ps.kjenjang,js.njenjang,konsentrasi FROM program_studi ps,jenjang_studi js WHERE js.kjenjang=ps.kjenjang AND ps.kjur!=0';
                $this->DB->setFieldTable(array('kjur','kode_epsbed','nama_ps','njenjang','konsentrasi'));
                $dataitem = $this->DB->getRecord($str);                
                $this->Application->Cache->set('listprodi',$dataitem);            
            }
            $this->Redirect('dmaster.Prodi',true);
        }
    }
    public function deleteRecord ($sender,$param) {        
        $kjur=$this->getDataKeyField($sender,$this->RepeaterS);          
        if ($this->DB->checkRecordIsExist('kjur1','formulir_pendaftaran_temp',$kjur," OR kjur2=$kjur")) {
            $this->lblHeaderMessageError->Text='Menghapus Program Studi';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus program studi dengan ID ($kjur) karena sedang digunakan di formulir pendaftaran.";
            $this->modalMessageError->Show();
        }elseif ($this->DB->checkRecordIsExist('kjur1','formulir_pendaftaran',$kjur," OR kjur2=$kjur")) {
            $this->lblHeaderMessageError->Text='Menghapus Program Studi';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus program studi dengan ID ($kjur) karena sedang digunakan di formulir pendaftaran.";
            $this->modalMessageError->Show();
        }else{
            if ($this->Application->Cache) {                        
                $str = 'SELECT ps.kjur,ps.kode_epsbed,ps.nama_ps,ps.kjenjang,js.njenjang,konsentrasi FROM program_studi ps,jenjang_studi js WHERE js.kjenjang=ps.kjenjang AND ps.kjur!=0';
                $this->DB->setFieldTable(array('kjur','kode_epsbed','nama_ps','njenjang','konsentrasi'));
                $dataitem = $this->DB->getRecord($str);                
                $this->Application->Cache->set('listprodi',$dataitem);            
            }         
            $this->DB->deleteRecord("program_studi WHERE kjur='$kjur'");
            $this->Redirect('dmaster.Prodi',true);
        }        
    }   
}