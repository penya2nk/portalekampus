<?php
prado::using ('Application.MainPageM');
class CPembayaranFormulir extends MainPageM {
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuSPMBPendaftaran=true;
        $this->showPembayaranFormulir=true;
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePembayaranFormulir'])||$_SESSION['currentPagePembayaranFormulir']['page_name']!='m.spmb.PembayaranFormulir') {
				$_SESSION['currentPagePembayaranFormulir']=array('page_name'=>'m.spmb.PembayaranFormulir','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false);												
			}	
            $_SESSION['currentPagePembayaranFormulir']['search']=false;
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
            $this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
            $this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
            $this->tbCmbTahunMasuk->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();  
            $this->populateData();
			
		}
	}
    public function getInfoToolbar() {                
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="Tahun Masuk $tahunmasuk";
		return $text;
	}
    public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function btnSearch_Click ($sender,$param) {		
		$_SESSION['currentPagePembayaranFormulir']['page_num']=0;
		$this->populateData ($this->getStrSearch());
	}
	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePembayaranFormulir']['page_num']=$param->NewPageIndex;
		$this->populateData($this->getStrSearch());
	}
	
	private function populateData ($search=false) {
        $tahun_masuk=$_SESSION['tahun_masuk'];
        
        $str = "SELECT * FROM transaksi t JOIN transaksi_detail td ON (t.no_transaksi=td.no_transaksi) JOIN pin (pin.no_formulir=t.no_formulir) WHERE td.idkombi=1 AND t.tahun_masuk=$tahun_masuk";
        
//		$semester = $_SESSION['semester'];
//		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePembayaranFormulir']['page_num'];
//		if ($str == 'none' || $str == '') {
//			$jumlah_baris=$this->DB->getCountRowsOfTable("formulir_pendaftaran fp,bipend bp WHERE fp.no_formulir=bp.no_formulir AND fp.ta='$ta' AND fp.idsmt='$semester' AND fp.idkelas='".$_SESSION['kelas']."'");
//			$str = "SELECT bp.no_formulir,fp.nama_mhs,fp.alamat_rumah,bp.dibayarkan FROM formulir_pendaftaran fp,bipend bp,profiles_mahasiswa pm WHERE fp.no_formulir=bp.no_formulir AND pm.no_formulir=fp.no_formulir AND fp.ta='$ta' AND fp.idsmt='$semester' AND fp.idkelas='".$_SESSION['kelas']."'";	
//		}else {
//			$jumlah_baris=$this->DB->getCountRowsOfTable("formulir_pendaftaran fp,bipend bp WHERE fp.no_formulir=bp.no_formulir AND fp.ta='$ta' AND $str");
//			$str = "SELECT bp.no_formulir,fp.nama_mhs,fp.alamat_rumah,bp.dibayarkan FROM formulir_pendaftaran fp,bipend bp,profiles_mahasiswa pm WHERE fp.no_formulir=bp.no_formulir AND pm.no_formulir=fp.no_formulir AND fp.ta='$ta' AND fp.idsmt='$semester' AND fp.idkelas='".$_SESSION['kelas']."' AND $str";	
//		}		
//		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
//		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
//		$limit=$this->RepeaterS->PageSize;
//		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
//			$limit=$this->RepeaterS->VirtualItemCount-$offset;
//		}
//		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePembayaranFormulir']['page_num']=0;}
//		$str = $str . " ORDER BY fp.nama_mhs ASC LIMIT $offset,$limit ";			
//		$this->DB->setFieldTable(array('no_formulir','nama_mhs','alamat_rumah','dibayarkan'));
//		$r=$this->DB->getRecord($str,$offset+1);				
//		$this->RepeaterS->DataSource=$r;
//		$this->RepeaterS->dataBind();
	}
	public function checkNoFormulir ($sender,$param) {	
		try {
			$id=$sender->getId ();
			$this->idProcess = ($id=='addNoFormulir')?'add':'edit';	
			$no_formulir=($id=='addNoFormulir')?$this->txtAddFormulir1->Value.$this->txtAddFormulir2->Text:$this->txtEditFormulir1->Value.$this->txtEditFormulir2->Text;			
			if ($this->txtEditFormulir->Value != $no_formulir) {
				$this->spmb->setNoFormulir($no_formulir);
				if ($this->spmb->isNoFormulirExist()) throw new Exception ("No Formulir $no_formulir Sudah ada, silahkan ganti dengan yang lain.");
				$no_formulir_via_web = $_SESSION['ta'].'6000';
				if ($no_formulir>=$no_formulir_via_web)throw new Exception ("No Formulir jangan lebih dari $no_formulir_via_web karena diperuntukan untuk pendaftaran melalui web (secara online).");
			}
		}catch (Exception $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->getMessage();
		}	
	}
	
	public function checkEmail ($sender,$param) {
		try {
			$id=$sender->getId ();
			$this->idProcess = ($id=='editEmail')?'edit':'add';			
			$email=$this->getLogic('Email');
			$email_mhs=$id=='editEmail'?$this->txtEditEmail->Text:$this->txtAddEmail->Text;
			if ($email_mhs != '') {
				$email->setEmailUser ($email_mhs);
				if (!$email->check())throw new Exception ("Format email ($email_mhs) Anda salah");
				if ($this->hidenEditEmail->Value != $email_mhs) {
					if ($email->check(true,'profiles_mahasiswa','email')) throw new Exception ("Email ($email_mhs) sudah tidak tersedia. Silahkan ganti dengan yang lain.");
				}
			}
		}catch (Exception $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->getMessage();
		}	
	}
	public function checkNoFaktur ($sender,$param) {
		try {			
			$id=$sender->getId ();
			$this->idProcess = ($id=='addNoFaktur')?'add':'edit';			
			$no_faktur = ($id=='addNoFaktur')?$this->txtAddNoFaktur->Text:$this->txtEditNoFaktur->Text;			
			if ($this->hiddenEditNoFaktur->Value != $no_faktur) {
				$this->spmb->Finance->setNoFaktur($no_faktur);
				if ($this->spmb->Finance->isNoFakturExist('bipend'))throw new Exception ("($no_faktur) sudah ada. Ganti dengan yang lain.");
			}
		}catch (Exception $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->getMessage();
		}		
	}

	private function getStrSearch() {
		$txtSearch=trim(strtoupper($this->txtBerdasarkan->Text));
		if ($txtSearch == '') {
			$str='none';
		}else {
			switch ($this->cmbBerdasarkan->Text) {
				case 'no_formulir' :
					$str = "bp.no_formulir='$txtSearch'";
				break;				
				case 'nama_mhs':
					$str = "fp.nama_mhs LIKE '%$txtSearch%'";
				break;
			}			
		}
		return $str;
	}
	public function addProcess ($sender,$param) {
		$this->Pengguna->updateActivity();			
		$ta=$_SESSION['tahun_masuk'];
		$biaya_pendaftaran=$this->spmb->getBiayaPendaftaran($_SESSION['tahun_masuk'],$_SESSION['kelas']);							
		if ($biaya_pendaftaran>0) {
			$pembayaran_spmb['biaya_pendaftaran']=$biaya_pendaftaran;
			$max_record=$this->DB->getMaxOfRecord('no_formulir',"formulir_pendaftaran WHERE ta='$ta' AND daftar_via='FO'")+1;		
			$urut=substr($max_record,strlen($ta),4);		
			$urut=($urut=='')?'0001':$urut;
			$pembayaran_spmb['no_urut']=$urut;
			$_SESSION['pembayaran_spmb']=$pembayaran_spmb;
			$this->spmb->redirect('a.m.SPMB.PembayaranSPMB');
		}else {
			$this->erroMessage->Text='<br />Biaya pendaftaran Rp.0 Silahkan ganti di Data Master.';
		}
	}
	
	public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {		
			$this->Pengguna->updateActivity();	
			$no_faktur=trim($this->txtAddNoFaktur->Text);			
			$no_formulir=$this->txtAddFormulir1->Value.$this->txtAddFormulir2->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtAddNamaMhs->Text)));	
			$alamat_rumah=strtoupper(trim($this->txtAddAlamatRumah->Text));	
			$telp_rumah=trim($this->txtAddNoTelpRumah->Text);		
			$telp_hp=trim($this->txtAddNoHP->Text);
			$tgl_bayar=$this->TGL->tukarTanggal ($this->txtTglBayar->Text);				
			$ket=trim($this->txtAddKeterangan->Text);
			$userid=$this->Pengguna->getDataUser('userid');
			$dibayarkan=$this->spmb->Finance->toInteger($this->txtAddJumlahBayar->Text);
			$userpassword=md5(1234);			
			try {
				$str="INSERT INTO formulir_pendaftaran (no_formulir,nama_mhs,alamat_rumah,telp_rumah,telp_hp,ta,idsmt,daftar_via,idkelas) VALUES ($no_formulir,'$nama_mhs','$alamat_rumah','$telp_rumah','$telp_hp','".$_SESSION['tahun_masuk']."','".$_SESSION['semester']."','FO','".$_SESSION['kelas']."')";
				$this->DB->query('BEGIN');
				if ($this->DB->insertRecord($str)) {
					$str = "INSERT INTO profiles_mahasiswa (idprofile,no_formulir,email,userpassword) VALUES (NULL,$no_formulir,'".$this->txtAddEmail->Text."','$userpassword')";
					$this->DB->insertRecord($str);
					$str = 'INSERT INTO bipend (idbipend,tahun,no_faktur,tgl_bayar,no_formulir,gelombang,dibayarkan,ket,userid) VALUES ';
					$str .= "(NULL,".$_SESSION['tahun_masuk'].",'$no_faktur','$tgl_bayar','$no_formulir','".$_SESSION['gelombang']."','$dibayarkan','$ket','$userid')";				
					$this->DB->insertRecord($str);
					$this->DB->query('COMMIT');
				}else {
					$this->DB->query('ROLLBACK');
				}				
			}catch (Exception $e) {
				echo $e->getMessage();
			}			
			unset($_SESSION['pembayaran_spmb']);
			$this->spmb->redirect('a.m.SPMB.PembayaranSPMB');
		}
	}
	public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
			$url=$this->Themes->getIcon();			
			$urlImage=$url.'error.png';
			$dibayarkan=$item->DataItem['dibayarkan'];			
			if ($dibayarkan >= $_SESSION['biaya_pendaftaran']) {
				$urlImage=$url.'sah.png';
			}
			$item->imgKet->ImageUrl=$urlImage;
			$nama_mhs=$item->DataItem['nama_mhs'];
			$item->btnHapus->Attributes->Title="Hapus $nama_mhs";
			if ($this->DB->checkRecordIsExist('no_formulir','register_mahasiswa',$item->DataItem['no_formulir'])) {
				$item->btnHapus->Enabled=false;
				$item->btnHapus->Attributes->OnClick="Modalbox.show(node, {title: this.title}); return false;";
			}
		}
	}
	
	public function editRecord ($sender,$param) {
		$this->Pengguna->updateActivity();	
		$this->idProcess='edit';
		$this->disableToolbars();
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
		$str = 'SELECT bp.no_formulir,fp.nama_mhs,fp.alamat_rumah,fp.telp_rumah,fp.telp_hp,pm.email,bp.no_faktur,bp.tgl_bayar,bp.dibayarkan,bp.ket FROM formulir_pendaftaran fp,bipend bp,profiles_mahasiswa pm WHERE fp.no_formulir=bp.no_formulir AND pm.no_formulir=fp.no_formulir AND bp.no_formulir='.$no_formulir;
		$this->DB->setFieldTable(array('no_formulir','nama_mhs','alamat_rumah','telp_rumah','telp_hp','email','no_faktur','tgl_bayar','dibayarkan','ket'));
		$r=$this->DB->getRecord($str);	
		
		$r=$r[1];
		$this->txtEditFormulir->Value=$r['no_formulir'];
		$urut=substr($r['no_formulir'],strlen($_SESSION['tahun_masuk']),4);		
		$this->txtEditFormulir2->Text=$urut;	
		$this->txtEditNamaMhs->Text=stripslashes($r['nama_mhs']);
		$this->txtEditAlamatRumah->Text=$r['alamat_rumah'];
		$this->txtEditNoTelpRumah->Text=$r['telp_rumah'];
		$this->txtEditNoHP->Text=$r['telp_hp'];
		$this->hidenEditEmail->Value=$r['email'];
		$this->txtEditEmail->Text=$r['email'];
		$this->txtEditNoFaktur->Text=$r['no_faktur'];
		$this->hiddenEditNoFaktur->Value=$r['no_faktur'];
		$this->txtEditTglBayar->Text=$this->TGL->tukarTanggal($r['tgl_bayar'],'entoid');
		$this->txtEditJumlahBayar->Text=$this->spmb->Finance->toRupiah($r['dibayarkan']);
		$this->txtEditKeterangan->Text=$r['ket'];
	}
	
	public function updateData ($sender,$param) {
		if ($this->IsValid) {
			$this->Pengguna->updateActivity();	
			$no_formulir=$this->txtEditFormulir1->Value.$this->txtEditFormulir2->Text;
			$nama_mhs=addslashes(strtoupper(trim($this->txtEditNamaMhs->Text)));	
			$alamat_rumah=strtoupper(trim($this->txtEditAlamatRumah->Text));	
			$telp_rumah=trim($this->txtEditNoTelpRumah->Text);		
			$telp_hp=trim($this->txtEditNoHP->Text);
			$tgl_bayar=$this->TGL->tukarTanggal ($this->txtTglBayar->Text);
			$no_faktur=trim($this->txtEditNoFaktur->Text);
			$ket=trim($this->txtEditKeterangan->Text);
			$userid=$this->Pengguna->getDataUser('userid');
			$dibayarkan=$this->spmb->Finance->toInteger($this->txtEditJumlahBayar->Text);
			try {
				$str = "UPDATE formulir_pendaftaran SET no_formulir='$no_formulir',nama_mhs='$nama_mhs',alamat_rumah='$alamat_rumah',telp_rumah='$telp_rumah',telp_hp='$telp_hp' WHERE no_formulir='".$this->txtEditFormulir->Value."'";
				$this->DB->query('BEGIN');
				if ($this->DB->updateRecord($str)) {
					$this->DB->updateRecord ("UPDATE profiles_mahasiswa SET email='".$this->txtEditEmail->Text."' WHERE no_formulir='$no_formulir'");
					$str = "UPDATE bipend SET no_faktur='$no_faktur',tgl_bayar='$tgl_bayar',dibayarkan='$dibayarkan',ket='$ket',userid='$userid' WHERE no_formulir='$no_formulir'";
					$this->DB->updateRecord($str);
					$this->DB->query('COMMIT');
				}else {
					$this->DB->query('ROLLBACK');
				}				
			}catch (Exception $e) {
				echo $e->getMessage();
			}
			$this->spmb->redirect('a.m.SPMB.PembayaranSPMB');
		}
	}
	
	public function deleteRecord ($sender,$param) {
		$this->Pengguna->updateActivity();	
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);		
		$str = "formulir_pendaftaran WHERE no_formulir='$no_formulir'";
		$this->DB->query ('BEGIN');
		if ($this->DB->deleteRecord($str) ) {
			$this->DB->deleteRecord ("transaksi WHERE no_formulir='$no_formulir'");
			$this->DB->query ('COMMIT');
		}else {
			$this->DB->query ('ROLLBACK');
		}
		$this->spmb->redirect('a.m.SPMB.PembayaranSPMB');
	}
	public function CloseAddProcess($sender,$param) {
		unset($_SESSION['pembayaran_spmb']);
		$this->spmb->redirect('a.m.SPMB.PembayaranSPMB');
	}
}
?>