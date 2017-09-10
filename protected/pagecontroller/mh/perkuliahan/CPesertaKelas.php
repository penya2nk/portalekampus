<?php
prado::using ('Application.MainPageMHS');
class CPesertaKelas extends MainPageMHS {	
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showJadwalPerkuliahan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePesertaKelas'])||$_SESSION['currentPagePesertaKelas']['page_name']!='m.perkuliahan.PesertaKelas') {
				$_SESSION['currentPagePesertaKelas']=array('page_name'=>'m.perkuliahan.PesertaKelas','page_num'=>0,'search'=>false,'InfoKelas'=>array());
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
            $txtsearch=$this->txtKriteria->Text;
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

}
?>