<?php
prado::using ('Application.MainPageD');
class CDetailDPNA extends MainPageD {    
   	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showDPNA=true;    
        $this->createObj('Akademik');        
        $this->createObj('Nilai');        
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailDPNA'])||$_SESSION['currentPageDetailDPNA']['page_name']!='d.nilai.DetailDPNA') {
				$_SESSION['currentPageDetailDPNA']=array('page_name'=>'d.nilai.DetailDPNA','page_num'=>0,'search'=>false,'DataDPNA'=>array());
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
                $_SESSION['currentPageDetailDPNA']['DataDPNA']=$this->Demik->InfoKelas;
                $this->populateData();	             
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}
	}    
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageDetailDPNA']['idkelas_mhs']=$this->cmbDaftarKelas->Text;
		$this->populateData($_SESSION['currentPageDetailDPNA']['search']);        
        $this->InfoKelasPanel->render($param->NewWriter);
	}
	protected function populateData() {	
        $idkelas_mhs=$_SESSION['currentPageDetailDPNA']['DataDPNA']['idkelas_mhs'];
        $str = "SELECT vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,n.n_kuan,n.n_kual FROM kelas_mhs_detail kmd LEFT JOIN nilai_matakuliah n ON (n.idkrsmatkul=kmd.idkrsmatkul) JOIN v_krsmhs vkm ON (vkm.idkrsmatkul=kmd.idkrsmatkul) JOIN v_datamhs vdm ON (vkm.nim=vdm.nim) WHERE  kmd.idkelas_mhs=$idkelas_mhs AND vkm.sah=1 AND vkm.batal=0 ORDER BY vdm.nama_mhs ASC";        
        $this->DB->setFieldTable(array('nim','nirm','nama_mhs','jk','n_kuan','n_kual'));
        $r=$this->DB->getRecord($str);	           
        $result=array();
        $sks=$this->Demik->InfoMatkul['sks'];
        while (list($k,$v)=each($r)) {
            $n_kuan='-';
            $n_kual='-';
            $am='-';
            $hm='-';
            if ($v['n_kual']!= '') {
                $n_kuan=$v['n_kuan'];
                $n_kual=$v['n_kual'];
                $am=$this->Nilai->getAngkaMutu($v['n_kual']);
                $hm=$am*$sks;
            }
            $v['n_kuan']=$n_kuan;
            $v['n_kual']=$n_kual;
            $v['am']=$am;
            $v['hm']=$hm;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();	                
	}
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case 'pdf' :
                $dataReport=$_SESSION['currentPageDetailDPNA']['DataDPNA'];        
                $nama_matakuliah=$dataReport['nmatkul'];
                
                $messageprintout="Matakuliah $nama_matakuliah";
                $nama_tahun = $this->DMaster->getNamaTA($dataReport['tahun']);
                $nama_semester = $this->setup->getSemester($dataReport['idsmt']);                
                $nama_ps=$_SESSION['daftar_jurusan'][$dataReport['kjur']];
                
                $dataReport['nama_ps']=$nama_ps;
                $dataReport['ta']=$nama_tahun;
                $dataReport['nama_semester']=$nama_semester; 
                
                $dataReport['dosenpengajar']=$dataReport['nama_dosen'];
                $dataReport['nama_dosen']=$dataReport['nama_dosen_matakuliah'];
                $dataReport['nama_jabatan_dpna']=$this->setup->getSettingValue('nama_jabatan_dpna');
                $dataReport['nama_penandatangan_dpna']=$this->setup->getSettingValue('nama_penandatangan_dpna');
                $dataReport['jabfung_penandatangan_dpna']=$this->setup->getSettingValue('jabfung_penandatangan_dpna');
                $dataReport['nipy_penandatangan_dpna']=$this->setup->getSettingValue('nipy_penandatangan_dpna');

                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);

                $this->report->printDPNA($this->Nilai,true);
                
            break;
            case  'excel2007' :
                $dataReport=$_SESSION['currentPageDetailDPNA']['DataDPNA'];        
                $nama_matakuliah=$dataReport['nmatkul'];
                
                $messageprintout="Matakuliah $nama_matakuliah";

                $dataReport['nama_jabatan_dpna']=$this->setup->getSettingValue('nama_jabatan_dpna');
                $dataReport['nama_penandatangan_dpna']=$this->setup->getSettingValue('nama_penandatangan_dpna');
                $dataReport['jabfung_penandatangan_dpna']=$this->setup->getSettingValue('jabfung_penandatangan_dpna');
                $dataReport['nipy_penandatangan_dpna']=$this->setup->getSettingValue('nipy_penandatangan_dpna');

                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);

                $this->report->printDPNA($this->Nilai,true);
            break;
        }        
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Peserta dan Nilai Akhir';
        $this->modalPrintOut->show();
	}
}