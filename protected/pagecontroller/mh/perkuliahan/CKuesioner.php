<?php
prado::using ('Application.MainPageMHS');
class CKuesioner extends MainPageMHS {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showKuesioner=true;        
        $this->createObj('Akademik');
        $this->createObj('Kuesioner');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKuesioner'])||$_SESSION['currentPageKuesioner']['page_name']!='d.perkuliahan.Kuesioner') {                
				$_SESSION['currentPageKuesioner']=array('page_name'=>'d.perkuliahan.Kuesioner','DataMatakuliah'=>array(),'ta'=>$_SESSION['ta'],'semester'=>$_SESSION['semester'],'idpengampu_penyelenggaraan'=>'none');												
			}
            try {
                $idkrsmatkul=addslashes($this->request['id']);   
                $str = "SELECT idpengampu_penyelenggaraan,iddosen,nidn,nama_dosen FROM v_kelas_mhs WHERE idkrsmatkul=$idkrsmatkul";
                $this->DB->setFieldTable(array('idpengampu_penyelenggaraan','iddosen','nidn','nama_dosen'));
                $dk=$this->DB->getRecord($str);
                
                //check telah menjadi anggota kelas
                if (!isset($dk[1])) {
                    throw new Exception ("Anda tidak terdaftar dikelas manapun, silahkan tentukan kelas terlebih dahulu dihalaman KRS.");
                }
                $datakelas=$dk[1];
                //check nilainya udah di isi atau belum ?
                $str = "SELECT km.idkrsmatkul AS idkrsmatkul,km.idpenyelenggaraan AS idpenyelenggaraan,k.idsmt AS idsmt,k.tahun AS tahun,p.kmatkul AS kmatkul,m.nmatkul AS nmatkul,m.sks AS sks,m.semester AS semester,nm.telah_isi_kuesioner AS telah_isi_kuesioner,nm.tanggal_isi_kuesioner AS tanggal_isi_kuesioner,d.iddosen AS iddosen,d.nidn AS nidn,concat(d.gelar_depan,_latin1' ',d.nama_dosen,_latin1' ',d.gelar_belakang) AS nama_dosen FROM (((((krs k JOIN krsmatkul km ON((k.idkrs = km.idkrs))) JOIN penyelenggaraan p ON((km.idpenyelenggaraan = p.idpenyelenggaraan))) JOIN matakuliah m ON((p.kmatkul = m.kmatkul))) JOIN dosen d ON((d.iddosen = p.iddosen))) JOIN nilai_matakuliah nm ON((nm.idkrsmatkul = km.idkrsmatkul))) where ((k.sah = 1) and (km.batal = 0) AND (km.idkrsmatkul=$idkrsmatkul))";                
                $this->DB->setFieldTable(array('idkrsmatkul','idpenyelenggaraan','idsmt','tahun','kmatkul','nmatkul','sks','semester','telah_isi_kuesioner','iddosen','nidn','nama_dosen'));
                $r=$this->DB->getRecord($str);				
                if (isset($r[1])) {
                    $datamatkul=$r[1];                    
                    if ($datamatkul['telah_isi_kuesioner']) {
                        $tanggal=$this->TGL->tanggal('d F Y',$datamatkul['tanggal_isi_kuesioner']);
                        throw new Exception ("Untuk matakuliah ini, Anda telah mengisi Kuesioner pada tanggal $tanggal.");
                    }else{
                        $datamatkul['iddosen2']=$datakelas['iddosen'];
                        $datamatkul['nidn2']=$datakelas['nidn'];
                        $datamatkul['nama_dosen2']=$datakelas['nama_dosen'];
                        $datamatkul['iddosen_kuesioner']=$datakelas['iddosen'];
                        
                        $datamatkul['kmatkul']=$this->Demik->getKMatkul($datamatkul['kmatkul']);                    
                        $this->Demik->InfoMatkul=$datamatkul;
                        $_SESSION['currentPageKuesioner']['DataMatakuliah']=$datamatkul;                    
                        $idsmt=$datamatkul['idsmt'];
                        $tahun=$datamatkul['tahun'];
                        $_SESSION['currentPageKuesioner']['idsmt']=$idsmt;
                        $_SESSION['currentPageKuesioner']['ta']=$tahun;	
                        
                        $_SESSION['currentPageKuesioner']['idpengampu_penyelenggaraan']=$datakelas['idpengampu_penyelenggaraan'];

                        $daftar_dosen_pengampu=array($datakelas['idpengampu_penyelenggaraan']=>$datakelas['nama_dosen'].' ['.$datakelas['nidn'].']');			
                        $this->cmbPengampuMatakuliah->DataSource=$daftar_dosen_pengampu;
                        $this->cmbPengampuMatakuliah->Text=$_SESSION['currentPageKuesioner']['idpengampu_penyelenggaraan'];
                        $this->cmbPengampuMatakuliah->DataBind();                        
                    }
                }else{
                    throw new Exception ("Nilai Untuk matakuliah ini belum tersedia, sehingga kuesionernya tidak perlu di isi.");
                }
            }catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }  
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            $this->populateData();
		}			
	}    
    public function getInfoToolbar() {                
		$ta=$_SESSION['currentPageKuesioner']['ta'];
		$semester=$this->setup->getSemester($_SESSION['currentPageKuesioner']['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	}   
    public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
            if ($item->DataItem['ada']) {
                $item->literalNamaKelompok->Text='<tr class="success">
                                                    <td colspan="9">'.$item->DataItem['nama_kelompok'].'</td></tr>';
            }
            $idkuesioner=$item->DataItem['idkuesioner'];
            $daftar_indikator=$this->Demik->getList("kuesioner_indikator WHERE idkuesioner=$idkuesioner AND nama_indikator != 'none'",array('idindikator','nama_indikator'),'nilai_indikator',null,1);              
            $item->cmbJawaban->DataSource=$daftar_indikator;
            $item->cmbJawaban->DataBind();
        }        
    }
    public function changeDosenPengampu ($sender,$param) {
        $_SESSION['currentPageKuesioner']['idpengampu_penyelenggaraan']=$this->cmbPengampuMatakuliah->Text;
        $idkrsmatkul=$_SESSION['currentPageKuesioner']['DataMatakuliah']['idkrsmatkul'];  
        $this->redirect('perkuliahan.Kuesioner',true,array('id'=>$idkrsmatkul));
    }
	public function populateData() {	        
        if ($_SESSION['currentPageKuesioner']['idpengampu_penyelenggaraan'] != 'none') {
            $ta=$_SESSION['currentPageKuesioner']['ta'];
            $idsmt=$_SESSION['currentPageKuesioner']['semester']; 
            $kelompok_pertanyaan=$this->DMaster->getListKelompokPertanyaan();                
            $kelompok_pertanyaan[0]='UNDEFINED';
            unset($kelompok_pertanyaan['none']);

            $result=array();
            while (list($idkelompok_pertanyaan,$nama_kelompok)=each($kelompok_pertanyaan)) {
                $str = "SELECT idkuesioner,pertanyaan FROM kuesioner WHERE tahun='$ta' AND idsmt='$idsmt' AND idkelompok_pertanyaan=$idkelompok_pertanyaan ORDER BY idkelompok_pertanyaan ASC,(orders+0)";             		
                $this->DB->setFieldTable(array('idkuesioner','pertanyaan'));
                $r=$this->DB->getRecord($str);
                $jumlah_r=count($r);
                if ($jumlah_r > 0) {
                    $r[1]['ada']=true;
                    $r[1]['nama_kelompok']=$nama_kelompok;
                    $result[]=$r[1];
                    next($r);
                    while (list($k,$v)=each($r)) {                                
                        $result[]=$v;
                    }                
                }
            }       
            $this->RepeaterS->DataSource=$result;
            $this->RepeaterS->dataBind();                
        }
	}	
    
    public function saveData ($sender,$param) {		
		if ($this->IsValid) {
            $idkrsmatkul=$_SESSION['currentPageKuesioner']['DataMatakuliah']['idkrsmatkul'];       
            if ($idkrsmatkul > 0) {
                $idpengampu_penyelenggaraan=$_SESSION['currentPageKuesioner']['idpengampu_penyelenggaraan'];                        
                $this->DB->beginTransaction();
                $str="UPDATE nilai_matakuliah SET telah_isi_kuesioner=1,tanggal_isi_kuesioner=NOW() WHERE idkrsmatkul=$idkrsmatkul";
                if ($this->DB->updateRecord($str)) {
                    $jumlahpertanyaan=$this->RepeaterS->Items->getCount();
                    $i=0;
                    foreach ($this->RepeaterS->Items as $inputan) {
                        $item=$inputan->cmbJawaban->getNamingContainer();
                        $idkuesioner=$this->RepeaterS->DataKeys[$item->getItemIndex()];                        
                        $idindikator=$inputan->cmbJawaban->Text;
                        if ($jumlahpertanyaan > $i+1) {
                            $values = "$values (NULL,$idpengampu_penyelenggaraan,$idkrsmatkul,$idkuesioner,$idindikator),";
                        }else{
                            $values = "$values (NULL,$idpengampu_penyelenggaraan,$idkrsmatkul,$idkuesioner,$idindikator)";
                        }
                        $i=$i+1;
                    }
                    $str = "INSERT INTO kuesioner_jawaban (idkuesioner_jawaban,idpengampu_penyelenggaraan,idkrsmatkul,idkuesioner,idindikator) VALUES $values";
                    $this->DB->insertRecord($str);  
                    if ($this->DB->checkRecordIsExist('idpengampu_penyelenggaraan','kuesioner_hasil',$idpengampu_penyelenggaraan)) {
                        $this->Kuesioner->hitungKuesioner($idpengampu_penyelenggaraan,'update');
                    }else{
                        $this->Kuesioner->hitungKuesioner($idpengampu_penyelenggaraan,'insert');
                    }
                    $this->DB->commitTransaction();                                 
                    unset($_SESSION['currentPageKuesioner']);
                    $this->redirect('perkuliahan.Kuesioner',true,array('id'=>$idkrsmatkul));
                }else{
                    $this->DB->rollbackTransaction();
                }
            }else{
                $this->modalMessageError->show();
                $this->lblContentMessageError->Text="[nilai idkrsmatkul kosong]. Gunakan internet yang lebih cepat atau hubungi Sekretariat Prodi.";
            }            
        }
	}    
}

?>