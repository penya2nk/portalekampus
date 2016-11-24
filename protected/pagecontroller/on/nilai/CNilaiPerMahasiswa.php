<?php
prado::using ('Application.MainPageON');
class CNilaiPerMahasiswa extends MainPageON {	
	public $pnlInputNim = false;
	public $pnlInputPenyelenggaraan = false;
	public function onLoad ($param) {
		parent::onLoad($param);	
		$this->createObj('Nilai');
        $this->showSubMenuAkademikNilai=true;
        $this->showNilaiPerMahasiswa=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageNilaiPerMahasiswa'])||$_SESSION['currentPageNilaiPerMahasiswa']['page_name']!='on.nilai.NilaiPerMahasiswa') {
				$_SESSION['currentPageNilaiPerMahasiswa']=array('page_name'=>'on.nilai.NilaiPerMahasiswa','DataMHS'=>array());												
			}
            try {
                $nim=addslashes($this->request['id']);                
                $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas  FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                $r=$this->DB->getRecord($str);	           
                if (!isset($r[1])) {
                    unset($_SESSION['currentPageNilaiPerMahasiswa']);
                    throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                }
                $datamhs=$r[1];
                $datamhs['iddata_konversi']=$this->Nilai->isMhsPindahan($nim,true);
                
                $kelas=$this->Nilai->getKelasMhs();                
                $datamhs['nkelas']=($kelas['nkelas']=='')?'Belum ada':$kelas['nkelas'];			                    
                $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];

                $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
                $datamhs['nama_dosen']=$nama_dosen;                
                
                $this->Nilai->setDatamHS($datamhs);
                $_SESSION['currentPageNilaiPerMahasiswa']['DataMHS']=$datamhs;
            }catch (Exception $ex) {
                $this->idProcess='view';	            
            }
