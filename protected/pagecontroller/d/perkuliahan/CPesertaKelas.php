<?php
prado::using ('Application.MainPageD');
class CPesertaKelas extends MainPageD {	
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPembagianKelas=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePesertaKelas'])||$_SESSION['currentPagePesertaKelas']['page_name']!='d.perkuliahan.PesertaKelas') {
				$_SESSION['currentPagePesertaKelas']=array('page_name'=>'d.perkuliahan.PesertaKelas','page_num'=>0,'search'=>false,'InfoKelas'=>array());
			}  
            $_SESSION['currentPagePesertaKelas']['search']=false;            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();            
            try {                     
                $id=addslashes($this->request['id']); 
                $infokelas=$this->Demik->getInfoKelas($id);
                if (!isset($infokelas['idkelas_mhs'])){
                    throw new Exception ("Kode Kelas dengan id ($id) tidak terdaftar.");
                }
                $infokelas['namakelas']=$this->DMaster->getNamaKelasByID($infokelas['idkelas']).'-'.chr($infokelas['nama_kelas']+64);
                $infokelas['hari']=$this->Page->TGL->getNamaHari($infokelas['hari']);
                $this->Demik->InfoKelas=$infokelas;
                $_SESSION['currentPagePembagianKelas']['iddosen']=$infokelas['iddosen'];
                $_SESSION['currentPagePesertaKelas']['InfoKelas']=$infokelas;          
                $this->populateData();		
            } catch (Exception $ex) {
                $this->idProcess='view';
                $this->errorMessage->Text=$ex->getMessage();
            }
		}		
	}  
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePesertaMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPagePesertaMatakuliah']['search']);
	}
    public function populateData ($search=false) {
        $idkelas_mhs=$_SESSION['currentPagePesertaKelas']['InfoKelas']['idkelas_mhs'];        
        $str = "SELECT kmd.idkrsmatkul,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tahun_masuk,k.sah FROM kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kmd.idkrsmatkul=km.idkrsmatkul AND km.idkrs=k.idkrs AND k.nim=vdm.nim AND kmd.idkelas_mhs=$idkelas_mhs AND km.batal=0";
        if ($search) {            
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kmd.idkrsmatkul=km.idkrsmatkul AND km.idkrs=k.idkrs AND k.nim=vdm.nim AND kmd.idkelas_mhs=$idkelas_mhs AND km.batal=0 $clausa",'kmd.idkrsmatkul');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kmd.idkrsmatkul=km.idkrsmatkul AND km.idkrs=k.idkrs AND k.nim=vdm.nim AND kmd.idkelas_mhs=$idkelas_mhs AND km.batal=0 $clausa",'kmd.idkrsmatkul');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kmd.idkrsmatkul=km.idkrsmatkul AND km.idkrs=k.idkrs AND k.nim=vdm.nim AND kmd.idkelas_mhs=$idkelas_mhs AND km.batal=0 $clausa",'kmd.idkrsmatkul');
                    $str = "$str $clausa";
                break;
            }
        }else{                        
            $jumlah_baris=$this->DB->getCountRowsOfTable("kelas_mhs_detail kmd,krsmatkul km,krs k,v_datamhs vdm WHERE kmd.idkrsmatkul=km.idkrsmatkul AND km.idkrs=k.idkrs AND k.nim=vdm.nim AND kmd.idkelas_mhs=$idkelas_mhs AND km.batal=0",'kmd.idkrsmatkul');
        }				
        $str = "$str ORDER BY vdm.nama_mhs ASC";
		$this->DB->setFieldTable(array('nim','nirm','nama_mhs','jk','tahun_masuk','sah'));	
		$r=$this->DB->getRecord($str,$offset+1);
        $result=array();
        while (list($k,$v)=each($r)) {
            $status='belum disahkan';
            if ($v['sah']==1) {
                $status='sah';
            }
            $v['status']=$status;
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
	}
    public function printOut ($sender,$param) {		
        $this->createObj('reportakademik');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';        
        $dataReport=$_SESSION['currentPagePesertaKelas']['InfoKelas'];
		switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :               
                $dataReport['namakelas']=$this->DMaster->getNamaKelasByID($dataReport['idkelas']).'-'.chr($dataReport['nama_kelas']+64);
                $dataReport['hari']=$this->Page->TGL->getNamaHari($dataReport['hari']);
                
                $dataReport['nama_prodi']=$_SESSION['daftar_jurusan'][$dataReport['kjur']];
                $dataReport['nama_tahun'] = $this->DMaster->getNamaTA($dataReport['tahun']);
                $dataReport['nama_semester'] = $this->setup->getSemester($dataReport['idsmt']);               
                
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);  
                
                $messageprintout="Daftar Hadir Mahasiswa : <br/>";
                $this->report->printDaftarHadirMahasiswa();
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode excel pdf belum kami support.";
            break;
        }                
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Hadir Mahasiswa';
        $this->modalPrintOut->show();
	}
}