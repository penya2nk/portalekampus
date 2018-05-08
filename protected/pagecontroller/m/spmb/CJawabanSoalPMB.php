<?php
prado::using ('Application.MainPageM');
class CJawabanSoalPMB extends MainPageM {
    public $DataUjian;
    public function onLoad ($param) {
        parent::onLoad ($param);        
        $this->showSubMenuSPMBUjianPMB=true;
        $this->showNilaiUjianPMB = true;	    
        $this->createObj('Akademik');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	            
            try {          
                $no_formulir=addslashes($this->request['id']);
                $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.alamat_rumah,fp.telp_hp,k.nkelas,fp.kjur1,fp.kjur2,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk,fp.waktu_mendaftar FROM formulir_pendaftaran fp,profiles_mahasiswa pm,kelas k WHERE fp.no_formulir=pm.no_formulir AND fp.idkelas=k.idkelas AND fp.no_formulir='$no_formulir'";
                $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','telp_hp','nkelas','kjur1','kjur2','tahun_masuk','semester_masuk','waktu_mendaftar'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1]) ) {
                    throw new Exception("No. Formulir ($no_formulir) tidak terdaftar.");
                }    
                $datamhs=$r[1];
                $datamhs['nama_ps1']=$_SESSION['daftar_jurusan'][$datamhs['kjur1']];
                $datamhs['nama_ps2']=$datamhs['kjur2']==0 ?'N.A' :$_SESSION['daftar_jurusan'][$datamhs['kjur2']];
                $datamhs['diterima_ps1']='';
                $datamhs['diterima_ps2']='';
                $datamhs['waktu_mendaftar']=$this->TGL->tanggal('d F Y H:m:s',$datamhs['waktu_mendaftar']);
                $this->Demik->setDataMHS($datamhs);
                
                $str = "SELECT ku.no_formulir,ku.tgl_ujian,ku.tgl_selesai_ujian,ku.isfinish,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.soal_tidak_terjawab,num.nilai,num.kjur FROM kartu_ujian ku LEFT JOIN nilai_ujian_masuk num ON ku.no_formulir=num.no_formulir WHERE ku.no_formulir=$no_formulir";
                $this->DB->setFieldTable(array('no_formulir','tgl_ujian','tgl_selesai_ujian','isfinish','jumlah_soal','jawaban_benar','jawaban_salah','soal_tidak_terjawab','nilai','kjur'));
                $r = $this->DB->getRecord($str);      
                if (isset($r[1]) ) {
                    $dataujian=$r[1];                                                        
                    if ($dataujian['isfinish']) {                            
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
                        $kjur1=$this->Demik->getDataMHS('kjur1');
                        $kjur2=$this->Demik->getDataMHS('kjur2');
                        $dataujian['kjur'];
                        $datamhs['diterima_ps1']=($kjur1 > 0 && $dataujian['kjur'] == $kjur1)?'<span class="label label-success">DI TERIMA</span>':'';
                        $datamhs['diterima_ps2']=($kjur2 > 0 && $dataujian['kjur'] == $kjur2)?'<span class="label label-success">DI TERIMA</span>':'';
                        $this->Demik->setDataMHS($datamhs);

                        $this->DataUjian=$dataujian;                                
                        $this->populateSoal();
                    }else{                                
                        throw new Exception ("Ujian PMB Calon Mahasiswa dengan No. Formulir ($no_formulir) belum selelai.");
                    }
                }else{
                    throw new Exception ("Calon Mahasiswa dengan No. Formulir ($no_formulir) belum mengikuti ujian.");
                }                                        
            }catch (Exception $e) {
                $this->idProcess='view';
                $this->errorMessage->Text=$e->getMessage();
            }
        }
    }    
    public function getDataMHS($idx) {
        return $this->Demik->getDataMHS($idx);
    }
    public function populateSoal () {
        $no_formulir=$this->DataUjian['no_formulir'];
        $str = "SELECT ju.idsoal,nama_soal,ju.idjawaban FROM jawaban_ujian ju,soal s WHERE ju.idsoal=s.idsoal AND ju.no_formulir=$no_formulir";
        $this->DB->setFieldTable(array('idsoal','nama_soal','idjawaban')); 
        $r=$this->DB->getRecord($str);	        
        $this->RepeaterS->DataSource=$r;
        $this->RepeaterS->dataBind();
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
            $item->rdJawaban->Enabled=false;
		}
	}            
}