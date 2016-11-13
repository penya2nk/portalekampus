<?php
prado::using ('Application.MainPageD');
class CDetailEditNilai extends MainPageD {    
   	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showEditNilai=true;    
        $this->createObj('Akademik');        
        $this->createObj('Nilai');        
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailEditNilai'])||$_SESSION['currentPageDetailEditNilai']['page_name']!='d.nilai.DetailEditNilai') {
				$_SESSION['currentPageDetailEditNilai']=array('page_name'=>'d.nilai.DetailEditNilai','page_num'=>0,'search'=>false,'DataEditNilai'=>array());
			}  
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();            
            try {
                $idkelas_mhs=addslashes($this->request['id']);
                $this->Demik->getInfoKelas($idkelas_mhs);                
                if (!isset($this->Demik->InfoKelas['idkelas_mhs'])) {                                                
                    throw new Exception ("Kelas Mahasiswa dengan id ($idkelas_mhs) tidak terdaftar.");		
                }     
                $infokelas=$this->Demik->InfoKelas;
                $this->Demik->InfoKelas['namakelas']=$this->DMaster->getNamaKelasByID($infokelas['idkelas']).'-'.chr($infokelas['nama_kelas']+64);
                $this->Demik->InfoKelas['hari']=$this->TGL->getNamaHari($infokelas['hari']);
                $_SESSION['currentPageDetailEditNilai']['DataEditNilai']=$this->Demik->InfoKelas;
                $this->populateData();	             
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}
	}    
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageDetailEditNilai']['idkelas_mhs']=$this->cmbDaftarKelas->Text;
		$this->populateData($_SESSION['currentPageDetailEditNilai']['search']);        
        $this->InfoKelasPanel->render($param->NewWriter);
	}
	protected function populateData() {	
        $idkelas_mhs=$_SESSION['currentPageDetailEditNilai']['DataEditNilai']['idkelas_mhs'];
        $str = "SELECT vkm.idkrsmatkul,vdm.nim,vdm.nama_mhs,n.persentase_quiz, n.persentase_tugas, n.persentase_uts, n.persentase_uas, n.persentase_absen, n.nilai_quiz, n.nilai_tugas, n.nilai_uts, n.nilai_uas, n.nilai_absen, n.nilai_akhir,n.n_kual FROM kelas_mhs_detail kmd LEFT JOIN nilai_matakuliah n ON (n.idkrsmatkul=kmd.idkrsmatkul) JOIN v_krsmhs vkm ON (vkm.idkrsmatkul=kmd.idkrsmatkul) JOIN v_datamhs vdm ON (vkm.nim=vdm.nim) WHERE kmd.idkelas_mhs=$idkelas_mhs AND vkm.sah=1 AND vkm.batal=0 ORDER BY vdm.nama_mhs ASC";        
        $this->DB->setFieldTable(array('idkrsmatkul','nim','nama_mhs','persentase_quiz', 'persentase_tugas', 'persentase_uts', 'persentase_uas', 'persentase_absen', 'nilai_quiz', 'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_absen','n_kual'));
        $r=$this->DB->getRecord($str);	           
        $result=array();
        $sks=$this->Demik->InfoMatkul['sks'];
        while (list($k,$v)=each($r)) {                
            $n_kual='-';
            $am='-';
            $hm='-';
            if ($v['n_kual']!= '') {
                $n_kual=$v['n_kual'];
                $am=$this->Nilai->getAngkaMutu($v['n_kual']);
                $hm=$am*$sks;
            }
            $v['n_kual']=$n_kual;
            $v['am']=$am;
            $v['hm']=$hm;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();	                
	}	
    public function saveData ($sender,$param) {
        if ($this->IsValid) {
            
        }
    }
}	

?>