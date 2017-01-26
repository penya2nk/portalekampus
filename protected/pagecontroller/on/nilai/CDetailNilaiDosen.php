<?php
prado::using ('Application.MainPageON');
class CDetailNilaiDosen extends MainPageON {    
   	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showStopInputNilai=true;
        $this->createObj('Akademik');        
        $this->createObj('Nilai');        
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailNilaiDosen'])||$_SESSION['currentPageDetailNilaiDosen']['page_name']!='on.nilai.DetailNilaiDosen') {
				$_SESSION['currentPageDetailNilaiDosen']=array('page_name'=>'on.nilai.DetailNilaiDosen','page_num'=>0,'search'=>false,'DataNilai'=>array());
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
                $_SESSION['currentPageDetailNilaiDosen']['DataNilai']=$this->Demik->InfoKelas;
                $this->populateData();	             
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}
	}    
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageDetailNilaiDosen']['idkelas_mhs']=$this->cmbDaftarKelas->Text;
		$this->populateData($_SESSION['currentPageDetailNilaiDosen']['search']);        
        $this->InfoKelasPanel->render($param->NewWriter);
	}
	protected function populateData() {	
        $datakelas=$_SESSION['currentPageDetailNilaiDosen']['DataNilai'];
        $idkelas_mhs=$datakelas['idkelas_mhs'];
        $str = "SELECT vkm.idkrsmatkul,vdm.nim,vdm.nama_mhs,COALESCE(n.nilai_quiz, 'N.A') AS nilai_quiz, COALESCE(n.nilai_tugas, 'N.A') AS nilai_tugas, COALESCE(n.nilai_uts, 'N.A') AS nilai_uts, COALESCE(n.nilai_uas, 'N.A') AS nilai_uas, COALESCE(n.nilai_absen, 'N.A') AS nilai_absen, COALESCE(n.n_kuan, 'N.A') AS n_kuan,COALESCE(n.n_kual, 'N.A') AS n_kual FROM kelas_mhs_detail kmd LEFT JOIN nilai_matakuliah n ON (n.idkrsmatkul=kmd.idkrsmatkul) JOIN v_krsmhs vkm ON (vkm.idkrsmatkul=kmd.idkrsmatkul) JOIN v_datamhs vdm ON (vkm.nim=vdm.nim) WHERE kmd.idkelas_mhs=$idkelas_mhs AND vkm.sah=1 AND vkm.batal=0 ORDER BY vdm.nama_mhs ASC";        
        $this->DB->setFieldTable(array('idkrsmatkul','nim','nama_mhs','nilai_quiz', 'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_absen','n_kuan','n_kual'));
        $r=$this->DB->getRecord($str);	           
        $result=array();
        
        while (list($k,$v)=each($r)) { 
            $v['am']=$v['n_kual']=='N.A' ? 'N.A' : $this->Nilai->getAngkaMutu($v['n_kual']);
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();	                
	}
    public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        
    }
}	
?>