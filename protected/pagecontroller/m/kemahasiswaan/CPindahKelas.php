<?php
prado::using ('Application.MainPageM');
class CPindahKelas Extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showPindahKelas=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePindahKelas'])||$_SESSION['currentPagePindahKelas']['page_name']!='m.kemahasiswaan.PindahKelas') {
				$_SESSION['currentPagePindahKelas']=array('page_name'=>'m.kemahasiswaan.PindahKelas','page_num'=>0,'search'=>false);
			}   
			$_SESSION['currentPagePindahKelas']['search']=false;
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();			
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->setInfoToolbar();
            $this->populateData();	

		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
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
		$_SESSION['currentPage']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
	public function populateData() {
		$ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPage']['page_num'];
		$this->RepeaterS->VirtualItemCount=$this->DB->getCountRowsOfTable("v_datamhs vdm,pindahkelas pk WHERE vdm.nim=pk.nim AND pk.tahun='$ta' AND pk.idsmt='$idsmt'");	
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPage']['page_num']=0;}
		$str = "SELECT pk.idpindahkelas,pk.no_surat,pk.tanggal,pk.nim,vdm.nama_mhs,vdm.tahun_masuk,pk.idkelas_lama,pk.idkelas_baru FROM v_datamhs vdm,pindahkelas pk WHERE vdm.nim=pk.nim AND pk.tahun='$ta' AND pk.idsmt='$idsmt' ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idpindahkelas','no_surat','tanggal','nim','nama_mhs','tahun_masuk','idkelas_lama','idkelas_baru'));
		$r=$this->DB->getRecord($str,$offset+1);
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	public function processDataBound ($sender,$param) {			
		$item=$param->Item;
		if ($item->ItemType == 'Item' || $item->ItemType=='AlternatingItem') {
            $this->Demik->setDataMHS(array('nim'=>$item->DataItem['nim']));
			$datadulang=$this->Demik->getDataDulang($_SESSION['semester'],$_SESSION['ta']);
            if (isset($datadulang['iddulang'])) {
                $item->btnDelete->Enabled=false;
				$item->btnEdit->Enabled=false;
            }else{
                 $item->btnDelete->Attributes->OnClick="if(!confirm('Anda ingin menghapus data pindah kelas dan mengembalikannya ke semula?')) return false;";
            }
		}	
	}
	public function checkNIM ($sender,$param) {
        $nim=addslashes($param->Value);
		try {
			$ta=$_SESSION['ta'];
			$idsmt=$_SESSION['semester'];					
			if ($this->DB->checkRecordIsExist('nim','pindahkelas',$nim," AND idsmt='$idsmt' AND tahun='$ta'")){
                throw new Exception ("Nim ($nim) pada T.A dan Semester ini, telah melakukan pindah kelas !!!");
            }
            $this->Demik->setDataMHS(array('nim'=>$nim));
			$datadulang=$this->Demik->getDataDulang($_SESSION['semester'],$_SESSION['ta']);
            if (isset($datadulang['iddulang'])) {
                throw new Exception ("Mahasiswa Dengan NIM ($nim) telah daftar ulang di T.A dan Semester ini.");
            }
            if ($idsmt == 3) {
                if ($this->DB->checkRecordIsExist('nim','transaksi_sp',$nim," AND idsmt='$idsmt' AND tahun='$ta' LIMIT 1")){
                    throw new Exception ("Nim ($nim) pada T.A dan Semester ini, telah melakukan transaksi keuangan");
                }
            }else{
                if ($this->DB->checkRecordIsExist('nim','transaksi',$nim," AND idsmt='$idsmt' AND tahun='$ta' LIMIT 1")){
                    throw new Exception ("Nim ($nim) pada T.A dan Semester ini, telah melakukan transaksi keuangan");
                }
            }
		}catch (Exception $e) {
			$param->IsValid=false;
            $sender->ErrorMessage=$e->getMessage();
            $this->populateData();
		}	
	}
	public function Go($sender,$param) {
		if ($this->Page->IsValid) {
			$this->idProcess='add';		
            $nim=addslashes($this->txtNIM->Text);
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
            $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
            $r=$this->DB->getRecord($str);	
            
            $datamhs=$r[1];  
            $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
            $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
            $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
            $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
            $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
            
            $this->Demik->setDataMHS($datamhs);
            
            $this->hiddennim->Value=$datamhs['nim'];			
            $idkelas=$datamhs['idkelas'];
            $this->hiddenkelaslama->Value=$idkelas;
            $daftar_kelas=$this->DMaster->removeIdFromArray($this->DMaster->getListKelas (),$datamhs['idkelas']);
            $this->cmbAddKelasBaru->DataSource=$daftar_kelas;
            $this->cmbAddKelasBaru->dataBind();	
		}
	}
    public function checkNoSurat ($sender,$param) {
        $this->idProcess=$sender->getId()=='addPindah'?'add':'edit';
        $no_surat=addslashes($param->Value);
		try {
			if ($no_surat != '') {
                if ($this->hiddennosurat->Value!=$no_surat) {                                                            
                    if ($this->DB->checkRecordIsExist('no_surat','pindahkelas',$no_surat)){                                 
                        throw new Exception ("No. Surat ($no_surat) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                } 
            }
		}catch (Exception $e) {
			$param->IsValid=false;
            $sender->ErrorMessage=$e->getMessage();
		}	
	}
	public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {
			$nim=$this->hiddennim->Value;
			$kelas_lama=$this->hiddenkelaslama->Value;
			$ta=$_SESSION['ta'];
			$idsmt=$_SESSION['semester'];
            $tanggal=date ('Y-m-d',$this->cmbAddTanggal->TimeStamp);
			$kelas_baru=$this->cmbAddKelasBaru->Text;
			$no_surat=$this->txtAddNoSurat->getSafeText();
			$ket=$this->txtAddKeterangan->getSafeText();			
			$this->DB->query ('BEGIN');			
			$str = "UPDATE register_mahasiswa SET idkelas='$kelas_baru' WHERE nim='$nim'";
			if ($this->DB->updateRecord($str)) {
				$str = "INSERT INTO pindahkelas (idpindahkelas,nim,idkelas_lama,idkelas_baru,tahun,idsmt,tanggal,no_surat,keterangan) VALUES (NULL,'$nim','$kelas_lama','$kelas_baru','$ta','$idsmt','$tanggal','$no_surat','$ket')";
				$this->DB->insertRecord ($str);										
				$this->DB->query ('COMMIT');
			}else {
				$this->DB->query ('ROLLBACK');
			}
			$this->redirect('kemahasiswaan.PindahKelas',true);
		}		
	}
    public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$idpindahkelas = $this->getDataKeyField($sender,$this->RepeaterS);
		$nim=$sender->CommandParameter;
        $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
        $r=$this->DB->getRecord($str);	

        $datamhs=$r[1];  
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
        $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);

        $this->Demik->setDataMHS($datamhs);
        
		$r = $this->Demik->getList("pindahkelas WHERE idpindahkelas='$idpindahkelas'",array('idkelas_lama','idkelas_baru','tanggal','no_surat','keterangan'));
		$r=$r[1];				
		$this->hiddennim->Value=$nim;
		$this->hiddenid->Value=$idpindahkelas;		
        
        $daftar_kelas=$this->DMaster->removeIdFromArray($this->DMaster->getListKelas (),$r['idkelas_lama']);
        $this->cmbEditKelasBaru->DataSource=$daftar_kelas;
        $this->cmbEditKelasBaru->dataBind();	
		$this->cmbEditKelasBaru->Text=$r['idkelas_baru'];
        
		$this->cmbEditTanggal->Text=$this->TGL->tanggal('d-m-Y',$r['tanggal']);
		$this->hiddennosurat->Value=$r['no_surat'];
		$this->txtEditNoSurat->Text=$r['no_surat'];
		$this->txtEditKeterangan->Text=$r['keterangan'];
		
	}
	public function updateData ($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenid->Value;
			$tanggal=date ('Y-m-d',$this->cmbEditTanggal->TimeStamp);
			$kelas_baru=$this->cmbEditKelasBaru->Text;
			$no_surat=$this->txtEditNoSurat->getSafeText();
			$ket=$this->txtEditKeterangan->getSafeText();
			$str = "UPDATE pindahkelas SET idkelas_baru='$kelas_baru',tanggal='$tanggal',no_surat='$no_surat',keterangan='$ket' WHERE idpindahkelas=$id";
			$this->DB->query ('BEGIN');
			if ($this->DB->updateRecord ($str)) {
                $nim=$this->hiddennim->Value;
				$str = "UPDATE register_mahasiswa SET idkelas='$kelas_baru' WHERE nim='$nim'";
				$this->DB->query ('COMMIT');
			}else {
				$this->DB->query ('ROLLBACK');
			}
			$this->redirect('kemahasiswaan.PindahKelas',true);
		}
	}	
	public function deleteRecord ($sender,$param) {
		$idpindahkelas= $this->getDataKeyField($sender,$this->RepeaterS);
		$id=$sender->CommandParameter;			
		$this->DB->query ('BEGIN');
		if ($this->DB->deleteRecord("pindahkelas WHERE idpindahkelas='$idpindahkelas'")) {
			$id=explode('_',$id);
			$nim=$id[0];
			$kelas_lama=$id[1];
			$this->DB->query ('BEGIN');			
			$str = "UPDATE register_mahasiswa SET idkelas='$kelas_lama' WHERE nim='$nim'";
            $this->DB->updateRecord($str);
			$this->DB->query ('COMMIT');
		}else {
			$this->DB->query ('ROLLBACK');
		}
        $this->redirect('kemahasiswaan.PindahKelas',true);
	}
}