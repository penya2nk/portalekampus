<?php
prado::using ('Application.MainPageDW');
class CDetailPKRS extends MainPageDW {		
	/**
	* total SKS
	*/
	public static $totalSKS=0;	
	/**
	* total Matakuliah
	*/
	public static $jumlahMatkul=0;		
	public function onLoad($param) {
		parent::onLoad($param);	
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPKRS = true;
		$this->createObj('KRS');
			
		if (!$this->IsPostBack&&!$this->IsCallback) {
            $this->setInfoToolbar();
            $this->populateData();	
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->KRS->getDataMHS($idx);
    }
	public function setInfoToolbar() {   
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);		
		$this->lblModulHeader->Text="T.A $ta Semester $semester";        
	}	
	private function populateData ($search=false) {
        try {			
            $idkrs=addslashes($this->request['id']);
            $datamhs=$_SESSION['currentPagePKRS']['DataMHS'];
            $datakrs=$_SESSION['currentPagePKRS']['DataKRS']['krs'];
            $this->Page->KRS->DataKRS['krs']=$datakrs;
            if (!isset($datakrs['idkrs'])) {
                throw new Exception ('Mohon kembali ke halaman <a href="'.$this->constructUrl('perkuliahan.PKRS',true).'">ini</a>');
            }
            if ($datakrs['idkrs'] != $idkrs) {
                throw new Exception ('Mohon kembali ke halaman <a href="'.$this->constructUrl('perkuliahan.PKRS',true).'">ini</a>');
            }
            $this->KRS->setDataMHS($datamhs);
            $detailkrs=$this->KRS->getDetailKRS($idkrs);
            $this->RepeaterS->DataSource=$detailkrs;
            $this->RepeaterS->dataBind();
        }catch (Exception $e) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();	
        }
//		$this->dataMhs=$this->session['pkrs_mhs'];		
//		$this->KRS->setNim($this->dataMhs['nim']);
//		$this->KRS->setParameterGlobal ($this->session['ta'],$this->session['semester'],$this->session['pkrs_mhs']['kjur']);		
//		$this->dataKrs=$this->KRS->getKrs();
//		$this->txtIdKrs->Value=$this->dataKrs['krs']['idkrs'];
//		$this->dataKrs['krs']['maks_sks']=$this->session['pkrs_mhs']['maks_sks'];
//		$total=$this->KRS->getTotalSKSAndMatkulInKrs ();				
//		$this->totalSks = $total['sks'];
//		$this->jumlahMatkul=$total['matkul'];
//		$this->session['krs_sah']=$this->dataKrs['krs']['sah'];				
//		$this->KrsMahasiswa->DataSource=$this->dataKrs['matakuliah'];
//		$this->KrsMahasiswa->dataBind();	
//		$this->createObjDemik();					
//		$this->Demik->setNim($this->dataMhs['nim']);        
//		$idkur=$this->Demik->Matkul->getIDKurikulum ($this->dataMhs['tahun_masuk'],$this->dataMhs['kjur']);
//		$this->Demik->setParameterGlobal ($this->session['ta'],$this->session['semester'],$this->dataMhs['kjur'],$idkur);		
//		$penyelenggaraan=$this->Demik->getPenyelenggaraanMatakuliah('sudah',true,'');				
//		$this->repeaterPenyelenggaraan->DataSource=$penyelenggaraan;
//		$this->repeaterPenyelenggaraan->dataBind();
	}
    public function itemCreated ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {                
            CDetailPKRS::$totalSKS+=$item->DataItem['sks'];
            CDetailPKRS::$jumlahMatkul+=1;
            if ($item->DataItem['batal']) {
				$item->btnBatalkanMatkul->Text='Sahkan';
				$item->btnBatalkanMatkul->Attributes->onclick="if(!confirm('Anda ingin mensahkan Matakuliah mahasiswa ini ?')) return false;";					
                $item->btnBatalkanMatkul->CssClass='table-link';                
            }else {
				$item->btnBatalkanMatkul->Attributes->onclick="if(!confirm('Anda ingin membatalkan Matakuliah mahasiswa ini ?')) return false;";						
                $item->btnBatalkanMatkul->CssClass='table-link danger';
                $item->btnBatalkanMatkul->Attributes->Title='Batalkan Matakuliah';
			}
        }
    }
	public function tambahMatkul ($sender,$param) {
		try {
			
			$idkrs=$this->txtIdKrs->Value;
			$str = "SELECT SUM(sks) AS jumlah FROM v_krsmhs WHERE idkrs='$idkrs'";
			$this->DB->setFieldTable(array('jumlah'));
			$r=$this->DB->getRecord($str);
			$jumlah=$r[1]['jumlah']+$sender->CommandParameter;
			$maks_sks=$this->session['pkrs_mhs']['maks_sks'];
			if ($jumlah > $maks_sks) throw new Exception ('Tidak bisa tambah sks lagi. Karena telah melebihi batas anda');
			$idpenyelenggaraan=$this->getDataKeyField($sender,$this->repeaterPenyelenggaraan);
			$this->KRS->dataMhs['nim']=$this->session['pkrs_mhs']['nim'];
			$this->KRS->dataMhs['idkelas']=$this->session['pkrs_mhs']['idkelas'];
			$this->KRS->checkMatkulSyarat(null,$idpenyelenggaraan);						
			if (!$this->DB->checkRecordIsExist('idpenyelenggaraan','krsmatkul',$idpenyelenggaraan,' AND idkrs='.$idkrs)) { 
				$str = "INSERT INTO krsmatkul (idkrsmatkul,idkrs,idpenyelenggaraan,batal) VALUES (NULL,'$idkrs',$idpenyelenggaraan,0)";
				$this->DB->insertRecord($str);							
			}
			$this->DB->insertRecord("INSERT INTO pkrs (nim,idpenyelenggaraan,tambah,tanggal) VALUES ('{$this->session['pkrs_mhs']['nim']}',$idpenyelenggaraan,1,NOW())");			
			$this->redirect('perkuliahan.PKRS',true);
		}catch (Exception $e) {
			$this->ErrorMessageTambahMatkul->Text=$e->getMessage();					
		}		
	}	
	public function closeKrs ($sender,$param) {		
		unset($this->session['krs_sah']);
		unset($this->session['pkrs_mhs']);
		$this->redirect('perkuliahan.PKRS',true);
	}
    public function batalkanSahkanMatkul ($sender,$param){
		$datakrs=$_SESSION['currentPagePKRS']['DataKRS']['krs'];
		$idkrsmatkul=$this->getDataKeyField($sender,$this->RepeaterS);		
		$id=explode('_',$sender->CommandParameter);
		$idpenyelenggaraan=$id[1];	
		if ($id[0]==1) {			
			try {				
				$idkrs=$this->txtIdKrs->Value;
				$str = "SELECT SUM(sks) AS jumlah FROM v_krsmhs WHERE idkrs='$idkrs' AND batal=0";
				$this->DB->setFieldTable(array('jumlah'));
				$r=$this->DB->getRecord($str);	
				$jumlah=$r[1]['jumlah']+$id[1];				
				$maks_sks=$this->session['pkrs_mhs']['maks_sks'];
				if ($jumlah > $maks_sks) throw new Exception ('Matakuliah, tidak bisa disahkan. Karena telah melebihi batas anda');
				$str = "UPDATE krsmatkul SET batal=0 WHERE idkrsmatkul=$idkrsmatkul";
				$this->DB->updateRecord($str);
				$this->DB->insertRecord("INSERT INTO pkrs (nim,idpenyelenggaraan,sah,tanggal) VALUES ('{$this->session['pkrs_mhs']['nim']}',$idpenyelenggaraan,1,NOW())");			
				$this->redirect('perkuliahan.DetailPKRS',true,array('id'=>$datakrs['idkrs']));	
			} catch (Exception $e) {
				$this->idProcess='view';	
                $this->errorMessage->Text=$e->getMessage();						
			}
		}elseif ($id[0]==0) {		
			$str = "UPDATE krsmatkul SET batal=1 WHERE idkrsmatkul=$idkrsmatkul";			
			$this->DB->updateRecord($str);
			$this->DB->insertRecord("INSERT INTO pkrs (nim,idpenyelenggaraan,batal,tanggal) VALUES ('{$this->session['pkrs_mhs']['nim']}',$idpenyelenggaraan,1,NOW())");			
			$this->redirect('perkuliahan.DetailPKRS',true,array('id'=>$datakrs['idkrs']));	
		}
		
	}
	public function hapusMatkul ($sender,$param) {		
		$idkrsmatkul=$this->getDataKeyField($sender,$this->RepeaterS);
        $id=explode('_',$sender->CommandParameter);				
		$idpenyelenggaraan=$id[1];		
        $datakrs=$_SESSION['currentPagePKRS']['DataKRS']['krs'];
        $nim=$datakrs['nim'];
		$this->DB->query ('BEGIN');		
		if ($this->DB->deleteRecord("krsmatkul WHERE idkrsmatkul='$idkrsmatkul'")) {
			$this->DB->deleteRecord("kelas_mhs_detail WHERE idkrsmatkul='$idkrsmatkul'");
			$this->DB->insertRecord("INSERT INTO pkrs (nim,idpenyelenggaraan,hapus,tanggal) VALUES ('$nim',$idpenyelenggaraan,1,NOW())");										
			$this->DB->query ('COMMIT');
		}else {
			$this->DB->query ('ROLLBACK');
		}		
		$this->redirect('perkuliahan.DetailPKRS',true,array('id'=>$datakrs['idkrs']));
	}
	
	public function viewKrs ($sender,$param) {		
		
		$nim=$this->getDataKeyField($sender,$this->RepeaterS);		
		$nilai=$this->getLogic('Nilai');
		$nilai->setParameterGlobal ($this->session['ta'],$this->session['semester'],'');
		$nilai->setNim($nim,true);
		$nilai->dataMhs['maks_sks']=$nilai->getMaxSks();
		$_SESSION['pkrs_mhs']=$nilai->dataMhs;
		$this->redirect('perkuliahan.PKRS',true);					
	}
	
	public function isiKrs ($sender,$param) {						
		if ($this->IsValid) {
			
			$nilai=$this->getLogic('Nilai');
			$nilai->setParameterGlobal ($this->session['ta'],$this->session['semester'],'');
			$nilai->setNim($this->txtAddNim->Text,true);
			$nilai->dataMhs['maks_sks']=$nilai->getMaxSks();
			$this->session['pkrs_mhs']=$nilai->dataMhs;
			$this->redirect('perkuliahan.PKRS',true);
		}		
	}
	public function viewLogPKRS ($sender,$param) {		
		$this->idProcess='view';
		$nim=$this->getDataKeyField($sender,$this->RepeaterS);	
				
		$idsmt=$this->session['semester'];
		$ta=$this->session['ta'];		
		$this->KRS->setNim($nim,true);
		$this->dataMhs=$this->KRS->dataMhs;
		$this->KRS->setParameterGlobal ($ta,$idsmt,'');
		$result=$this->KRS->getKrs('data');	
		if (isset($result['idkrs'])) {
			$this->dataKrs=$result;			
			$str = "SELECT vp.kmatkul,vp.nmatkul,vp.sks,vp.semester,p.tanggal,p.hapus,p.batal,p.sah,p.tambah FROM pkrs p,v_penyelenggaraan vp WHERE p.idpenyelenggaraan=vp.idpenyelenggaraan AND vp.idsmt='$idsmt' AND tahun='$ta' AND p.nim='$nim' ORDER BY p.tanggal DESC";
			$this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','tanggal','hapus','batal','sah','tambah'));
			$r=$this->DB->getRecord($str);
			$this->RepeaterViewLogPKRS->DataSource=$r;
			$this->RepeaterViewLogPKRS->dataBind();
		}else {
			$this->RepeaterViewLogPKRS->DataSource=array();
			$this->RepeaterViewLogPKRS->dataBind();
		}					
	}
	public function dataBoundViewLogPKRS($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {
			$ket='-';
			if ($item->DataItem['tambah']) 
				$ket='Matakuliah ini ditambahkan ke KRS, oleh dosen wali.';
			elseif ($item->DataItem['hapus']) 
				$ket='Matakuliah ini dihapus dari KRS, oleh dosen wali.';
			elseif ($item->DataItem['batal']) 
				$ket='Matakuliah ini dibatalkan, oleh dosen wali.';
			elseif ($item->DataItem['sah']) 
				$ket='Matakuliah ini disahkan, oleh dosen wali.';
			$item->txtKet->Text=$ket;
		}
	}
}

?>