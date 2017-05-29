<?php
prado::using ('Application.MainPageMB');
class CSoalPMB extends MainPageMB {
    public $DataUjian;
    public function onLoad ($param) {
        parent::onLoad ($param);        
        $this->showSoalPMB = true;	    
        $this->createObj('Akademik');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	            
            try {          
                $no_formulir=$this->Pengguna->getDataUser('username');
                $this->Demik->setDataMHS(array('no_formulir'=>$no_formulir));                
                if ($this->Demik->isNoFormulirExist()) {
                    if ($this->Demik->isMhsRegistered(true)) {
                        throw new Exception ('Anda sudah ter-register sebagai mahasiswa,maka dari itu tidak bisa melakukan ujian');
                    }else{
                        $str = "SELECT ku.tgl_ujian,ku.tgl_selesai_ujian,ku.isfinish,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.soal_tidak_terjawab,num.nilai FROM kartu_ujian ku LEFT JOIN nilai_ujian_masuk num ON ku.no_formulir=num.no_formulir WHERE ku.no_formulir=$no_formulir";
                        $this->DB->setFieldTable(array('tgl_ujian','tgl_selesai_ujian','isfinish','jumlah_soal','jawaban_benar','jawaban_salah','soal_tidak_terjawab','nilai'));
                        $r = $this->DB->getRecord($str);      
                        if (isset($r[1]) ) {
                            $dataujian=$r[1];                                                        
                            if ($dataujian['isfinish']) {
                                $this->idProcess='edit';
                                if ($dataujian['nilai'] == '') {                                    
                                    $jawaban_benar=$this->DB->getCountRowsOfTable("jawaban_ujian ju LEFT JOIN jawaban j ON (j.idjawaban=ju.idjawaban) WHERE no_formulir='$no_formulir' AND ju.idjawaban!=0 AND status=1",'ju.idjawaban');
                                    $dataujian['jawaban_benar']=$jawaban_benar;
                                    $jawaban_salah=$this->DB->getCountRowsOfTable("jawaban_ujian ju LEFT JOIN jawaban j ON (j.idjawaban=ju.idjawaban) WHERE no_formulir='$no_formulir' AND ju.idjawaban!=0 AND status=0",'ju.idjawaban');
                                    $dataujian['jawaban_salah']=$jawaban_salah;
                                    $soal_tidak_terjawab=$this->DB->getCountRowsOfTable("jawaban_ujian WHERE idjawaban=0 AND no_formulir='$no_formulir'",'idjawaban');
                                    $dataujian['soal_tidak_terjawab']=$soal_tidak_terjawab;
                                    $jumlah_soal=$jawaban_benar+$jawaban_salah+$soal_tidak_terjawab;
                                    $dataujian['jumlah_soal']=$jumlah_soal;
                                    $nilai=($jawaban_benar/$jumlah_soal)*100;
                                    $dataujian['nilai']=$nilai;
                                    
                                    $str= "INSERT INTO nilai_ujian_masuk (idnilai_ujian_masuk,no_formulir,jumlah_soal,jawaban_benar,jawaban_salah,soal_tidak_terjawab,nilai,ket_lulus) VALUES (NULL,$no_formulir,$jumlah_soal,$jawaban_benar,$jawaban_salah,$soal_tidak_terjawab,$nilai,0)";
                                    $this->DB->insertRecord($str);                                        
                                }
                                $this->DataUjian=$dataujian;                                
                            }else{
                                $this->timerSoalPMB->StartTimerOnLoad=true;
                                $this->populateSoal();
                            }
                        }else{
                            $this->idProcess='add';
                        }
                    }
                }else{
                    throw new Exception ('Untuk mengikuti ujian silahkan isi formulir terlebih dahulu');
                }                                        
            }catch (Exception $e) {
                $this->idProcess='view';
                $this->errorMessage->Text=$e->getMessage();
            }
        }
    }
    public function checkPIN ($sender,$param) { 
        $pin=addslashes($param->Value);
        try {
            if ($pin != '') {			            
                $no_formulir=$this->Pengguna->getDataUser('username');
                if (!$this->DB->checkRecordIsExist ('no_pin','pin',$pin," AND no_formulir='$no_formulir'")) {
                    throw new Exception ("Nomor PIN ($pin) tidak terdaftar di Portal.");
                }     
            }
        }catch(Exception $e) {			
            $sender->ErrorMessage=$e->getMessage();				
            $param->IsValid=false;			
		}
    }
    public function startUjian ($sender,$param) {        
        if ($this->IsValid) {
            $no_formulir=$this->Pengguna->getDataUser('username');
            $str = "INSERT INTO kartu_ujian (no_formulir,no_ujian,tgl_ujian,idtempat_spmb) VALUES ($no_formulir,'$no_formulir',NOW(),0)";			
            $this->DB->query('BEGIN');
            if ($this->DB->insertRecord($str)) {              
                $str = "INSERT INTO jawaban_ujian (idsoal,idjawaban,no_formulir) SELECT s.idsoal,0,$no_formulir FROM soal s ORDER BY RAND() LIMIT 80";
                $this->DB->insertRecord($str);        
                $this->DB->query('COMMIT');			
            }else {
                $this->DB->query('ROLLBACK');
            }        
            $this->redirect('SoalPMB',true);
        }
    }    
    public function populateSoal () {
        $no_formulir=$this->Pengguna->getDataUser('username');
        $str = "SELECT ju.idsoal,nama_soal,ju.idjawaban FROM jawaban_ujian ju,soal s WHERE ju.idsoal=s.idsoal AND ju.no_formulir=$no_formulir";
        $this->DB->setFieldTable(array('idsoal','nama_soal','idjawaban')); 
        $r=$this->DB->getRecord($str);	     
        if (isset($r[1])) {
            $this->RepeaterS->DataSource=$r;
            $this->RepeaterS->dataBind();
        }else{
            $this->DB->deleteRecord("kartu_ujian WHERE no_formulir=$no_formulir");
            $this->redirect('SoalPMB',true);
        }        
	}  
    public function dataBindRepeaterJawaban ($sender,$param) {
        $item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {					
            $idsoal=$item->DataItem['idsoal'];
            $idjawaban_tersimpan=$item->DataItem['idjawaban'];
            $str = "SELECT idjawaban,idsoal,j.jawaban,$idjawaban_tersimpan AS jawaban_tersimpan FROM jawaban j WHERE idsoal=$idsoal";
            $this->DB->setFieldTable(array('idjawaban','idsoal','jawaban','jawaban_tersimpan')); 
            $r=$this->DB->getRecord($str);                   
            $item->RepeaterJawaban->DataSource=$r;
            $item->RepeaterJawaban->dataBind();
        }
    }
    public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {	
            $idsoal=$item->DataItem['idsoal'];
			$item->rdJawaban->setUniqueGroupName("jawaban$idsoal");
            if ($item->DataItem['jawaban_tersimpan'] > 0){                                
                $item->rdJawaban->Checked=$item->DataItem['jawaban_tersimpan']==$item->DataItem['idjawaban'];
            }
		}
	}    
    public function saveJawaban ($sender,$param) {
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');        
        foreach($this->RepeaterS->Items as $v) {                
            $repeaterJawaban=$v->RepeaterJawaban->Items;             
            $idsoal=$v->txtIDSoal->Value;             
            $idjawaban='';
            foreach ($repeaterJawaban as $inputan) {                        
                if ($inputan->rdJawaban->Checked) {
                    $item=$inputan->rdJawaban->getNamingContainer();
                    $idjawaban=$v->RepeaterJawaban->DataKeys[$item->getItemIndex()];
                    $str = "UPDATE jawaban_ujian SET idjawaban=$idjawaban WHERE idsoal=$idsoal AND no_formulir=$no_formulir";
                    $this->DB->updateRecord($str);
                    break;
                }
            }            
        }            
        $this->redirect('SoalPMB',true);
    }
    public function saveJawabanTimer ($sender,$param) {
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');        
        foreach($this->RepeaterS->Items as $v) {                
            $repeaterJawaban=$v->RepeaterJawaban->Items;             
            $idsoal=$v->txtIDSoal->Value;             
            $idjawaban='';
            foreach ($repeaterJawaban as $inputan) {                        
                if ($inputan->rdJawaban->Checked) {
                    $item=$inputan->rdJawaban->getNamingContainer();
                    $idjawaban=$v->RepeaterJawaban->DataKeys[$item->getItemIndex()];
                    $str = "UPDATE jawaban_ujian SET idjawaban=$idjawaban WHERE idsoal=$idsoal AND no_formulir=$no_formulir";
                    $this->DB->updateRecord($str);
                    break;
                }
            }            
        }                    
    }
    public function selesaiUjian ($sender,$param) {            
        $no_formulir=$this->Pengguna->getDataUser('no_formulir');
        $this->saveJawaban($sender, $param);
        $jawaban_benar=$this->DB->getCountRowsOfTable("jawaban_ujian ju LEFT JOIN jawaban j ON (j.idjawaban=ju.idjawaban) WHERE no_formulir='$no_formulir' AND ju.idjawaban!=0 AND status=1",'ju.idjawaban');        
        $jawaban_salah=$this->DB->getCountRowsOfTable("jawaban_ujian ju LEFT JOIN jawaban j ON (j.idjawaban=ju.idjawaban) WHERE no_formulir='$no_formulir' AND ju.idjawaban!=0 AND status=0",'ju.idjawaban');        
        $soal_tidak_terjawab=$this->DB->getCountRowsOfTable("jawaban_ujian WHERE idjawaban=0 AND no_formulir='$no_formulir'",'idjawaban');        
        $jumlah_soal=$jawaban_benar+$jawaban_salah+$soal_tidak_terjawab;                
        $nilai=$jawaban_benar > 0 ? ($jawaban_benar/$jumlah_soal)*100:0;
        
        $str = "UPDATE kartu_ujian SET tgl_selesai_ujian=NOW(),isfinish=1 WHERE no_formulir=$no_formulir";        
        $this->DB->query ('BEGIN');
        if ($this->DB->updateRecord($str)) {
            $str= "INSERT INTO nilai_ujian_masuk (idnilai_ujian_masuk,no_formulir,jumlah_soal,jawaban_benar,jawaban_salah,soal_tidak_terjawab,nilai,ket_lulus) VALUES (NULL,$no_formulir,$jumlah_soal,$jawaban_benar,$jawaban_salah,$soal_tidak_terjawab,$nilai,0)";
            $this->DB->query('COMMIT');
            $this->redirect('SoalPMB',true);
        }else{
            $this->DB->query('ROLLBACK');
        }
        
    } 
}

?>