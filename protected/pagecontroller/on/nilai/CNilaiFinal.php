<?php
prado::using ('Application.MainPageON');
class CNilaiFinal extends MainPageON {	
	public function onLoad ($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikNilai=true;
        $this->showNilaiFinal=true;    
		$this->createObj('Nilai');
        
		if (!$this->IsPostBack&&!$this->IsCallBack) {			

            if (!isset($_SESSION['currentPageNilaiFinal'])||$_SESSION['currentPageNilaiFinal']['page_name']!='on.nilai.NilaiFinal') {					
                $_SESSION['currentPageNilaiFinal']=array('page_name'=>'on.nilai.NilaiFinal','page_num'=>0,'search'=>false,'tanggal_terbit'=>'none');												
            }
            $_SESSION['currentPageNilaiFinal']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

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

            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

            $this->lblModulHeader->Text=$this->getInfoToolbar();			
            $this->populateData();

		}
		
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageNilaiFinal']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageNilaiFinal']['search']);
	}	
    public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNilaiFinal']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageNilaiFinal']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageNilaiFinal']['search']=true;
		$this->populateData($_SESSION['currentPageNilaiFinal']['search']);
	}
	public function populateData($search=false) {							
        $kjur=$_SESSION['kjur'];
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];                
        if ($search) {
            $str = "SELECT vdm.nim,vdm.nirm,vdm.nama_mhs,nomor_transkrip,predikat_kelulusan,tanggal_lulus,vdm.k_status FROM v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $cluasa="AND ta.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim $cluasa",'ta.nim');
                    $str = "$str $cluasa";
                break;
                case 'nirm' :
                    $cluasa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim $cluasa",'ta.nim');
                    $str = "$str $cluasa";
                break;
                case 'nama' :
                    $cluasa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim $cluasa",'ta.nim');
                    $str = "$str $cluasa";
                break;
            }
        }else{
            $str = "SELECT vdm.nim,vdm.nirm,vdm.nama_mhs,nomor_transkrip,predikat_kelulusan,tanggal_lulus,vdm.k_status FROM v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim AND vdm.kjur=$kjur AND ta.tahun=$ta AND ta.idsmt=$idsmt";
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_datamhs vdm,transkrip_asli ta WHERE ta.nim=vdm.nim AND vdm.kjur=$kjur AND ta.tahun=$ta AND ta.idsmt=$idsmt",'ta.nim');				
        }        
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageNilaiFinal']['page_num'];		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageNilaiFinal']['page_num']=0;}
        $str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('nim','nirm','nama_mhs','nomor_transkrip','predikat_kelulusan','tanggal_lulus','k_status'));
		$result=$this->DB->getRecord($str,$offset+1);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}	
	public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
			$nim=$item->DataItem['nim'];			
            $this->Nilai->setDataMHS(array('nim'=>$nim));
            $this->Nilai->getTranskrip(false);            
			$item->lblIpk->Text=$this->Nilai->getIPKAdaNilai();
		}	
	}
	public function checkNim ($sender,$param){		
		$nim=$this->txtNim->Text;
		if ($nim != '') {
			try {				
				$this->Nilai->setNim($nim);
				$r = $this->Nilai->getList("register_mahasiswa WHERE nim='$nim'",array('nim','k_status','tahun','idsmt'));
				$this->Nilai->dataMhs=$r[1];				
				if (!$this->Nilai->isNimExist()) throw new AkademikException ($nim,2);	
				if ($this->Application->getModule ('environment')->checkRequirementNilaiFinal) {
					if ($this->Nilai->dataMhs['k_status']!='L')throw new Exception ("Status ($nim) belum lulus.");
					$awal=$r[1]['tahun'].$r[1]['semester'];
					$akhir=$_SESSION['ta'].$_SESSION['semester'];
					$totalsks=$this->DB->getSumRowsOfTable('sks',"v_nilai WHERE (tasmt BETWEEN $awal AND $akhir) AND nim='$nim' AND n_kual !='E'");
					if ($totalsks <144)throw new Exception ("Pada T.A dan semester ini total SKS ($nim) baru $totalsks harus lebih dari atau sama dengan 144");				
				}
			}catch (AkademikException $e) {
				$sender->ErrorMessage=$e->pesanKesalahan();				
				$param->IsValid=false;
			}catch(Exception $e) {			
				$sender->ErrorMessage=$e->getMessage();				
				$param->IsValid=false;
			}			
		}
	}
	public function processKelulusan ($sender,$param) {
		if ($this->IsValid) {
			$nim=$this->txtNim->Text;
			$this->Nilai->setNim($nim,true);
            $idkur=$this->Demik->Matkul->getIDKurikulum ($this->Nilai->dataMhs['tahun_masuk'],$this->Nilai->dataMhs['kjur']);
            $this->Nilai->dataMhs['idkur']=$idkur;
			$_SESSION['currentPageNilaiFinal']['m_NilaiFinal']=$this->Nilai->dataMhs;
			$this->redirect('nilai.NilaiFinal',true);
		}
	}
	public function detailProcess () {				
		$this->dataMhs=$_SESSION['currentPageNilaiFinal']['m_NilaiFinal'];		
		$nim=$this->dataMhs['nim'];
		$daftar_dosen=$this->getLogic('Dosen')->getListDosen(2);
		$this->cmbAddDosenPembimbing->DataSource=$daftar_dosen;
		$this->cmbAddDosenPembimbing->dataBind();
		$this->cmbAddDosenPembimbing2->DataSource=$daftar_dosen;
		$this->cmbAddDosenPembimbing2->dataBind();
		$this->cmbAddKetuaInstitusi->DataSource=$daftar_dosen;
		$this->cmbAddKetuaInstitusi->dataBind();
		$this->cmbAddPemketI->DataSource=$daftar_dosen;
		$this->cmbAddPemketI->dataBind();
		
		$str = "SELECT nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket FROM transkrip_asli WHERE nim='$nim'";
		$this->DB->setFieldTable(array('nomor_transkrip','predikat_kelulusan','tanggal_lulus','judul_skripsi','iddosen_pembimbing','iddosen_pembimbing2','iddosen_ketua','iddosen_pemket'));
		$r=$this->DB->getRecord($str);
		$bool=true;
		if (isset($r[1])) {
			$bool=false;
			$_SESSION['currentPageNilaiFinal']['m_NilaiFinal']['dataTranskrip']=$r[1];
			$this->hiddennomortranskrip->Value=$r[1]['nomor_transkrip'];			
			$this->txtAddNomorTranskrip->Text=$r[1]['nomor_transkrip'];			
			$this->cmbAddPredikatKelulusan->Text=$r[1]['predikat_kelulusan'];			
			$this->txtAddTanggalLulus->Text=$this->TGL->tukarTanggal($r[1]['tanggal_lulus'],'entoid');
			$this->cmbAddDosenPembimbing->Text=$r[1]['iddosen_pembimbing'];
			$this->cmbAddDosenPembimbing2->Text=$r[1]['iddosen_pembimbing2'];												
			$this->txtAddJuduluSkripsi->Text=$r[1]['judul_skripsi'];						
			$this->cmbAddKetuaInstitusi->Text=$r[1]['iddosen_ketua'];												
			$this->cmbAddPemketI->Text=$r[1]['iddosen_pemket'];				
		}else{
			$this->systemErrorMessage = 'Proses tidak bisa dilaksanakan karena belum ada data Transkrip Final.';
			$this->btnToExcel2->Attributes->OnClick="Modalbox.show(systemErrorMessage, {title: this.title}); return false;";
			$this->btnToExcel2->Enabled=false;
			$this->btnToPdf2->Attributes->OnClick="Modalbox.show(systemErrorMessage, {title: this.title}); return false;";						
			$this->btnToPdf2->Enabled=false;
			$options=$this->getLogic('Options');			
			$iddosen=$options->getOptions('ketua_stisipol');							
			$this->cmbAddKetuaInstitusi->Text=$iddosen;
			$iddosen=$options->getOptions('puket_1_bidang_akademik');
			$this->cmbAddPemketI->Text=$iddosen;			
		}		
		$this->Nilai->setNim($nim);        
		$this->Nilai->setParameterGlobal('','','',$this->dataMhs['idkur']);			
		$daftar_nilai=$this->Nilai->getTranskrip($bool);	
		$this->RepeaterTranskripS->DataSource=$daftar_nilai;
		$this->RepeaterTranskripS->dataBind();
	}
	public function viewProcess ($sender,$param) {
			
		$nim = $this->getDataKeyField($sender,$this->RepeaterS);
		$this->Nilai->setNim($nim,true);	
        $idkur=$this->Demik->Matkul->getIDKurikulum ($this->Nilai->dataMhs['tahun_masuk'],$this->Nilai->dataMhs['kjur']);
        $this->Nilai->dataMhs['idkur']=$idkur;
		$_SESSION['currentPageNilaiFinal']['m_NilaiFinal']=$this->Nilai->dataMhs;	
		$this->redirect('nilai.NilaiFinal',true);
	}
	public function checkNoTranskrip ($sender,$param) {
		try {
			$no_transkrip=$this->txtAddNomorTranskrip->Text;
			if ($this->no_transkrip->Value!=$no_transkrip) {
				if ($this->DB->checkRecordIsExist('nomor_transkrip','transkrip_asli',$no_transkrip)) {
					throw new Exception ("Nomor Transkrip ($no_transkrip) telah ada, silahkan ganti dengan yang lain");
				}
			}
		}catch (Exception $e) {
			$sender->ErrorMessage = $e->getMessage();
			$param->IsValid=false;
		}
	}
	public function saveData ($sender,$param) {
		if ($this->IsValid) {
			$log = $this->getLogic('Log');			
			$log->getIdLogMaster();
			$nim=$_SESSION['currentPageNilaiFinal']['m_NilaiFinal']['nim'];
			$log->dataMhs['nim']=$_SESSION['currentPageNilaiFinal']['m_NilaiFinal']['nim'];
			$ta=$_SESSION['ta'];					
			$semester=$_SESSION['semester'];
			$no_transkrip=$this->no_transkrip->Value;			
			$predikat=$this->cmbAddPredikatKelulusan->Text;
			$tanggal_lulus=$this->TGL->tukarTanggal ($this->txtAddTanggalLulus->Text);						
			$pembimbing=$this->cmbAddDosenPembimbing->Text;						
			$pembimbing2=$this->cmbAddDosenPembimbing2->Text;						
			$judul_skripsi=strtoupper(addslashes($this->txtAddJuduluSkripsi->Text));						
			$ketua=$this->cmbAddKetuaInstitusi->Text;						
			$pemket=$this->cmbAddPemketI->Text;						
			$this->DB->query('BEGIN');
			if ($no_transkrip == '') {                
				$no_transkrip=$this->txtAddNomorTranskrip->Text;			
				$str = 'INSERT INTO transkrip_asli (nim,nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket,tahun,idsmt) VALUES ';
				$str = $str."('$nim','$no_transkrip','$predikat','$tanggal_lulus','$judul_skripsi','$pembimbing','$pembimbing2','$ketua','$pemket',$ta,$semester)";				
				if ($this->DB->insertRecord($str)) {					
					$str = "INSERT INTO transkrip_asli_detail (nim,kmatkul,nmatkul,sks,semester,n_kual) VALUES ";
					foreach($this->RepeaterTranskripS->Items as $inputan) {						
						$item=$inputan->cmbNilai->getNamingContainer();
						$kmatkul=$this->RepeaterTranskripS->DataKeys[$item->getItemIndex()]; 
						$nmatkul=$inputan->txtMatkul->Value;
						$sks=$inputan->txtSks->Value;
						$semester=$inputan->txtSemester->Value;
						$n_kual=$inputan->cmbNilai->Text=='none'?'':$inputan->cmbNilai->Text;						
						$str2="$str('$nim','$kmatkul','$nmatkul','$sks','$semester','$n_kual')";						
						$this->DB->insertRecord($str2);
						if ($n_kual != '')
							$log->insertLogIntoNilaiFinal($kmatkul,$nmatkul,'input',$n_kual);
					}
					$this->DB->query('COMMIT');
				}else {
					$this->DB->query('ROLLBACK');
				}			
			}else {				                
				$no_transkrip=$this->txtAddNomorTranskrip->Text;
				$str = "UPDATE transkrip_asli SET nim='$nim',nomor_transkrip='$no_transkrip',predikat_kelulusan='$predikat',tanggal_lulus='$tanggal_lulus',judul_skripsi='$judul_skripsi',iddosen_pembimbing='$pembimbing',iddosen_pembimbing2='$pembimbing2',iddosen_ketua='$ketua',iddosen_pemket='$pemket',tahun=$ta,idsmt=$semester WHERE nim='$nim'";
				$this->DB->query('BEGIN');
				if ($this->DB->updateRecord($str)) {				
					foreach($this->RepeaterTranskripS->Items as $inputan) {						
						$item=$inputan->cmbNilai->getNamingContainer();
						$kmatkul=$this->RepeaterTranskripS->DataKeys[$item->getItemIndex()]; 						
						$n_kual_sebelumnya=$inputan->txtNilaiSebelumnya->Value;						
						$n_kual=$inputan->cmbNilai->Text=='none'?'':$inputan->cmbNilai->Text;		
						if ($n_kual_sebelumnya != $n_kual) {				
							$nmatkul=$inputan->txtMatkul->Value;
							$str="UPDATE transkrip_asli_detail SET n_kual='$n_kual' WHERE nim='$nim' AND kmatkul='$kmatkul'";						
							$this->DB->updateRecord($str);
							if ($n_kual_sebelumnya == '')
								$log->insertLogIntoNilaiFinal($kmatkul,$nmatkul,'input',$n_kual);
							else
								$log->insertLogIntoNilaiFinal($kmatkul,$nmatkul,'update',$n_kual_sebelumnya,$n_kual);
						}
					}
					$this->DB->query('COMMIT');
				}else {
					$this->DB->query('ROLLBACK');
				}	
			}	
			$this->redirect('nilai.NilaiFinal',true);
		}
	}
	public function printTranskripperNim ($sender,$param) {
		if ($this->isValid) {
									
			$nim=$this->txtNim->Text;					
			$this->Nilai->setNim($nim,true);					
			$this->Nilai->setParameterGlobal ($_SESSION['ta'],$_SESSION['semester'],'',$this->getKurikulumPS($this->Nilai->dataMhs['kjur']));			
			$str = "SELECT nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket FROM transkrip_asli WHERE nim='$nim'";
			$this->DB->setFieldTable(array('nomor_transkrip','predikat_kelulusan','tanggal_lulus','judul_skripsi','iddosen_pembimbing','iddosen_pembimbing2','iddosen_ketua','iddosen_pemket'));
			$r=$this->DB->getRecord($str);							
			$this->Nilai->dataMhs['dataTranskrip']=$r[1];
			$file="transkrip_asli_$nim";
			$this->Nilai->dataMhs['tanggalterbit']=$this->TGL->tukarTanggal ($this->txtDefaultTanggalTerbit->Text);
			$this->Nilai->printNilaiFinal('pdf');				
			$this->Nilai->Report->printOut($file);
			$this->Nilai->Report->setLink($this->resultLink,"<br />$file");	
		}
	}
	public function printOut ($sender,$param) {		
        $this->createObj('reportnilai');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        $bool=true;
		switch ($sender->getId()) {
			case 'btnPrintOutR' :                
				$nim = $this->getDataKeyField($sender,$this->RepeaterS);				
                switch ($_SESSION['outputreport']) {
                    case  'summarypdf' :
                        $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
                    break;
                    case  'summaryexcel' :
                        $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
                    break;
                    case  'excel2007' :
                        $messageprintout="Mohon maaf Print out pada mode excel 2007 belum kami support.";                
                    break;
                    case 'pdf' :
                        $messageprintout='Transkrip Final : ';
                        $str = "SELECT vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.nama_ps,k_status FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE nim='$nim'";
                        $this->DB->setFieldTable(array('nim','nirm','nama_mhs','tempat_lahir','tanggal_lahir','nama_ps','k_status'));
                        $r=$this->DB->getRecord($str);				
                        
                        $dataReport = $r[1];                        
                        if ($dataReport['k_status'] == 'L') {
                            $dataReport['nama_jabatan_transkrip']=$this->setup->getSettingValue('nama_jabatan_transkrip');
                            $dataReport['nama_penandatangan_transkrip']=$this->setup->getSettingValue('nama_penandatangan_transkrip');
                            $dataReport['jabfung_penandatangan_transkrip']=$this->setup->getSettingValue('jabfung_penandatangan_transkrip');
                            $dataReport['nidn_penandatangan_transkrip']=$this->setup->getSettingValue('nidn_penandatangan_transkrip');

                            //biasayanya sama sehingga menggunakan yang KHS
                            $dataReport['nama_jabatan_khs']=$this->setup->getSettingValue('nama_jabatan_khs');
                            $dataReport['nama_penandatangan_khs']=$this->setup->getSettingValue('nama_penandatangan_khs');
                            $dataReport['jabfung_penandatangan_khs']=$this->setup->getSettingValue('jabfung_penandatangan_khs');
                            $dataReport['nidn_penandatangan_khs']=$this->setup->getSettingValue('nidn_penandatangan_khs');

                            $str = "SELECT nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket,tahun,idsmt FROM transkrip_asli WHERE nim='$nim'";
                            $this->DB->setFieldTable(array('nomor_transkrip','predikat_kelulusan','tanggal_lulus','judul_skripsi','iddosen_pembimbing','iddosen_pembimbing2','iddosen_ketua','iddosen_pemket','tahun','idsmt'));
                            $datatranskrip=$this->DB->getRecord($str);

                            $datatranskrip[1]['nama_pembimbing1']=$this->DMaster->getNamaDosenPembimbing($datatranskrip[1]['iddosen_pembimbing']);
                            $datatranskrip[1]['nama_pembimbing2']=$this->DMaster->getNamaDosenPembimbing($datatranskrip[1]['iddosen_pembimbing2']);            

                            $dataReport['dataTranskrip']=$datatranskrip[1];  
                            $dataReport['linkoutput']=$this->linkOutput; 
                            $this->report->setDataReport($dataReport); 
                            $this->report->setMode($_SESSION['outputreport']);
                            $this->report->printNilaiFinal($this->Nilai,true);				
                        }else{
                            $bool=false;
                            $errormessage="Mahasiswa dengan NIM ($nim) statusnya belum lulus !!!.";
                        }
                    break;
                }
			break;			
            case 'btnPrintNilaiFinalAll' :                 
                switch ($_SESSION['outputreport']) {
                    case  'summarypdf' :
                        $messageprintout="Mohon maaf Print out pada mode summary pdf belum kami support.";                
                    break;
                    case  'summaryexcel' :
                        $messageprintout="Mohon maaf Print out pada mode summary excel belum kami support.";                
                    break;
                    case  'excel2007' :
                        $messageprintout="Mohon maaf Print out pada mode excel 2007 tidak kami support.";                
                    break;
                    case 'pdf' :
                        $messageprintout="Mohon maaf Print out pada mode pdf tidak kami support.";                                            
                    break;
                }
            break;
		}		
        if ($bool) {
            $this->lblMessagePrintout->Text=$messageprintout;
            $this->lblPrintout->Text='Transkrip Final';
            $this->modalPrintOut->show();
        }else{
            $this->lblContentMessageError->Text=$errormessage;
            $this->modalMessageError->show();
        }
	}   
	public function closeTranskrip($sender,$param) {	
		unset($_SESSION['currentPageNilaiFinal']['m_NilaiFinal']);
		$this->redirect('nilai.NilaiFinal',true);
	}
}

?>