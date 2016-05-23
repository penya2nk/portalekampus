<?php
prado::using ('Application.MainPageM');
class CKuesionerDetailDosen extends MainPageM {	    
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showKuesioner=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['KuesionerDetailDosen'])||$_SESSION['KuesionerDetailDosen']['page_name']!='m.perkuliahan.KuesionerDetailDosen') {                
				$_SESSION['KuesionerDetailDosen']=array('page_name'=>'m.perkuliahan.KuesionerDetailDosen','page_num'=>0,'search'=>false,'DataDosen'=>array());												
			}
            $_SESSION['KuesionerDetailDosen']['search']=false; 
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
            $daftar_dosen=$this->Demik->getListDosenFromPenyelenggaraan ($_SESSION['semester'],$_SESSION['ta']);
            $this->cmbDosen->DataSource=$daftar_dosen;
            $this->cmbDosen->DataBind();            
            try {                
                if (array_key_exists($_SESSION['KuesionerDetailDosen']['DataDosen']['iddosen'], $daftar_dosen)) {
                    $this->DMaster->DataDosen=$_SESSION['KuesionerDetailDosen']['DataDosen'];
                    $this->cmbDosen->Text=$this->DMaster->DataDosen['iddosen'];                
                    $this->populateMatakuliah();
                }else{
                    $this->panelInfoDosen->Visible=false;
                    unset($_SESSION['KuesionerDetailDosen']['DataDosen']);
                }
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}			
	}
    public function getInfoToolbar() {                
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	} 
    public function viewRecord($sender,$param) {                
        if ($this->IsValid){
            $iddosen=$this->cmbDosen->Text;          
            $_SESSION['KuesionerDetailDosen']['DataDosen']=$this->DMaster->getDataDosen($iddosen);
            $this->redirect('perkuliahan.KuesionerDetailDosen',true);
        }
    }
    public function populateMatakuliah() {                
        $idsmt=$_SESSION['semester'];
        $tahun=$_SESSION['ta'];
        $iddosen=$_SESSION['KuesionerDetailDosen']['DataDosen']['iddosen'];        
        $daftar_prodi=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
        $jumlah_prodi=count($daftar_prodi);
        $i=0;
        foreach ($daftar_prodi as $k=>$v) {
            $idkur=$this->Demik->getIDKurikulum($k);
            if ($jumlah_prodi > $i+1) {
                $values = "$values idkur=$idkur OR ";
            }else{
                $values = "$values idkur=$idkur";
            }
            $i=$i+1;
        }                
        $str = "SELECT  DISTINCT(p.kmatkul),m.nmatkul,m.sks,m.semester,p.kjur FROM pengampu_penyelenggaraan pp,penyelenggaraan p,matakuliah m WHERE pp.idpenyelenggaraan=p.idpenyelenggaraan AND m.kmatkul=p.kmatkul AND pp.iddosen=$iddosen AND p.tahun=$tahun AND p.idsmt=$idsmt AND ($values)";
        $this->DB->setFieldTable(array('kmatkul','nmatkul','sks','semester','kjur'));
        $r=$this->DB->getRecord($str);
        $result=array();        
        while (list($k,$v)=each($r)) {  
            $kmatkul=$v['kmatkul'];            
            $jumlahresponden=$this->DB->getCountRowsOfTable("v_nilai vn,pengampu_penyelenggaraan pp WHERE pp.idpenyelenggaraan=vn.idpenyelenggaraan AND telah_isi_kuesioner=1 AND pp.iddosen=$iddosen AND vn.tahun=$tahun AND vn.idsmt=$idsmt AND vn.kmatkul='$kmatkul'",'vn.nim');
            $v['jumlahresponden']=$jumlahresponden;
            
            $str = "SELECT nilai_indikator,COUNT(idkuesioner_jawaban) jumlah  FROM kuesioner_jawaban kj,kuesioner_indikator ki,krsmatkul km,penyelenggaraan p WHERE ki.idindikator=kj.idindikator AND kj.idkrsmatkul=km.idkrsmatkul AND p.idpenyelenggaraan=km.idpenyelenggaraan AND kj.iddosen=$iddosen AND p.tahun=$tahun AND p.idsmt=$idsmt AND p.kmatkul='$kmatkul' GROUP BY nilai_indikator ORDER By nilai_indikator ASC";            
            $this->DB->setFieldTable(array('nilai_indikator','jumlah'));
            $s=$this->DB->getRecord($str);
            
            $result[$k]=$v;
        }
        $this->RepeaterDaftarMatakuliah->DataSource=$result;
        $this->RepeaterDaftarMatakuliah->DataBind();
    }
}

?>