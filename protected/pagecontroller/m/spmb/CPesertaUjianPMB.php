<?php
prado::using ('Application.MainPageM');
class CPesertaUjianPMB extends MainPageM {
    public $DataUjianPMB=array();
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuSPMBUjianPMB=true;
        $this->showJadwalUjianPMB=true;
        
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePesertaUjianPMB'])||$_SESSION['currentPagePesertaUjianPMB']['page_name']!='m.spmb.PesertaUjianPMB') {
				$_SESSION['currentPagePesertaUjianPMB']=array('page_name'=>'m.spmb.PesertaUjianPMB','page_num'=>0,'search'=>false,'DataUjianPMB');
			}  
            $_SESSION['currentPagePesertaUjianPMB']['search']=false;            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();            
            try {                     
                $id=addslashes($this->request['id']); 
                $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,rk.namaruang,rk.kapasitas,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE idjadwal_ujian=$id ORDER BY tanggal_ujian ASC";        
                $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','namaruang','kapasitas','status'));
                $r = $this->DB->getRecord($str);
                if (!isset($r[1])){
                    throw new Exception ("Jadwal Ujian PMB dengan id ($id) tidak terdaftar.");
                }
                $this->DataUjianPMB=$r[1];
                $_SESSION['currentPagePesertaUjianPMB']['DataUjianPMB']=$this->DataUjianPMB;
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
        $idjadwal_ujian=$_SESSION['currentPagePesertaUjianPMB']['DataUjianPMB']['idjadwal_ujian'];        
        $str = "SELECT pum.idpeserta_ujian,pum.no_formulir,fp.nama_mhs,fp.jk,fp.kjur1,fp.kjur2,pin.no_pin FROM peserta_ujian_pmb pum,formulir_pendaftaran fp,pin WHERE fp.no_formulir=pum.no_formulir AND pin.no_formulir=pum.no_formulir AND pum.idjadwal_ujian=$idjadwal_ujian";
        if ($search) {            
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) {                
                case 'no_formulir' :
                    $clausa="AND fp.no_formulir='$txtsearch'";
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND fp.nama_mhs LIKE '%$txtsearch%'";
                    $str = "$str $clausa";
                break;
            }
        }				
        $str = "$str ORDER BY fp.nama_mhs ASC";
		$this->DB->setFieldTable(array('idpeserta_ujian','no_formulir','no_pin','nama_mhs','jk','kjur1','kjur2'));	
		$r=$this->DB->getRecord($str);
        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
        
	}
    public function editRecord ($sender,$param) {
        $this->idProcess='edit';        
        $id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenid->Value=$id;        
        
        $this->DataUjianPMB=$_SESSION['currentPagePesertaUjianPMB']['DataUjianPMB'];
        $idsmt=$this->DataUjianPMB['idsmt'];
        $tahun_masuk=$this->DataUjianPMB['tahun_masuk'];
        $str = "SELECT idjadwal_ujian,tahun_masuk,idsmt,nama_kegiatan,tanggal_ujian,jam_mulai,jam_akhir,tanggal_akhir_daftar,jup.idruangkelas,rk.namaruang,rk.kapasitas,date_added,status FROM jadwal_ujian_pmb jup LEFT JOIN ruangkelas rk ON (jup.idruangkelas=rk.idruangkelas) WHERE tahun_masuk='$tahun_masuk' AND idsmt='$idsmt' AND idjadwal_ujian != $id AND status=1 ORDER BY tanggal_ujian ASC";
        $this->DB->setFieldTable(array('idjadwal_ujian','tahun_masuk','idsmt','nama_kegiatan','tanggal_ujian','jam_mulai','jam_akhir','tanggal_akhir_daftar','idruangkelas','namaruang','kapasitas','status'));
		$r = $this->DB->getRecord($str);
        $result = array('none'=>' ');
        while (list($k,$v)=each($r)) {  
            $idjadwal_ujian=$v['idjadwal_ujian'];
            $jumlah_peserta=$this->DB->getCountRowsOfTable("peserta_ujian_pmb WHERE idjadwal_ujian=$idjadwal_ujian",'idjadwal_ujian');
            if ($jumlah_peserta < $v['kapasitas']) {
                $str = $v['nama_kegiatan'] . ' # '.$this->Page->TGL->tanggal ('l, d F Y',$v['tanggal_ujian']).' # '. $v['jam_mulai'].'-'.$v['jam_akhir'] . ' # '.$this->Page->TGL->tanggal ('l, d F Y',$v['tanggal_akhir_daftar']) .' # '.$v['namaruang'].' ['.$v['kapasitas'].'] sisa '.($v['kapasitas']-$jumlah_peserta);                                                
                $result[$idjadwal_ujian]=$str;
            }
        }
        
        $this->cmbEditJadwal->DataSource=$result;
        $this->cmbEditJadwal->dataBind();
        
    }
    public function updateData ($sender,$param) {
        if ($this->IsValid) {
            $id=$this->hiddenid->Value;
            $idjadwal_ujian=$this->cmbEditJadwal->Text;
            
            $str = "UPDATE peserta_ujian_pmb SET idjadwal_ujian=$idjadwal_ujian WHERE idpeserta_ujian=$id";
            $this->DB->updateRecord($str);
            
            $this->redirect('spmb.PesertaUjianPMB',true,array('id'=>$_SESSION['currentPagePesertaUjianPMB']['DataUjianPMB']['idjadwal_ujian']));
        }
    }
    public function deleteRecord ($sender,$param) {        
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $str = "SELECT no_formulir,idjadwal_ujian FROM peserta_ujian_pmb WHERE idpeserta_ujian=$id";
        $this->DB->setFieldTable(array('idjadwal_ujian','no_formulir'));
		$r = $this->DB->getRecord($str);
        
        if ($this->DB->checkRecordIsExist ('no_formulir','nilai_ujian_masuk',$r[1]['no_formulir'])) {
            $this->lblHeaderMessageError->Text='Menghapus Peserta Ujian PMB';
            $this->lblContentMessageError->Text="Anda tidak bisa menghapus peserta ini karena sudah melakukan Ujian PMB.";
            $this->modalMessageError->Show();
        }else{
            $this->DB->deleteRecord("peserta_ujian_pmb WHERE idpeserta_ujian='$id'");
            $this->redirect('spmb.PesertaUjianPMB',true,array('id'=>$r[1]['idjadwal_ujian']));
        }
    }  
    public function printOut ($sender,$param) {		
        $this->createObj('reportspmb');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';        
        $dataReport=$_SESSION['currentPagePesertaUjianPMB']['DataUjianPMB'];
        $idjadwal_ujian=$dataReport['idjadwal_ujian'];
        $jumlah_peserta=$this->DB->getCountRowsOfTable ("peserta_ujian_pmb pum,formulir_pendaftaran fp,pin WHERE fp.no_formulir=pum.no_formulir AND pin.no_formulir=pum.no_formulir AND pum.idjadwal_ujian=$idjadwal_ujian",'pum.no_formulir');
		switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :  
                $messageprintout="Mohon maaf Print out pada mode excel belum kami support.";
            break;
            case  'pdf' :                
                $dataReport['nama_tahun']=$this->DMaster->getNamaTA($dataReport['tahun_masuk']);
                $dataReport['jumlah_peserta']=$jumlah_peserta;
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);  
                
                $messageprintout="Berita Acara Ujian SPMB : <br/>";
                $this->report->printBeritaAcaraUjianSPMB($this->DMaster);
            break;
        }                
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Berita Acara Ujian SPMB';
        $this->modalPrintOut->show();
	}
}