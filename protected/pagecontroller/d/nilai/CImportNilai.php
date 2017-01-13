<?php
prado::using ('Application.MainPageD');
class CImportNilai extends MainPageD {    
   	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showImportNilai=true;    
        $this->createObj('Akademik');        
        $this->createObj('Nilai');        
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageImportNilai'])||$_SESSION['currentPageImportNilai']['page_name']!='d.nilai.ImportNilai') {
				$_SESSION['currentPageImportNilai']=array('page_name'=>'d.nilai.ImportNilai','page_num'=>0,'search'=>false,'DataNilai'=>array());
			}  
            try {
                $idkelas_mhs=addslashes($this->request['id']);
                $this->Demik->getInfoKelas($idkelas_mhs);                
                if (!isset($this->Demik->InfoKelas['idkelas_mhs'])) {                                                
                    throw new Exception ("Kelas Mahasiswa dengan id ($idkelas_mhs) tidak terdaftar.");		
                }     
                $infokelas=$this->Demik->InfoKelas;
                $this->Demik->InfoKelas['namakelas']=$this->DMaster->getNamaKelasByID($infokelas['idkelas']).'-'.chr($infokelas['nama_kelas']+64);
                $this->Demik->InfoKelas['hari']=$this->TGL->getNamaHari($infokelas['hari']);
               
                $_SESSION['currentPageImportNilai']['DataNilai']=$this->Demik->InfoKelas;
                $this->populateData();	             
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}
	}   
	protected function populateData() {	
        $datanilai=$_SESSION['currentPageImportNilai']['DataNilai'];
        $idkelas_mhs=$datanilai['idkelas_mhs'];
        $str = "SELECT vkm.idkrsmatkul,vdm.nim,vdm.nama_mhs,ni.persentase_quiz, ni.persentase_tugas, ni.persentase_uts, ni.persentase_uas, ni.persentase_absen, ni.nilai_quiz, ni.nilai_tugas, ni.nilai_uts, ni.nilai_uas, ni.nilai_absen, ni.n_kuan,ni.n_kual FROM nilai_imported ni JOIN v_krsmhs vkm ON (vkm.idkrsmatkul=ni.idkrsmatkul) JOIN v_datamhs vdm ON (vkm.nim=vdm.nim) WHERE ni.idkelas_mhs=$idkelas_mhs AND vkm.sah=1 AND vkm.batal=0 ORDER BY vdm.nama_mhs ASC";        
        $this->DB->setFieldTable(array('idkrsmatkul','nim','nama_mhs','persentase_quiz', 'persentase_tugas', 'persentase_uts', 'persentase_uas', 'persentase_absen', 'nilai_quiz', 'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_absen','n_kuan','n_kual'));
        $r=$this->DB->getRecord($str);	           

        $this->RepeaterS->DataSource=$r;
        $this->RepeaterS->dataBind();	                
	}
    public function fileUploaded($sender,$param) {
        if($sender->HasFile) {
            $phpexcel=BASEPATH.'protected/lib/excel/';
            define ('PHPEXCEL_ROOT',$phpexcel);
            set_include_path(get_include_path() . PATH_SEPARATOR . $phpexcel);
            require_once ('PHPExcel.php');
            
            $inputFileName=$sender->LocalName;
            try {
                $file_type=$sender->FileType;
                $bool=false;
                switch ($file_type) {
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' :
                        $bool=true;
                    break;
                }
                if (!$bool) {
                    throw new Exception ("Tipe file tidak kenal. Untuk saat ini, hanya mendukung file excel versi 2007 ke atas.");
                }
                $userid=$this->Pengguna->getDataUser('iddosen');
                $datanilai=$_SESSION['currentPageImportNilai']['DataNilai'];
                $idkelas_mhs=$datanilai['idkelas_mhs'];
                $persentase_quiz=$datanilai['persen_quiz'] > 0 ?number_format($datanilai['persen_quiz']/100,2):0;
                $persentase_tugas=$datanilai['persen_tugas'] > 0 ?number_format($datanilai['persen_tugas']/100,2):0;
                $persentase_uts=$datanilai['persen_uts'] > 0 ?number_format($datanilai['persen_uts']/100,2):0;
                $persentase_uas=$datanilai['persen_uas'] > 0 ?number_format($datanilai['persen_uas']/100,2):0;
                $persentase_absen=$datanilai['persen_absen'] > 0 ?number_format($datanilai['persen_absen']/100,2):0;
                
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
                
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow(); 
                $highestColumn = $sheet->getHighestColumn();
                
                for ($row = 2; $row <= $highestRow; $row++){ 
                    //  Read a row of data into an array
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,NULL,TRUE,FALSE);
                    $idkrsmatkul=$rowData[0][0];
                    $nilai_quiz=$rowData[0][3];
                    $nilai_tugas=$rowData[0][4];
                    $nilai_uts=$rowData[0][5];
                    $nilai_uas=$rowData[0][6];
                    $nilai_absen=$rowData[0][7];
                    $n_kuan=($persentase_quiz*$nilai_quiz)+($persentase_tugas*$nilai_tugas)+($persentase_uts*$nilai_uts)+($persentase_uas*$nilai_uas)+($persentase_absen*$nilai_absen);
                    $n_kual=$this->Nilai->getRentangNilaiNKuan($n_kuan);
                    
                    $str = "REPLACE INTO nilai_imported (idkrsmatkul,idkelas_mhs,persentase_quiz, persentase_tugas, persentase_uts, persentase_uas, persentase_absen, nilai_quiz, nilai_tugas, nilai_uts, nilai_uas, nilai_absen, n_kuan,n_kual) VALUES ($idkrsmatkul,$idkelas_mhs,'$persentase_quiz','$persentase_tugas','$persentase_uts','$persentase_uas','$persentase_absen','$nilai_quiz','$nilai_tugas','$nilai_uts','$nilai_uas','$nilai_absen','$n_kuan','$n_kual')";																				
                    $this->DB->insertRecord($str);
                }
                
                $this->redirect("nilai.ImportNilai", true,array('id'=>$idkelas_mhs));
            } catch (Exception $e) {
                $this->modalMessageError->show();
                $this->lblContentMessageError->Text='Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            }
            
        }
    }
    
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
//            $idkelas_mhs=$_SESSION['currentPageImportNilai']['DataNilai']['idkelas_mhs'];
//            $userid=$this->Pengguna->getDataUser('iddosen');
//            foreach ($this->RepeaterS->Items As $inputan) {
//                if ($inputan->chkProcess->Checked) {
//                    $item=$inputan->txtNilaiQuiz->getNamingContainer();
//                    $idkrsmatkul=$this->RepeaterS->DataKeys[$item->getItemIndex()];
//                    $persentase_quiz=$inputan->hiddenpersenquiz->Value;
//                    $persentase_tugas=$inputan->hiddenpersentugas->Value;
//                    $persentase_uts=$inputan->hiddenpersenuts->Value;
//                    $persentase_uas=$inputan->hiddenpersenuas->Value;
//                    $persentase_absen=$inputan->hiddenpersenabsen->Value;
//
//                    $nilai_quiz=addslashes($inputan->txtNilaiQuiz->Text);
//                    $nilai_tugas=addslashes($inputan->txtNilaiTugas->Text);
//                    $nilai_uts=addslashes($inputan->txtNilaiUTS->Text);
//                    $nilai_uas=addslashes($inputan->txtNilaiUAS->Text);
//                    $nilai_absen=addslashes($inputan->txtNilaiAbsen->Text);                    
//                    $n_kuan=($persentase_quiz*$nilai_quiz)+($persentase_tugas*$nilai_tugas)+($persentase_uts*$nilai_uts)+($persentase_uas*$nilai_uas)+($persentase_absen*$nilai_absen);
//                    $n_kual=$this->Nilai->getRentangNilaiNKuan($n_kuan);
//                    
//                    $str = "REPLACE INTO nilai_matakuliah (idkrsmatkul,persentase_quiz, persentase_tugas, persentase_uts, persentase_uas, persentase_absen, nilai_quiz, nilai_tugas, nilai_uts, nilai_uas, nilai_absen, n_kuan,n_kual,userid_input,tanggal_input,bydosen) VALUES ($idkrsmatkul,'$persentase_quiz','$persentase_tugas','$persentase_uts','$persentase_uas','$persentase_absen','$nilai_quiz','$nilai_tugas','$nilai_uts','$nilai_uas','$nilai_absen','$n_kuan','$n_kual',$userid,NOW(),1)";																				
//                    $this->DB->insertRecord($str);
//                }
//            }
//            $this->redirect("nilai.ImportNilai", true,array('id'=>$idkelas_mhs));
        }
        
    }
    public function resetData ($sender,$param) {
        if ($this->IsValid) {
            $idkelas_mhs=$_SESSION['currentPageImportNilai']['DataNilai']['idkelas_mhs'];
            $this->DB->deleteREcord("nilai_imported WHERE idkelas_mhs=$idkelas_mhs");
            $this->redirect("nilai.ImportNilai", true,array('id'=>$idkelas_mhs));
        }
    }
    public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        
        $dataReport=$_SESSION['currentPageImportNilai']['DataNilai'];
        $this->lblMessagePrintout->Text='Kelas ' . $dataReport['namakelas'];
        $dataReport['linkoutput']=$this->linkOutput; 
        $this->report->setDataReport($dataReport); 
        $this->report->setMode('excel2007');
        $this->report->printPesertaImportNilai();
       
        $this->lblPrintout->Text='Daftar Peserta Matakuliah';
        $this->modalPrintOut->show();
    }
}	

?>