<?php
prado::using ('Application.MainPageON');
class CDetailNilaiFinal extends MainPageON {		
	public function onLoad($param) {
		parent::onLoad($param);				
		$this->showSubMenuAkademikNilai=true;
        $this->showNilaiFinal=true;    
        $this->createObj('Nilai');
		if (!$this->IsPostback&&!$this->IsCallback) {
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
            if (!isset($_SESSION['currentPageNilaiFinal']['DataMHS']['nim']) ){
                throw new Exception('Mohon kembali ke halaman Nilai Final.');
            }
            $datamhs=$_SESSION['currentPageNilaiFinal']['DataMHS'];
            $nim=$datamhs['nim'];
            $this->Nilai->setDataMHS($datamhs);
            $str = "SELECT nomor_transkrip,predikat_kelulusan,tanggal_lulus,judul_skripsi,iddosen_pembimbing,iddosen_pembimbing2,iddosen_ketua,iddosen_pemket,tahun,idsmt FROM transkrip_asli WHERE nim='$nim'";
            $this->DB->setFieldTable(array('nomor_transkrip','predikat_kelulusan','tanggal_lulus','judul_skripsi','iddosen_pembimbing','iddosen_pembimbing2','iddosen_ketua','iddosen_pemket','tahun','idsmt'));
            $r=$this->DB->getRecord($str);
            $datatranskrip=$r[1];
            
            $daftar_dosen=$this->DMaster->removeIdFromArray($this->DMaster->getDaftarDosen(),'none');
            $this->cmbEditDosenPembimbing->DataSource=$daftar_dosen;
            $this->cmbEditDosenPembimbing->dataBind();            
            $this->cmbEditDosenPembimbing->Text=$datatranskrip['iddosen_pembimbing'];

            $this->cmbEditDosenPembimbing2->DataSource=$daftar_dosen;
            $this->cmbEditDosenPembimbing2->dataBind();
            $this->cmbEditDosenPembimbing2->Text=$datatranskrip['iddosen_pembimbing2'];	
            if (isset($r[1])) {
                
                $this->hiddennomortranskrip->Value=$datatranskrip['nomor_transkrip'];
                $this->txtEditNomorTranskrip->Text=$datatranskrip['nomor_transkrip'];
                
                $this->cmbEditPredikatKelulusan->Text=$datatranskrip['predikat_kelulusan'];
                $this->txtEditTanggalLulus->Text=$this->TGL->tanggal('d-m-Y',$datatranskrip['tanggal_lulus']);
                
                $this->txtEditJuduluSkripsi->Text=$datatranskrip['judul_skripsi'];
                
                $datatranskrip['nama_pembimbing1']=$this->DMaster->getNamaDosenPembimbing($datatranskrip['iddosen_pembimbing']);
                $datatranskrip['nama_pembimbing2']=$this->DMaster->getNamaDosenPembimbing($datatranskrip['iddosen_pembimbing2']);            
                $_SESSION['currentPageNilaiFinal']['DataNilai']=$datatranskrip;
                
                $transkrip = $this->Nilai->getTranskrip(false);           

                $this->RepeaterS->DataSource=$transkrip;
                $this->RepeaterS->dataBind();	
            }else{
                $transkrip = $this->Nilai->getTranskrip(false);           

                $this->RepeaterS->DataSource=$transkrip;
                $this->RepeaterS->dataBind();	
            }

        } catch (Exception $ex) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$ex->getMessage();
        }        
	}
    public function checkNoTranskrip ($sender,$param) {
        $no_transkrip=addslashes($param->Value);
		try {			
			if ($this->hiddennomortranskrip->Value!=$no_transkrip) {
				if ($this->DB->checkRecordIsExist('nomor_transkrip','transkrip_asli',$no_transkrip)) {
					throw new Exception ("Nomor Transkrip NiLAI ($no_transkrip) telah ada, silahkan ganti dengan yang lain");
				}
			}
		}catch (Exception $e) {
			$sender->ErrorMessage = $e->getMessage();
			$param->IsValid=false;
		}
	}
	public function saveData ($sender,$param) {
		if ($this->IsValid) {						
            $datamhs=$_SESSION['currentPageNilaiFinal']['DataMHS'];
            $nim=$datamhs['nim'];
            $ta=$datamhs['ta'];
            $idsmt=$datamhs['idsmt'];
            
			$no_transkrip=$this->txtEditNomorTranskrip->Text;			
			$predikat=$this->cmbEditPredikatKelulusan->Text;
			$tanggal_lulus=date('Y-m-d',$this->txtEditTanggalLulus->TimeStamp);						
			$pembimbing=$this->cmbEditDosenPembimbing->Text;						
			$pembimbing2=$this->cmbEditDosenPembimbing2->Text;						
			$judul_skripsi=strtoupper(addslashes($this->txtEditJuduluSkripsi->Text));						
			$ketua=$this->setup->getSettingValue('id_penandatangan_transkrip');						
			$pemket=$this->setup->getSettingValue('id_penandatangan_khs');	
            
            if ($this->DB->checkRecordIsExist('nim','transkrip_asli',$nim)){
                $str = "UPDATE transkrip_asli SET nomor_transkrip='$no_transkrip',predikat_kelulusan='$predikat',tanggal_lulus='$tanggal_lulus',judul_skripsi='$judul_skripsi',iddosen_pembimbing='$pembimbing',iddosen_pembimbing2='$pembimbing2',iddosen_ketua='$ketua',iddosen_pemket='$pemket',tahun='$ta',idsmt='$idsmt' WHERE nim='$nim'";
                $this->DB->updateRecord($str);
                
                foreach($this->RepeaterS->Items as $inputan) {						
                    $item=$inputan->cmbNilai->getNamingContainer();
                    $idtranskrip_detail=$this->RepeaterS->DataKeys[$item->getItemIndex()]; 						
                    $n_kual_sebelumnya=$inputan->hiddenNilaiSebelumnya->Value;						
                    $n_kual=$inputan->cmbNilai->Text=='none'?'':$inputan->cmbNilai->Text;
                    if ($n_kual == '' || $n_kual == '-') {						
                        $this->DB->deleteRecord("transkrip_asli_detail WHERE idtranskrip_detail=$idtranskrip_detail");
                    }elseif ($n_kual_sebelumnya != $n_kual) {		
                        $str="UPDATE transkrip_asli_detail SET n_kual='$n_kual' WHERE idtranskrip_detail=$idtranskrip_detail";						
                        $this->DB->updateRecord($str);   
                    }
                }
            }else{
                $str = "INSERT transkrip_asli SET nim='$nim',nomor_transkrip='$no_transkrip',predikat_kelulusan='$predikat',tanggal_lulus='$tanggal_lulus',judul_skripsi='$judul_skripsi',iddosen_pembimbing='$pembimbing',iddosen_pembimbing2='$pembimbing2',iddosen_ketua='$ketua',iddosen_pemket='$pemket',tahun='$ta',idsmt='$idsmt'";
                $this->DB->insertRecord($str);
            }
			$this->redirect('nilai.DetailNilaiFinal',true);
		}
	}
    public function addData ($sender,$param) {
        if ($this->IsValid) {	
            $this->createObj('Log');
            $datamhs=$_SESSION['currentPageNilaiFinal']['DataMHS'];
            $nim=$datamhs['nim'];
            $datasource=$this->cmbDataSource->Text;
            switch ($datasource) {
                case 'transkrip_krs' :
                    $str = "SELECT vnk.kmatkul,vnk.nmatkul,vnk.sks,semester,IF(char_length(COALESCE(vnk2.n_kual,'-'))>0,vnk2.n_kual,'') AS n_kual FROM v_nilai_khs vnk,(SELECT idkrsmatkul,MIN(n_kual) AS n_kual FROM `v_nilai_khs` WHERE nim='$nim' GROUP BY kmatkul ORDER BY (semester+0), kmatkul ASC) AS vnk2 WHERE vnk.idkrsmatkul=vnk2.idkrsmatkul";
                    $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','n_kual'));
                    $r=$this->DB->getRecord($str);
                    if (isset($r[1])) {
                        $this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'");
                        while (list($k,$v)=each($r)) {
                            $kmatkul=$v['kmatkul'];    
                            $nmatkul=$v['nmatkul']; 
                            $n_kual=$v['n_kual'];
                            $sks=$v['sks'];
                            $semester=$v['semester'];                
                            $str = "INSERT INTO transkrip_asli_detail SET nim='$nim',kmatkul='$kmatkul',nmatkul='$nmatkul',sks='$sks',semester='$semester',n_kual='$n_kual'";        
                            $this->DB->insertRecord($str);
                        }
                        $this->redirect('nilai.DetailNilaiFinal',true);
                    }else{
                        $this->lblHeaderMessageError->Text='Tambah Nilai';
                        $this->lblContentMessageError->Text='Data nilai matakuliah kosong';
                        $this->modalMessageError->show();
                    }
                break;
                case 'konversi' :
                    $iddata_konversi=$datamhs['iddata_konversi'];
                    $str = "SELECT kmatkul,nmatkul,sks,semester,n_kual FROM v_konversi2 WHERE iddata_konversi='$iddata_konversi'";
                    $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','n_kual'));
                    $r=$this->DB->getRecord($str);
                    if (isset($r[1])) {
                        $this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'");
                        while (list($k,$v)=each($r)) {
                            $kmatkul=$v['kmatkul'];    
                            $nmatkul=$v['nmatkul']; 
                            $n_kual=$v['n_kual'];
                            $sks=$v['sks'];
                            $semester=$v['semester'];                
                            $str = "INSERT INTO transkrip_asli_detail SET nim='$nim',kmatkul='$kmatkul',nmatkul='$nmatkul',sks='$sks',semester='$semester',n_kual='$n_kual'";        
                            $this->DB->insertRecord($str);
                        }
                        $this->redirect('nilai.DetailNilaiFinal',true);
                    }else{
                        $this->lblHeaderMessageError->Text='Tambah Nilai';
                        $this->lblContentMessageError->Text='Data nilai matakuliah kosong';
                        $this->modalMessageError->show();
                    }
                break;
                case 'konversi_transkripkrs' :
                    $iddata_konversi=$datamhs['iddata_konversi'];
                    $str = "SELECT kmatkul,nmatkul,sks,semester,n_kual FROM v_konversi2 WHERE iddata_konversi='$iddata_konversi'";
                    $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','n_kual'));
                    $r=$this->DB->getRecord($str);
                    $this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'");
                    if (isset($r[1])) {                        
                        $str = "SELECT vnk.kmatkul,vnk.nmatkul,vnk.sks,semester,IF(char_length(COALESCE(vnk2.n_kual,'-'))>0,vnk2.n_kual,'') AS n_kual FROM v_nilai_khs vnk,(SELECT idkrsmatkul,MIN(n_kual) AS n_kual FROM `v_nilai_khs` WHERE nim='$nim' GROUP BY kmatkul ORDER BY (semester+0), kmatkul ASC) AS vnk2 WHERE vnk.idkrsmatkul=vnk2.idkrsmatkul";
                        $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','n_kual'));
                        $r2=$this->DB->getRecord($str);
                        while (list($k,$v)=each($r)) {
                            $kmatkul=$v['kmatkul'];    
                            $nmatkul=$v['nmatkul']; 
                            $n_kual=$v['n_kual'];
                            $sks=$v['sks'];
                            $semester=$v['semester'];   

                            $str = "INSERT INTO transkrip_asli_detail SET nim='$nim',kmatkul='$kmatkul',nmatkul='$nmatkul',sks='$sks',semester='$semester',n_kual='$n_kual'";        
                            $this->DB->insertRecord($str);
                        } 
                        while (list($k,$v)=each($r2)) {
                            $kmatkul=$v['kmatkul'];    
                            $nmatkul=$v['nmatkul']; 
                            $n_kual=$v['n_kual'];
                            $sks=$v['sks'];
                            $semester=$v['semester'];   

                            $str = "INSERT INTO transkrip_asli_detail SET nim='$nim',kmatkul='$kmatkul',nmatkul='$nmatkul',sks='$sks',semester='$semester',n_kual='$n_kual'";        
                            $this->DB->insertRecord($str);
                        }                    
                        $this->redirect('nilai.DetailNilaiFinal',true);
                    }else{
                        $this->lblHeaderMessageError->Text='Tambah Nilai';
                        $this->lblContentMessageError->Text='Data nilai matakuliah kosong';
                        $this->modalMessageError->show();
                    }                    
                break;
            }
        }
    }
    public function deleteRecord($sender,$param) {	
        $idtranskrip_detail = $this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("transkrip_asli_detail WHERE idtranskrip_detail=$idtranskrip_detail");
        $this->redirect('nilai.DetailNilaiFinal',true,array('id'=>$nim));
    }
    public function resetTranskrip ($sender,$param) {
        $datamhs=$_SESSION['currentPageNilaiFinal']['DataMHS'];
        $nim=$datamhs['nim']; 
        $this->DB->deleteRecord("transkrip_asli_detail WHERE nim='$nim'");
        $this->redirect('nilai.DetailNilaiFinal',true);
    }
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');          
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';   
        
        $dataReport=$_SESSION['currentPageNilaiFinal']['DataMHS']; 
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
                    $messageprintout='Nilai Final : ';                     

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
                    $dataReport['dataTranskrip']=$_SESSION['currentPageNilaiFinal']['DataNilai'];
                    $dataReport['linkoutput']=$this->linkOutput; 
                    $this->report->setDataReport($dataReport); 
                    $this->report->setMode($_SESSION['outputreport']);
                    $this->report->printTranskripFinal($this->Nilai,true);				
                break;
            }
            $this->lblMessagePrintout->Text=$messageprintout;
            $this->lblPrintout->Text='Nilai Final';
            $this->modalPrintOut->show();
        }else{
            $this->lblContentMessageError->Text="Mahasiswa dengan NIM ($nim) statusnya belum lulus !!!.";
            $this->modalMessageError->show();
        }
	} 
    public function closeDetail ($sender,$param) {
        unset($_SESSION['currentPageNilaiFinal']['DataMHS']);
        unset($_SESSION['currentPageNilaiFinal']['DataNilai']);
        $this->redirect('nilai.NilaiFinal',true);
    }
}	

?>