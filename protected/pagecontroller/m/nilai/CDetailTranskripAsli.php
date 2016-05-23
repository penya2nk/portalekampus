<?php
prado::using ('Application.MainPageM');
class CDetailTranskripAsli extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->Pengguna->moduleForbiden('akademik','transkrip_sementara');
		$this->showSubMenuAkademikNilai=true;
        $this->showTranskripAsli=true;    
        $this->createObj('Nilai');
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailTranskripAsli'])||$_SESSION['currentPageDetailTranskripAsli']['page_name']!='m.nilai.DetailTranskripAsli') {
				$_SESSION['currentPageDetailTranskripAsli']=array('page_name'=>'m.nilai.DetailTranskripAsli','page_num'=>0,'search'=>false,'DataMHS'=>array(),'DataTranskrip');
			}  
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
			$this->populateData();	
		}
	}
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }
	protected function populateData() {		
        try {
            $nim=addslashes($this->request['id']);            				
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,idkelas,k_status FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE nim='$nim'";
            $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','idkelas','k_status'));
            $r=$this->DB->getRecord($str);				
            
            if (!isset($r[1])) {
                $_SESSION['currentPageDetailTranskripAsli']['DataMHS']=array();
                $_SESSION['currentPageDetailTranskripAsli']['DataTranskrip']=array();
                throw new Exception("Mahasiswa dengan NIM ($nim) tidak terdaftar.");
            }
            $datamhs=$r[1];
            $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
            $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
            $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
            $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
            $datamhs['iddata_konversi']=$this->Nilai->isMhsPindahan($nim,true);
            $this->Nilai->setDataMHS($datamhs);
            $_SESSION['currentPageDetailTranskripAsli']['DataMHS']=$datamhs;
            
            $str = "SELECT nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket,tahun,idsmt FROM transkrip_asli WHERE nim='$nim'";
            $this->DB->setFieldTable(array('nomor_transkrip','predikat_kelulusan','tanggal_lulus','judul_skripsi','iddosen_pembimbing','iddosen_pembimbing2','iddosen_ketua','iddosen_pemket','tahun','idsmt'));
            $datatranskrip=$this->DB->getRecord($str);
            if (!isset($datatranskrip[1]) ) {
                $_SESSION['currentPageDetailTranskripAsli']['DataMHS']=array();
                $_SESSION['currentPageDetailTranskripAsli']['DataTranskrip']=array();
                throw new Exception("Mahasiswa dengan NIM ($nim) tidak terdaftar di Transkrip Asli.");
            }
            $datatranskrip[1]['nama_pembimbing1']=$this->DMaster->getNamaDosenPembimbing($datatranskrip[1]['iddosen_pembimbing']);
            $datatranskrip[1]['nama_pembimbing2']=$this->DMaster->getNamaDosenPembimbing($datatranskrip[1]['iddosen_pembimbing2']);            
            $_SESSION['currentPageDetailTranskripAsli']['DataTranskrip']=$datatranskrip[1];
            
            $this->hiddennomortranskrip->Value=$datatranskrip[1]['nomor_transkrip'];			
			$this->txtEditNomorTranskrip->Text=$datatranskrip[1]['nomor_transkrip'];			
			$this->cmbEditPredikatKelulusan->Text=$datatranskrip[1]['predikat_kelulusan'];			
			$this->txtEditTanggalLulus->Text=$this->TGL->tanggal('d-m-Y',$datatranskrip[1]['tanggal_lulus']);
            $daftar_dosen=$this->DMaster->removeIdFromArray($this->DMaster->getDaftarDosen(),'none');
            $this->cmbEditDosenPembimbing->DataSource=$daftar_dosen;
            $this->cmbEditDosenPembimbing->dataBind();            
			$this->cmbEditDosenPembimbing->Text=$datatranskrip[1]['iddosen_pembimbing'];
            
            $this->cmbEditDosenPembimbing2->DataSource=$daftar_dosen;
            $this->cmbEditDosenPembimbing2->dataBind();
			$this->cmbEditDosenPembimbing2->Text=$datatranskrip[1]['iddosen_pembimbing2'];												
            
			$this->txtEditJuduluSkripsi->Text=$datatranskrip[1]['judul_skripsi'];						            
            
            $transkrip = $this->Nilai->getTranskrip(false);            
            
            $this->RepeaterS->DataSource=$transkrip;
            $this->RepeaterS->dataBind();		
            
            $this->btnChangeStatus->Visible=$datamhs['k_status']=='L' ? false : true;
        } catch (Exception $ex) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$ex->getMessage();
        }        
	}
    public function changeStatus ($sender,$param) {	
        $datamhs=$_SESSION['currentPageDetailTranskripAsli']['DataMHS'];
        $datatranskrip=$_SESSION['currentPageDetailTranskripAsli']['DataTranskrip'];
        $nim=$datamhs['nim'];
        $this->Nilai->setDataMHS(array('nim'=>$nim));
        $idsmt=$datatranskrip['idsmt'];
        $tahun=$datatranskrip['tahun'];
        $this->DB->query('BEGIN');
        $datadulang=$this->Nilai->getDataDulang ($idsmt,$tahun);
        $iddulang=$datadulang['iddulang'];
        $k_status=$datamhs['k_status'];
        if ($iddulang > 0) {
            $str = "UPDATE dulang SET status_sebelumnya='$k_status',k_status='L' WHERE iddulang=$iddulang";
            $this->DB->updateRecord($str);            
        }else{
            $idkelas=$datamhs['idkelas'];            
            $str = "INSERT INTO dulang (iddulang,nim,tahun,idsmt,tanggal,idkelas,status_sebelumnya,k_status) VALUES (NULL,'$nim','$tahun','$idsmt',NOW(),'$idkelas','$k_status','L')";
            $this->DB->insertRecord($str);            
        } 
        $this->Nilai->updateRegisterMHS('status','L');        
        $this->DB->query('COMMIT');
        $this->redirect('nilai.DetailTranskripAsli',true,array('id'=>$datamhs['nim']));
    }
    public function checkNoTranskrip ($sender,$param) {
		try {
			$no_transkrip=$this->txtEditNomorTranskrip->Text;
			if ($this->hiddennomortranskrip->Value!=$no_transkrip) {
				if ($this->DB->checkRecordIsExist('nomor_transkrip','transkrip_asli',$no_transkrip)) {
					throw new Exception ("Nomor Transkrip ($no_transkrip) telah ada, silahkan ganti dengan yang lain");
				}
			}
		}catch (Exception $e) {
			$sender->ErrorMessage = $e->getMessage();
			$param->IsValid=false;
		}
	}
	public function updateData ($sender,$param) {
		if ($this->IsValid) {						
            $datamhs=$_SESSION['currentPageDetailTranskripAsli']['DataMHS'];
            $nim=$datamhs['nim'];
			$no_transkrip=$this->hiddennomortranskrip->Value;			
			$predikat=$this->cmbEditPredikatKelulusan->Text;
			$tanggal_lulus=date('Y-m-d',$this->txtEditTanggalLulus->TimeStamp);						
			$pembimbing=$this->cmbEditDosenPembimbing->Text;						
			$pembimbing2=$this->cmbEditDosenPembimbing2->Text;						
			$judul_skripsi=strtoupper(addslashes($this->txtEditJuduluSkripsi->Text));						
			$ketua=$this->setup->getSettingValue('id_penandatangan_transkrip');						
			$pemket=$this->setup->getSettingValue('id_penandatangan_khs');						
			$this->DB->query('BEGIN');
			
            $str = "UPDATE transkrip_asli SET nomor_transkrip='$no_transkrip',predikat_kelulusan='$predikat',tanggal_lulus='$tanggal_lulus',judul_skripsi='$judul_skripsi',iddosen_pembimbing='$pembimbing',iddosen_pembimbing2='$pembimbing2',iddosen_ketua='$ketua',iddosen_pemket='$pemket' WHERE nim='$nim'";
            $this->DB->query('BEGIN');
            if ($this->DB->updateRecord($str)) {				
                $this->createObj('Log');
                foreach($this->RepeaterS->Items as $inputan) {						
                    $item=$inputan->cmbNilai->getNamingContainer();
                    $kmatkul=$this->RepeaterS->DataKeys[$item->getItemIndex()]; 						
                    $n_kual_sebelumnya=$inputan->hiddenNilaiSebelumnya->Value;						
                    $n_kual=$inputan->cmbNilai->Text=='none'?'':$inputan->cmbNilai->Text;		
                    if ($n_kual_sebelumnya != $n_kual) {				
                        $nmatkul=$inputan->hiddenNMatkul->Value;
                        $str="UPDATE transkrip_asli_detail SET n_kual='$n_kual' WHERE nim='$nim' AND kmatkul='$kmatkul'";						
                        $this->DB->updateRecord($str);
                        if ($n_kual_sebelumnya == '')
                            $this->Log->insertLogIntoTranskripAsli($nim,$kmatkul,$nmatkul,'input',$n_kual);
                        else
                            $this->Log->insertLogIntoTranskripAsli($nim,$kmatkul,$nmatkul,'update',$n_kual_sebelumnya,$n_kual);
                    }
                }
                $this->DB->query('COMMIT');
            }else {
                $this->DB->query('ROLLBACK');
            }				
			$this->redirect('nilai.DetailTranskripAsli',true,array('id'=>$nim));
		}
	}
    public function deleteRecord($sender,$param) {	
        $kmatkul = $this->getDataKeyField($sender,$this->RepeaterS);
        $datamhs=$_SESSION['currentPageDetailTranskripAsli']['DataMHS'];
        $nim=$datamhs['nim'];
		$this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim' AND kmatkul='$kmatkul'");
        $this->redirect('nilai.DetailTranskripAsli',true,array('id'=>$nim));
    }
    public function resetTranskrip ($sender,$param) {
        $datamhs=$_SESSION['currentPageDetailTranskripAsli']['DataMHS'];
        $nim=$datamhs['nim'];        
        $this->DB->query('BEGIN');
        if ($this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'")) {
            $str = "INSERT INTO transkrip_asli_detail (nim,kmatkul,nmatkul,sks,semester,n_kual)  SELECT '$nim',vnk.kmatkul,vnk.nmatkul,vnk.sks,semester,IF(char_length(COALESCE(vnk2.n_kual,''))>0,vnk2.n_kual,'-') AS n_kual FROM v_nilai_khs vnk,(SELECT idkrsmatkul,MIN(n_kual) AS n_kual FROM `v_nilai_khs` WHERE nim='$nim' GROUP BY kmatkul ORDER BY (semester+0), kmatkul ASC) AS vnk2 WHERE vnk.idkrsmatkul=vnk2.idkrsmatkul";        
            $this->DB->insertRecord($str);
            $this->DB->query('COMMIT');
        }else{
            $this->DB->query('ROLLBACK');
        }
        $this->redirect('nilai.DetailTranskripAsli',true,array('id'=>$nim));
    }
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');          
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';   
        
        $dataReport=$_SESSION['currentPageDetailTranskripAsli']['DataMHS']; 
        $nim=$dataReport['nim'];
        if ($dataReport['k_status'] == 'L') {
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
                    $messageprintout='Transkrip Asli : ';                     

                    $dataReport['nama_jabatan_transkrip']=$this->setup->getSettingValue('nama_jabatan_transkrip');
                    $dataReport['nama_penandatangan_transkrip']=$this->setup->getSettingValue('nama_penandatangan_transkrip');
                    $dataReport['jabfung_penandatangan_transkrip']=$this->setup->getSettingValue('jabfung_penandatangan_transkrip');
                    $dataReport['nidn_penandatangan_transkrip']=$this->setup->getSettingValue('nidn_penandatangan_transkrip');

                    //biasayanya sama sehingga menggunakan yang KHS
                    $dataReport['nama_jabatan_khs']=$this->setup->getSettingValue('nama_jabatan_khs');
                    $dataReport['nama_penandatangan_khs']=$this->setup->getSettingValue('nama_penandatangan_khs');
                    $dataReport['jabfung_penandatangan_khs']=$this->setup->getSettingValue('jabfung_penandatangan_khs');
                    $dataReport['nidn_penandatangan_khs']=$this->setup->getSettingValue('nidn_penandatangan_khs');
                    $dataReport['tanggalterbit']=date ('Y-m-d',$this->txtViewTanggalTerbit->TimeStamp);
                    $dataReport['dataTranskrip']=$_SESSION['currentPageDetailTranskripAsli']['DataTranskrip'];  
                    $dataReport['linkoutput']=$this->linkOutput; 
                    $this->report->setDataReport($dataReport); 
                    $this->report->setMode($_SESSION['outputreport']);
                    $this->report->printTranskripAsli($this->Nilai,true);				
                break;
            }
            $this->lblMessagePrintout->Text=$messageprintout;
            $this->lblPrintout->Text='Transkrip Asli';
            $this->modalPrintOut->show();
        }else{
            $this->lblContentMessageError->Text="Mahasiswa dengan NIM ($nim) statusnya belum lulus !!!.";
            $this->modalMessageError->show();
        }
	}    
}	

?>