//			if (isset($_SESSION['currentPageNilaiPerMahasiswa']['processNilai'])) {
//				$this->disableToolbars();
//				$this->idProcess='view';
//				$this->prepareTransaction ();
//			}else {
//				$this->tbCmbTa->DataSource=$this->Demik->removeNone($this->session['tahun_akademik']);
//				$this->tbCmbTa->Text=$this->session['ta'];
//				$this->tbCmbTa->dataBind();		
//					
//				$this->tbCmbPs->DataSource=$this->Demik->removeNone($this->session['daftar_jurusan']);
//				$this->tbCmbPs->Text=$this->session['kjur'];
//				$this->tbCmbPs->dataBind();				
//					
//				$this->tbCmbSmt->DataSource=$this->Demik->removeNone($this->session['daftar_semester']);
//				$this->tbCmbSmt->Text=$this->session['semester'];
//				$this->tbCmbSmt->dataBind();	
//				if (!isset($_SESSION['currentPageNilai'])||$_SESSION['currentPageNilai']['page_name']!='a_m_akademik_nilai') {
//					$_SESSION['currentPageNilai']=array('page_name'=>'a_m_akademik_nilai','page_num'=>0);												
//				}
//				$this->systemErrorMessage = 'Maaf jumlah Mahasiswa kurang mencukupi.';
////				$this->populateData();
//				$this->session['searchMhs']=false;
//				$this->tbCmbKelas->Enabled=false;
			
		}
		
	}	
    public function cekNIM ($sender,$param) {		
        $nim=addslashes($param->Value);		
        if ($nim != '') {
            try {
                $str = "SELECT nim FROM register_mahasiswa WHERE nim='$nim'";
                $this->DB->setFieldTable(array('nim'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                   
                    throw new Exception ("NIM ($nim) tidak terdaftar di Portal, silahkan ganti dengan yang lain.");		
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function GO ($sender,$param){
        if ($this->IsValid) {
            $nim=addslashes($this->txtAddNIM->Text);
            $this->redirect('nilai.NilaiPerMahasiswa', true,array('id'=>$nim));
        }
    }
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }
	public function changeTbTa ($sender,$param) {
		$_SESSION['currentPageNilai']['page_num']=0;
		$this->session['ta']=$this->tbCmbTa->Text;
		$this->populateData();
	}	
	public function changeTbPs ($sender,$param) {
		$_SESSION['currentPageNilai']['page_num']=0;
		$this->session['kjur']=$this->tbCmbPs->Text;
		$this->populateData();
	}	
	public function changeTbSmt ($sender,$param) {
		$_SESSION['currentPageNilai']['page_num']=0;
		$this->session['semester']=$this->tbCmbSmt->Text;
		$this->populateData();
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNilai']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['searchMhs']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function btnSearch_Click ($sender,$param) {		
		if ($this->txtAddBerdasarkan->Text	!= 'nim') {
			$_SESSION['currentPageNilai']['page_num']=0;					
			$_SESSION['searchMhs']=true;
			$this->populateData (true);			
		}
	}
	protected function populateData ($search=false) {
		$this->Pengguna->updateActivity();	
		$ta=$this->session['ta'];
		$kjur=$this->session['kjur'];
		$semester=$this->session['semester'];		
		$str='';
		if ($search) {
			$txtSearch=trim(strtoupper($this->txtAddBerdasarkan->Text));			
			switch ($this->cmbBerdasarkan->Text) {								
				case 'kmatkul':				
					$str = "AND kmatkul='%$txtSearch%' ";
				break;
				case 'nmatkul':				
					$str = "AND nmatkul LIKE '%$txtSearch%' ";
				break;														
				case 'dosen':				
					$str = "AND nama_dosen LIKE '%$txtSearch%' ";
				break;
			}	
			$jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan WHERE idsmt='$semester' AND tahun='$ta' AND kjur='$kjur' $str",'idpenyelenggaraan');			
			$str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,iddosen,nama_dosen FROM v_penyelenggaraan WHERE idsmt='$semester' AND tahun='$ta' AND kjur='$kjur'$str ";				
		}else {
			$jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan WHERE idsmt='$semester' AND tahun='$ta' AND kjur='$kjur'",'idpenyelenggaraan');			
			$str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,iddosen,nama_dosen FROM v_penyelenggaraan WHERE idsmt='$semester' AND tahun='$ta' AND kjur='$kjur' ";				
		}		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageNilai']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageNilai']['page_num']=0;}
		$str .= "ORDER BY nmatkul ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','iddosen','nama_dosen'));			
		$r = $this->DB->getRecord($str,$offset+1);	
		$result=array();		
		while (list ($k,$v) = each($r)) {										
			$v['kmatkul']=$this->nilai->Matkul->getKMatkul ($v['kmatkul']);
			$v['jumlahMhs']=$this->DB->getCountRowsOfTable('v_krsmhs WHERE idpenyelenggaraan='.$v['idpenyelenggaraan'].' AND sah=1 AND batal=0');			
			$result[$k]=$v;					
		}		
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	
	}
	private function prepareTransaction () {		
		switch ($this->session['n_state_trans']) {
			case 'inputnim':
				$this->pnlInputNim=true;				
				$this->dataMhs=$_SESSION['currentPageNilaiPerMahasiswa']['processNilai'];
				$this->nilai->setParameterGlobal($this->session['ta'],$this->session['semester'],$this->session['kjur']);		
				$this->nilai->setNim($this->dataMhs['nim'],true);				
				$result=$this->nilai->getNilaiByID('','nim');	
				$tgl=$this->TGL;		
				$str_su='SELECT username FROM simak_user WHERE userid=';
				$str_dosen='SELECT nama_dosen FROM dosen WHERE iddosen=';
				while (list($k,$v)=each($result)) {
					if ($v['bydosen']) {
						$userid_input=$v['userid_input'];
						if ($userid_input==0) {
							$v['userid_input']='-';
							$v['tanggal_input']='-';
							$v['userid_modif']='-';
							$v['tanggal_modif']='-';
							$v['ket']='-';
						}else {
							$this->DB->setFieldTable(array('nama_dosen'));
							$str_gab="$str_dosen"."$userid_input";
							$simak_user = $this->DB->getRecord($str_gab);
							$v['userid_input']=$simak_user[1]['nama_dosen'];
							$userid_modif=$v['userid_modif'];
							if ($userid_modif == 0) {
								$v['tanggal_modif']='-';
								$v['userid_modif']='-';
								$v['ket']='-';
							}else {
								$simak_user = $this->DB->getRecord($str_dosen.$userid_modif);
								$v['userid_modif']=$simak_user[1]['nama_dosen'];
								$v['tanggal_modif']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_modif']);
							}
						}
					}else {
						$userid_input=$v['userid_input'];
						if ($userid_input==0) {
							$v['userid_input']='-';
							$v['tanggal_input']='-';
							$v['userid_modif']='-';
							$v['tanggal_modif']='-';
							$v['ket']='-';
						}else {
							$v['tanggal_input']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_input']);
							$this->DB->setFieldTable(array('username'));
							$str_gab="$str_su"."$userid_input";
							$simak_user = $this->DB->getRecord($str_gab);
							$v['userid_input']=$simak_user[1]['username'];
							$userid_modif=$v['userid_modif'];
							if ($userid_modif == 0) {
								$v['tanggal_modif']='-';
								$v['userid_modif']='-';
								$v['ket']='-';
							}else {
								$simak_user = $this->DB->getRecord($str_su.$userid_modif);
								$v['userid_modif']=$simak_user[1]['username'];
								$v['tanggal_modif']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_modif']);
							}
						}
					}
					$result2[$k]=$v;
				}
				$this->repeaterKrs->DataSource=$result2;
				$this->repeaterKrs->dataBind();			
			break;
			case 'inputviapenyelenggaraan':				
				$this->infoMatkul=$_SESSION['currentPageNilaiPerMahasiswa']['processNilai'];				
				$this->populateNilaiPenyelenggaraan();
			break;
		}
	}
	public function renderCallbackIsiNilai ($sender,$param) {
		$this->idProcess='view';
		$this->RepeaterNilai->render($param->NewWriter);	
	}	
	public function Page_Changed_IsiNilai ($sender,$param) {
		$this->idProcess='view';
		$_SESSION['currentPageNilai']['page_num']=$param->NewPageIndex;
		$this->populateNilaiPenyelenggaraan();
	}
	public function populateNilaiPenyelenggaraan() {
		$this->pnlInputPenyelenggaraan=true;
		$id=$_SESSION['currentPageNilaiPerMahasiswa']['processNilai']['idpenyelenggaraan'];
		$this->RepeaterNilai->CurrentPageIndex=$_SESSION['currentPageNilai']['page_num'];				
		$this->RepeaterNilai->VirtualItemCount=$this->DB->getCountRowsOfTable("v_krsmhs vkm LEFT JOIN nilai_matakuliah nm ON (nm.idkrsmatkul=vkm.idkrsmatkul) JOIN v_datamhs vdm ON (vdm.nim=vkm.nim) WHERE vkm.idpenyelenggaraan='$id'",'vdm.nim');
		$offset=$this->RepeaterNilai->CurrentPageIndex*$this->RepeaterNilai->PageSize;
		$limit=$this->RepeaterNilai->PageSize;
		if (($offset+$limit)>$this->RepeaterNilai->VirtualItemCount) {
			$limit=$this->RepeaterNilai->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageNilai']['page_num']=0;}		
		$str = "SELECT vkm.idkrsmatkul,vkm.nim,vdm.nama_mhs,vdm.jk,nm.n_kual,nm.userid_input,nm.tanggal_input,nm.userid_modif,nm.tanggal_modif,nm.bydosen,nm.ket,vkm.batal,vkm.sah,vdm.k_status FROM v_krsmhs vkm LEFT JOIN nilai_matakuliah nm ON (nm.idkrsmatkul=vkm.idkrsmatkul) JOIN v_datamhs vdm ON (vdm.nim=vkm.nim) WHERE vkm.idpenyelenggaraan='$id' ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";	
		$this->DB->setFieldTable(array('idkrsmatkul','nim','nama_mhs','jk','n_kual','userid_input','tanggal_input','userid_modif','tanggal_modif','bydosen','ket','batal','sah','k_status'));				
		$r=$this->DB->getRecord($str,$offset+1);						
		$result=array();
		$tgl=$this->TGL;		
		$str_su='SELECT username FROM simak_user WHERE userid=';
		$str_dosen='SELECT nama_dosen FROM dosen WHERE iddosen=';
		while (list($k,$v)=each($r)) {
			$n_status='-';
			if ($v['k_status']=='L')$n_status='Lulus';elseif ($v['k_status']=='D')$n_status='Drop-Out';
			$v['n_status']=$n_status;
			$userid_input=$v['userid_input'];
			if (empty($userid_input)||$userid_input == '') {
				$v['userid_input']='-';
				$v['tanggal_input']='-';
				$v['tanggal_modif']='-';
				$v['userid_modif']='-';
				$v['ket']='-';
			}else{
				$v['tanggal_input']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_input']);				
				if ($v['bydosen']) {
					$this->DB->setFieldTable(array('nama_dosen'));
					$simak_user = $this->DB->getRecord($str_dosen.$userid_input);
					$v['userid_input']=$simak_user[1]['nama_dosen'];
					$userid_modif=$v['userid_modif'];
					if ($userid_modif == 0) {
						$v['tanggal_modif']='-';
						$v['userid_modif']='-';
						$v['ket']='-';
					}else {
						$simak_user = $this->DB->getRecord($str_dosen.$userid_modif);
						$v['userid_modif']=$simak_user[1]['nama_dosen'];
						$v['tanggal_modif']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_modif']);
					}
				}else {
					$this->DB->setFieldTable(array('username'));
					$simak_user = $this->DB->getRecord($str_su.$userid_input);
					$v['userid_input']=$simak_user[1]['username'];
					$userid_modif=$v['userid_modif'];
					if ($userid_modif == 0) {
						$v['tanggal_modif']='-';
						$v['userid_modif']='-';
						$v['ket']='-';
					}else {
						$simak_user = $this->DB->getRecord($str_su.$userid_modif);
						$v['userid_modif']=$simak_user[1]['username'];
						$v['tanggal_modif']=$tgl->tanggal ('j F Y H:m:s',$v['tanggal_modif']);
					}
				}
			}			
			$result[$k]=$v;
		}	
		$this->RepeaterNilai->DataSource=$result;
		$this->RepeaterNilai->dataBind();
	}
	
	public function close ($sender,$param) {
		unset ($this->session['n_state_trans']);
		unset ($this->session['n_state_value']);
		unset ($_SESSION['currentPageNilaiPerMahasiswa']['processNilai']);
		$this->nilai->Redirect('a.m.Akademik.Nilai');
	}
	public function checkBerdasarkan ($sender,$param){
		try {
			$id=$this->txtAddBerdasarkan->Text;
			switch ($this->cmbBerdasarkan->Text) {
				case 'nim' :					
					if ($id != ''){
						$this->nilai->setNim($id);						
						$r = $this->nilai->getList("register_mahasiswa WHERE nim='$id'",array('nim','k_status'));						
						if (!isset($r[1])) throw new AkademikException ($id,2);
						if ($r[1]['k_status']== 'L'||$r[1]['k_status']== 'D')throw new Exception ("Proses ini tidak bisa dilanjutkan karena nim ($id) sudah dinyatakan lulus atau drop-out");
						$r = $this->nilai->getList("krs WHERE nim='$id' AND tahun='{$this->session['ta']}' AND idsmt='{$this->session['semester']}'",array('sah'));						
						if (!isset($r[1]))throw new Exception ("Nim ($id) belum mengisi KRS pada T.A dan Semester sekarang.");
						if (!$r[1]['sah']) throw new KrsException ($id,1);
					}
				break;
				case 'kmatkul' :
					if ($id != ''){
						$kmatkul=$this->session['kjur'].'_'.$id;
						if (!$this->nilai->Matkul->isKMatkulExist($kmatkul)) throw new Exception ("Matakuliah dengan kode ($id) tidak terdaftar di database.");
					}					
				break;
			}
			$param->IsValid=true;
		}catch (AkademikException $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->pesanKesalahan();					
		}catch (KrsException $e) {
			$param->IsValid=false;
			$sender->ErrorMessage=$e->pesanKesalahan();					
		}catch (Exception $e) {
			$sender->ErrorMessage = $e->getMessage();
			$param->IsValid=false;
		}		
	}	
	
	public function viewNilaiNim ($sender,$param){
		if ($this->IsValid) {						
			$id=$this->txtAddBerdasarkan->Text;			
			switch ($this->cmbBerdasarkan->Text) {
				case 'nim' :										
					$this->nilai->setNim($id,true);					
					$_SESSION['currentPageNilaiPerMahasiswa']['processNilai']=$this->nilai->dataMhs;
					$this->session['n_state_trans']='inputnim';
					$this->nilai->redirect('a.m.Akademik.Nilai');
				break;
				case 'kmatkul':
				case 'nmatkul' :
				case 'dosen' :
					$this->populateData();				
				break;
			}			
		}
	}
	public function showNilai ($sender,$param) {
		$this->Pengguna->updateActivity();			
		$idpenyelenggaraan=$this->getDataKeyField($sender,$this->RepeaterS);
		$infomatkul=$this->nilai->getInfoMatkul($idpenyelenggaraan,'penyelenggaraan');		
		$_SESSION['currentPageNilaiPerMahasiswa']['processNilai']=$infomatkul;
		$_SESSION['n_state_trans']='inputviapenyelenggaraan';
		$this->nilai->redirect('a.m.Akademik.Nilai');		
	}

	public function setData ($sender,$param) {
		$item = $param->Item;	
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {					
			if ($item->DataItem['batal']) {
				$ket='Dibatalkan oleh dosen wali.';		
				$item->cmbNilai->Enabled=false;		
			}else {
				$item->cmbNilai->Text=$item->DataItem['n_kual'];
				$item->nilai_sebelumnya->Value=$item->DataItem['n_kual'];				
				$item->cmbNilai->Enabled=$item->DataItem['bydosen']==true?false:true;
			}				
		}
	}
	
	public function saveNilai ($sender,$param){
		$this->Pengguna->updateActivity();					
		$repeater=$sender->getId()=='btnSavePenyelenggaraan'?$this->RepeaterNilai:$this->repeaterKrs;
		$this->idProcess='view';	
		$userid=$this->userid;
		$tanggal_input=date ('Y-m-d H:m:s');		
		foreach ($repeater->Items as $inputan) {	
			if ($inputan->cmbNilai->Enabled) {												
				$idkrsmatkul=$inputan->idkrsmatkul->Value;								
				$n_kual=$inputan->cmbNilai->Text;				
				$nilai_sebelumnya=$inputan->nilai_sebelumnya->Value;				
				if ($nilai_sebelumnya==''&&$n_kual!='none') {//insert					
					if (!$this->DB->checkRecordIsExist('idkrsmatkul','nilai_matakuliah',$idkrsmatkul)) {
						$str = "INSERT INTO nilai_matakuliah (idnilai,idkrsmatkul,n_kual,userid_input,tanggal_input) VALUES (NULL,$idkrsmatkul,'$n_kual',$userid,'$tanggal_input')";				
						$this->DB->insertRecord($str);
					}
				}elseif($n_kual!='none'&&$n_kual!=$nilai_sebelumnya){//update										
					$str = "UPDATE nilai_matakuliah SET n_kual='$n_kual',userid_modif='$userid',tanggal_modif='$tanggal_input',ket='dari $nilai_sebelumnya menjadi $n_kual' WHERE idkrsmatkul='$idkrsmatkul'";				
					$this->DB->updateRecord($str);
				}elseif($nilai_sebelumnya != ''&&$n_kual=='none'){//delete
					$str = "nilai_matakuliah WHERE idkrsmatkul='$idkrsmatkul'";	
					$this->DB->deleteRecord($str);
				}				
			}
		}
		$this->nilai->redirect('a.m.Akademik.Nilai');
	}	
	
	//on item data bound untuk nilai per idpenyelenggaraan
	public function setDataNilai($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType == 'Item' || $item->ItemType=='AlternatingItem') {			
			$item->nilai_sebelumnya->Value=$item->DataItem['n_kual'];
			$item->cmbNilai->Text=$item->DataItem['n_kual'];
			$item->cmbNilai->Enabled=$item->DataItem['k_status'] == 'L'?false:true;
		}		
	}
	public function itemCreated ($sender,$param) {
		$item = $param->Item;	
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {			
			if ($item->DataItem['jumlahMhs'] == 0) {				
				$item->btnShow->Attributes->onclick='Modalbox.show(systemErrorMessage, {title: this.title}); return false;';
				$item->btnShow->Enabled=false;				
			}
		}
	}
	public function printPdf ($sender,$param) {
		$this->nilai->setDataMaster('',$this->session['daftar_semester'],$this->session['tahun_akademik'],$this->session['daftar_jurusan']);
		$this->nilai->setParameterGlobal($this->session['ta'],$this->session['semester'],$this->session['kjur']);		
		$this->nilai->printNilaiMatakuliah('pdf',$_SESSION['currentPageNilaiPerMahasiswa']['processNilai']);
		$this->nilai->Report->printOut('daftar_nilai_matakuliah');
		$this->nilai->Report->setLink($this->linkExcel);
	}
}

?>