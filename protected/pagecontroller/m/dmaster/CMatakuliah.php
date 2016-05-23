<?php
prado::using ('Application.MainPageM');
class CMatakuliah extends MainPageM {		    	
	public function onLoad($param) {
		parent::onLoad($param);		       
        $this->showMatakuliah=true;    
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageMatakuliah'])||$_SESSION['currentPageMatakuliah']['page_name']!='m.dmaster.Matakuliah') {
				$_SESSION['currentPageMatakuliah']=array('page_name'=>'m.dmaster.Matakuliah','page_num'=>0,'search'=>false,'idkonsentrasi'=>'none','semester'=>'none');
			}
            $_SESSION['currentPageMatakuliah']['search']=false;
            $_SESSION['outputreport']='excel2007';
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');
            
            $this->tbCmbPs->DataSource=$this->Demik->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();	
			$this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $daftar_semester=Logic_Akademik::$SemesterMatakuliah;
            $this->cmbFilterSemester->DataSource=$daftar_semester;
            $this->cmbFilterSemester->Text=$_SESSION['currentPageMatakuliah']['semester'];
            $this->cmbFilterSemester->DataBind();
            
            $this->populateKonsentrasi();
			$this->populateData();            
		}
	} 
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];        
        $_SESSION['currentPageMatakuliah']['idkonsentrasi']='none';
        $this->populateKonsentrasi();
		$this->populateData();
	}    
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageMatakuliah']['search']);
	}
    public function resetKonsentrasi ($sender,$param) {
		$_SESSION['currentPageMatakuliah']['idkonsentrasi']='none';
        $this->redirect('dmaster.Matakuliah',true);
	}
    public function filterKonsentrasi ($sender,$param) {
        $id=$this->getDataKeyField($sender, $this->RepeaterKonsentrasi);
        $_SESSION['currentPageMatakuliah']['idkonsentrasi']=$id;
        $this->populateKonsentrasi();
        $this->populateData();
    }
    public function populateKonsentrasi () {			
        $datakonsentrasi=$this->DMaster->getListKonsentrasiProgramStudi();        
        $r=array();
        $i=1;
        while (list($k,$v)=each($datakonsentrasi)) {                        
            if ($v['kjur']==$_SESSION['kjur']){
                $idkonsentrasi=$v['idkonsentrasi'];
                $v['jumlah_matkul'] = $this->DB->getCountRowsOfTable("matakuliah WHERE idkonsentrasi=$idkonsentrasi",'idkonsentrasi');                
                $r[$i]=$v;
                $i+=1;
            }
        }        
        $this->RepeaterKonsentrasi->DataSource=$r;
        $this->RepeaterKonsentrasi->DataBind();
    }
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageMatakuliah']['search']=true;
        $this->populateData($_SESSION['currentPageMatakuliah']['search']);
	}
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageMatakuliah']['semester']=$this->cmbFilterSemester->Text;
        $this->populateData();
	}
	protected function populateData ($search=false) {								
        $kjur=$_SESSION['kjur']; 
        $idkur=$this->Demik->getIDKurikulum($kjur);
        if ($search) {
            $str = "SELECT m.kmatkul,m.nmatkul,m.sks,m.semester,m.idkonsentrasi,k.nama_konsentrasi,m.ispilihan,m.islintas_prodi,m.aktif FROM matakuliah m LEFT JOIN konsentrasi k ON (k.idkonsentrasi=m.idkonsentrasi) WHERE idkur=$idkur";			
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'kode' :
                    $cluasa="AND kmatkul='{$idkur}_$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("matakuliah WHERE idkur=$idkur $cluasa",'kmatkul');		            
                    $str = "$str $cluasa";
                break;
                case 'nama' :
                    $cluasa="AND nmatkul LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("matakuliah WHERE idkur=$idkur $cluasa",'kmatkul');		            
                    $str = "$str $cluasa";
                break;
            }
        }else{
            $idkonsentrasi=$_SESSION['currentPageMatakuliah']['idkonsentrasi'];
            $str_konsentrasi = $idkonsentrasi == 'none'?'':" AND m.idkonsentrasi=$idkonsentrasi";
            $semester=$_SESSION['currentPageMatakuliah']['semester'];
            $str_semester= $semester=='none' ?'' :"AND semester=$semester";
            $jumlah_baris=$this->DB->getCountRowsOfTable("matakuliah m WHERE idkur=$idkur $str_konsentrasi $str_semester",'kmatkul');		            
            $str = "SELECT m.kmatkul,m.nmatkul,m.sks,m.semester,m.idkonsentrasi,k.nama_konsentrasi,m.ispilihan,m.islintas_prodi,m.aktif FROM matakuliah m LEFT JOIN konsentrasi k ON (k.idkonsentrasi=m.idkonsentrasi) WHERE idkur=$idkur $str_konsentrasi $str_semester";			
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageMatakuliah']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageMatakuliah']['page_num']=0;}
        $str = "$str ORDER BY semester,kmatkul ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','idkonsentrasi','nama_konsentrasi','ispilihan','islintas_prodi','aktif'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result = array();
        while (list($k,$v)=each($r)) {  
            $kmatkul=$v['kmatkul'];
            $v['kode_matkul']=$this->Demik->getKMatkul($kmatkul);
            if ($v['idkonsentrasi'] == 0) {
                if($v['islintas_prodi'] == 1){
                    $v['keterangan']='Matkul Lintas Prodi';                    			
                }elseif($v['ispilihan'] == 1) {
                    $v['keterangan']='Matkul Pilihan';                    
                }else {
                    $v['keterangan']='-';                    
                }                
            }else {
                $v['keterangan']='Kons. '.$v['nama_konsentrasi'];
            }
            $str = "SELECT GROUP_CONCAT(kmatkul_syarat) AS prasyarat FROM matakuliah_syarat WHERE kmatkul='$kmatkul' GROUP BY kmatkul";            
            $this->DB->setFieldTable(array('prasyarat'));
            $data = $this->DB->getRecord($str);	 
            $prasyarat='-';
            if (isset($data[1])) {                
                $data3 = explode(',', $data[1]['prasyarat']);
                $jumlah_data = count($data3);
                $data4=array();
                for ($i=0;$i<$jumlah_data;$i++) {
                    $kmatkul_prasyarat=  strtoupper($this->Demik->getKMatkul($data3[$i]));
                    $data4[]=$kmatkul_prasyarat;
                }                
                $prasyarat=  implode(',', $data4);
            }
            $v['prasyarat']=$prasyarat;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);        
	}		    
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        
        $sks=Logic_Akademik::$sks;        
        $this->cmbAddSks->DataSource=$sks;			
        $this->cmbAddSks->Text=$result['sks'];
        $this->cmbAddSks->dataBind();        
        
        $datakonsentrasi=$this->DMaster->getListKonsentrasiProgramStudi($_SESSION['kjur']);
        $this->cmbAddKonsentrasi->DataSource=$datakonsentrasi;			
        $this->cmbAddKonsentrasi->Text=$result['idkonsentrasi'];
        $this->cmbAddKonsentrasi->dataBind();        
        
        $this->cmbAddSksTatapMuka->DataSource=$sks;		
        $this->cmbAddSksTatapMuka->Text=$result['sks_tatap_muka'];		
        $this->cmbAddSksTatapMuka->dataBind();
        
        $this->cmbAddSksPraktikum->DataSource=$sks;		
        $this->cmbAddSksPraktikum->Text=$result['sks_praktikum'];
        $this->cmbAddSksPraktikum->dataBind();

        $this->cmbAddSksPraktikLapangan->DataSource=$sks;		
        $this->cmbAddSksPraktikLapangan->Text=$result['praktik_lapangan'];
        $this->cmbAddSksPraktikLapangan->dataBind();
        
        $this->cmbAddSemester->DataSource=  Logic_Akademik::$SemesterMatakuliah;
        $this->cmbAddSemester->Text=$result['semester'];						
        $this->cmbAddSemester->dataBind();
    }
    public function checkKodeMatkul ($sender,$param) {
		$this->idProcess=$sender->getId()=='addKodeMatkul'?'add':'edit';
        $kmatkul=$param->Value;		
        if ($kmatkul != '') {
            try {   
                $kmatkul=$this->Demik->getIDKurikulum($_SESSION['kjur']).'_'.$kmatkul;
                if ($this->hiddenid->Value!=$kmatkul) {                                                            
                    if ($this->DB->checkRecordIsExist('kmatkul','matakuliah',$kmatkul)) {                                
                        throw new Exception ("Kode matakuliah ($kmatkul) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function saveData ($sender,$param) {
		if ($this->Page->isValid) {	
            $idkur=$this->Demik->getIDKurikulum($_SESSION['kjur']);
            $kmatkul=$this->txtAddKodeMatkul->Text;
            $kode_matkul=$idkur."_$kmatkul";
            $nama_matkul=addslashes(strtoupper($this->txtAddNamaMatkul->Text));	
            $sks=$this->cmbAddSks->Text;
            $idkonsentrasi=$this->cmbAddKonsentrasi->Text;            
            if ($idkonsentrasi == 'none') {
                $islindas_prodi=$this->cmbAddMatkulLintasProdi->Text;
                $ispilihan=$islindas_prodi==1?1:$this->cmbAddMatkulPilihan->Text;
            }else{
                $islindas_prodi=0;
                $ispilihan=0;
            }
			$sks_tatap_muka=$this->cmbAddSksTatapMuka->Text;
            $sks_praktikum=$this->cmbAddSksPraktikum->Text;
            $semester=$this->cmbAddSemester->Text;
            $sks_praktik_lapangan=$this->cmbAddSksPraktikLapangan->Text;
			$minimal_nilai=$this->cmbAddNilai->Text;		
			$syarat_ta=($this->chkAddSyaratTa->Checked)?1:0;
			$aktif=($this->chkAddAktif->Checked)?1:0;
            
            $str='INSERT INTO matakuliah (kmatkul,idkur,nmatkul,sks,idkonsentrasi,ispilihan,islintas_prodi,semester,sks_tatap_muka,sks_praktikum,sks_praktik_lapangan,minimal_nilai,syarat_ta,aktif) VALUES ';						
			$str="$str ('$kode_matkul',$idkur,'$nama_matkul','$sks','$idkonsentrasi',$ispilihan,$islindas_prodi,'$semester','$sks_tatap_muka','$sks_praktikum','$sks_praktik_lapangan','$minimal_nilai',$syarat_ta,$aktif)";						
            
            $this->DB->insertRecord($str);
			$this->redirect('dmaster.Matakuliah',true);
        }
    }
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;        
        
        $str = "SELECT kmatkul,nmatkul,sks,idkonsentrasi,ispilihan,islintas_prodi,semester,sks_tatap_muka,sks_praktikum,sks_praktik_lapangan,minimal_nilai,syarat_ta,aktif FROM matakuliah WHERE kmatkul='$id'";
        $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','idkonsentrasi','ispilihan','islintas_prodi','semester','sks_tatap_muka','sks_praktikum','sks_praktik_lapangan','minimal_nilai','syarat_ta','aktif'));
        $r=$this->DB->getRecord($str);
        
        $result=$r[1];        	
        $this->txtEditKodeMatkul->Text=$this->Demik->getKMatkul($result['kmatkul']);
        $this->txtEditNamaMatkul->Text=$result['nmatkul'];			
        
        $sks=Logic_Akademik::$sks;        
        $this->cmbEditSks->DataSource=$sks;			
        $this->cmbEditSks->Text=$result['sks'];
        $this->cmbEditSks->dataBind();        
        
        $datakonsentrasi=$this->DMaster->getListKonsentrasiProgramStudi($_SESSION['kjur']);
        $this->cmbEditKonsentrasi->DataSource=$datakonsentrasi;			
        $this->cmbEditKonsentrasi->Text=$result['idkonsentrasi'];
        $this->cmbEditKonsentrasi->dataBind();        
        
        $this->cmbEditMatkulPilihan->Text=$result['ispilihan'];        
        $this->cmbEditMatkulLintasProdi->Text=$result['islintas_prodi'];        
            
        $this->cmbEditSksTatapMuka->DataSource=$sks;		
        $this->cmbEditSksTatapMuka->Text=$result['sks_tatap_muka'];		
        $this->cmbEditSksTatapMuka->dataBind();
        
        $this->cmbEditSksPraktikum->DataSource=$sks;		
        $this->cmbEditSksPraktikum->Text=$result['sks_praktikum'];
        $this->cmbEditSksPraktikum->dataBind();

        $this->cmbEditSksPraktikLapangan->DataSource=$sks;		
        $this->cmbEditSksPraktikLapangan->Text=$result['praktik_lapangan'];
        $this->cmbEditSksPraktikLapangan->dataBind();
        
        $this->cmbEditSemester->DataSource=  Logic_Akademik::$SemesterMatakuliah;
        $this->cmbEditSemester->Text=$result['semester'];						
        $this->cmbEditSemester->dataBind();
        
        $this->cmbEditNilai->Text=$result['minimal_nilai'];
        $this->chkEditSyaratTa->Checked=($result['syarat_ta']==1)?true:false;
        $this->chkEditAktif->Checked=($result['aktif']==1)?true:false;		
    }
    public function updateData ($sender,$param) {
		if ($this->Page->isValid) {			
            $id=$this->hiddenid->Value;
            $idkur=$this->Demik->getIDKurikulum($_SESSION['kjur']);
            $kmatkul=$this->txtEditKodeMatkul->Text;
            $kode_matkul=$idkur."_$kmatkul";
            $nama_matkul=addslashes(strtoupper($this->txtEditNamaMatkul->Text));	
            $sks=$this->cmbEditSks->Text;
            $idkonsentrasi=$this->cmbEditKonsentrasi->Text;            
            if ($idkonsentrasi == 'none') {
                $islindas_prodi=$this->cmbEditMatkulLintasProdi->Text;
                $ispilihan=$islindas_prodi==1?1:$this->cmbEditMatkulPilihan->Text;
            }else{
                $islindas_prodi=0;
                $ispilihan=0;
            }
			$sks_tatap_muka=$this->cmbEditSksTatapMuka->Text;
            $sks_praktikum=$this->cmbEditSksPraktikum->Text;
            $semester=$this->cmbEditSemester->Text;
            $sks_praktik_lapangan=$this->cmbEditSksPraktikLapangan->Text;
			$minimal_nilai=$this->cmbEditNilai->Text;		
			$syarat_ta=($this->chkEditSyaratTa->Checked)?1:0;
			$aktif=($this->chkEditAktif->Checked)?1:0;
			$str = "UPDATE matakuliah SET kmatkul='$kode_matkul',nmatkul='$nama_matkul',sks='$sks',idkonsentrasi='$idkonsentrasi',ispilihan=$ispilihan,islintas_prodi=$islindas_prodi,sks_tatap_muka='$sks_tatap_muka',sks_praktikum='$sks_praktikum',semester='$semester',sks_praktik_lapangan='$sks_praktik_lapangan',minimal_nilai='$minimal_nilai',syarat_ta=$syarat_ta,aktif=$aktif WHERE kmatkul='$id'";
			$this->DB->updateRecord($str);			
			$this->redirect('dmaster.Matakuliah',true);
		}
	}
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        if ($this->DB->checkRecordIsExist ('kmatkul','penyelenggaraan',$id)) {	
            $kmatkul=$this->Demik->getKMatkul($id);
            $this->lblHeaderMessageError->Text='Menghapus Matakuliah';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus matakuliah dengan kode ($kmatkul) karena sedang digunakan di penyelenggaraan.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("matakuliah WHERE kmatkul='$id'");
            $this->redirect('dmaster.Matakuliah',true);
        }
    }    
    public function printOut($sender,$param) {
        $this->createObj('reportakademik');
        $dataReport['kjur']=$_SESSION['kjur'];
        $nama_ps=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
        $dataReport['nama_ps']=$nama_ps;
        $dataReport['idkur']=$idkur=$this->Demik->getIDKurikulum($_SESSION['kjur']);
        $dataReport['linkoutput']=$this->linkOutput; 
        $this->report->setDataReport($dataReport);
        $this->report->setMode($_SESSION['outputreport']);
        $this->report->printMatakuliah($this->Demik);		
                
        $this->lblPrintout->Text='Daftar Matakuliah';
        $this->modalPrintOut->show();
    }
}
?>
