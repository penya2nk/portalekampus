<?php
prado::using ('Application.MainPageM');
class CPesertaKelas extends MainPageM {	
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPembagianKelas=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePesertaKelas'])||$_SESSION['currentPagePesertaKelas']['page_name']!='m.perkuliahan.PesertaKelas') {
				$_SESSION['currentPagePesertaKelas']=array('page_name'=>'m.perkuliahan.PesertaKelas','page_num'=>0,'search'=>false,'InfoKelas'=>array(),'DaftarKelasTujuan'=>array());
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
                
                $this->hiddenidkelasmhs->Value=$id;
                $idkelas=$infokelas['idkelas'];
                $idpengampu_penyelenggaraan=$infokelas['idpengampu_penyelenggaraan'];
                $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar FROM kelas_mhs km WHERE idkelas_mhs!='$id' AND idkelas='$idkelas' AND km.idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";
                $this->DB->setFieldTable(array('idkelas_mhs','idkelas','nama_kelas','hari','jam_masuk','jam_keluar'));
                $r = $this->DB->getRecord($str);
                
                $daftar_kelas=array('none'=>' ');
                while (list($k,$v)=each($r)) {
                    $daftar_kelas[$v['idkelas_mhs']]=$this->DMaster->getNamaKelasByID($v['idkelas']).'-'.chr($v['nama_kelas']+64).' '.$this->Page->TGL->getNamaHari($v['hari']). ' '.$v['jam_masuk'].'-'.$v['jam_keluar'];
                }
                $_SESSION['currentPagePesertaKelas']['DaftarKelasTujuan']=$daftar_kelas;
                $this->cmbKelasTujuan->DataSource=$daftar_kelas;
                $this->cmbKelasTujuan->DataBind();
                
                $this->populateData();		
            } catch (Exception $ex) {
                $this->idProcess='view';
                $this->errorMessage->Text=$ex->getMessage();
            }
		}		
	} 
    public function pindahkanAnggotaKelas ($sender,$param) {
        if ($this->IsValid) {
            $old_idkelas_mhs=$this->hiddenidkelasmhs->Value;
            $idkelas_mhs = $this->cmbKelasTujuan->Text;
            
            $jumlah_peserta=$this->DB->getCountRowsOfTable ("kelas_mhs_detail WHERE idkelas_mhs=$idkelas_mhs OR idkelas_mhs=$old_idkelas_mhs",'idkrsmatkul');
            $str = "SELECT rk.kapasitas FROM kelas_mhs km JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idkelas_mhs=$idkelas_mhs";
            $this->DB->setFieldTable(array('kapasitas'));
            $r = $this->DB->getRecord($str);
            if ($jumlah_peserta <= $r[1]['kapasitas']) {
                $str = "UPDATE kelas_mhs_detail SET idkelas_mhs=$idkelas_mhs WHERE idkelas_mhs=$old_idkelas_mhs";
                $this->DB->updateRecord($str);
                $this->redirect('perkuliahan.PesertaKelas', true, array('id'=>$old_idkelas_mhs));
            }else{
                $this->modalMessageError->show();
                $this->lblContentMessageError->Text='Tidak bisa pindah karena jumlah peserta dikelas tersebut, telah melampau kapasitas kelas.';
            }
            
        }
    }
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePesertaMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPagePesertaMatakuliah']['search']);
	}
    public function itemCreated ($sender,$param) {
		$item=$param->Item;		
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {		
            $item->cmbKelasTujuan->DataSource=$_SESSION['currentPagePesertaKelas']['DaftarKelasTujuan'];
            $item->cmbKelasTujuan->DataBind();
        }
    }
    public function pindahkanAnggotaKelasMHS ($sender,$param) {
        $idkrsmatkul=$this->getDataKeyField($sender,$this->RepeaterS);	
        $idkelas_mhs=$sender->Text;
        if ($idkelas_mhs != 'none') {
            $jumlah_peserta=$this->DB->getCountRowsOfTable ("kelas_mhs_detail WHERE idkelas_mhs=$idkelas_mhs",'idkrsmatkul')+1;
            $str = "SELECT rk.kapasitas FROM kelas_mhs km JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idkelas_mhs=$idkelas_mhs";
            $this->DB->setFieldTable(array('kapasitas'));
            $r = $this->DB->getRecord($str);
            if ($jumlah_peserta <= $r[1]['kapasitas']) {
                $str = "UPDATE kelas_mhs_detail SET idkelas_mhs=$idkelas_mhs WHERE idkrsmatkul=$idkrsmatkul";
                $this->DB->updateRecord($str);
                $this->redirect('perkuliahan.PesertaKelas', true, array('id'=>$this->hiddenidkelasmhs->Value));
            }else{
                $this->modalMessageError->show();
                $this->lblContentMessageError->Text='Tidak bisa pindah karena jumlah peserta di kelas tersebut telah melampau kapasitas kelas.';
            }
        }
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
		$this->DB->setFieldTable(array('idkrsmatkul','nim','nirm','nama_mhs','jk','tahun_masuk','sah'));	
		$r=$this->DB->getRecord($str);
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
?>