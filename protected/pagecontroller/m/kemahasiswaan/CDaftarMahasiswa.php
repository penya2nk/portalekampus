<?php
prado::using ('Application.MainPageM');
class CDaftarMahasiswa extends MainPageM {
	public function onLoad($param) {		
		parent::onLoad($param);						
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showDaftarMahasiswa=true;
                
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageDaftarMahasiswa'])||$_SESSION['currentPageDaftarMahasiswa']['page_name']!='m.kemahasiswaan.DaftarMahasiswa') {
				$_SESSION['currentPageDaftarMahasiswa']=array('page_name'=>'m.kemahasiswaan.DaftarMahasiswa','page_num'=>0,'search'=>false,'idkonsentrasi'=>'none','k_status'=>'none');												
			}
            $_SESSION['currentPageDaftarMahasiswa']['search']=false;
            
            $this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];
			$this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();				
                
			$tahun_masuk=$this->DMaster->getListTA();
			$tahun_masuk['none']='All';
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $kelas=$this->DMaster->getListKelas();
            $kelas['none']='All';
			$this->tbCmbKelas->DataSource=$kelas;
			$this->tbCmbKelas->Text=$_SESSION['kelas'];			
			$this->tbCmbKelas->dataBind();		
            
            $status=$this->DMaster->getListStatusMHS();
            $status['none']='All';
			$this->tbCmbStatus->DataSource=$status;
			$this->tbCmbStatus->Text=$_SESSION['currentPageDaftarMahasiswa']['k_status'];			
			$this->tbCmbStatus->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->populateSummary();
            $this->populateKonsentrasi();
			$this->populateData();
		}		
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblProdi->Text=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];        
        $_SESSION['currentPageDaftarMahasiswa']['idkonsentrasi']='none';
        
        $this->populateSummary();
        $this->populateKonsentrasi();
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {    				
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;	
        $this->populateSummary();
        $this->populateKonsentrasi();
		$this->populateData();
	}
	public function changeTbKelas ($sender,$param) {				
		$_SESSION['kelas']=$this->tbCmbKelas->Text;		
        $this->populateSummary();
        $this->populateKonsentrasi();
		$this->populateData();
	}
    public function changeTbStatus ($sender,$param) {				
		$_SESSION['currentPageDaftarMahasiswa']['k_status']=$this->tbCmbStatus->Text;	
        
        $this->populateSummary();
        $this->populateKonsentrasi();
		$this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDaftarMahasiswa']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDaftarMahasiswa']['search']);
	}
    public function filterKonsentrasi ($sender,$param) {
        $id=$this->getDataKeyField($sender, $this->RepeaterKonsentrasi);
        $_SESSION['currentPageDaftarMahasiswa']['idkonsentrasi']=$id;
        
        $this->populateSummary();
        $this->populateKonsentrasi();
        $this->populateData();
    }
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageDaftarMahasiswa']['search']=true;
        $this->populateData($_SESSION['currentPageDaftarMahasiswa']['search']);
	} 
    public function populateSummary () {
        $kjur=$_SESSION['kjur'];
        $tahun_masuk=$_SESSION['tahun_masuk'];        
        $str_tahun_masuk=$tahun_masuk == 'none' ?'':"AND tahun_masuk=$tahun_masuk";
        $idkonsentrasi=$_SESSION['currentPageDaftarMahasiswa']['idkonsentrasi'];
        $str_konsentrasi = $idkonsentrasi == 'none'?'':" AND idkonsentrasi=$idkonsentrasi";
        $kelas=$_SESSION['kelas'];
        $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
        $status=$_SESSION['currentPageDaftarMahasiswa']['k_status'];
        $str_status = $status == 'none'?'':" AND k_status='$status'";

        $str = "SELECT jk,COUNT(nim) AS jumlah FROM v_datamhs WHERE kjur='$kjur' $str_tahun_masuk $str_konsentrasi $str_kelas $str_status GROUP BY jk ORDER BY jk ASC";			
        $this->DB->setFieldTable(array('jk','jumlah'));
		$jumlah_jk = $this->DB->getRecord($str);
        
        $jumlah_pria=0;
        $jumlah_wanita=0;
        foreach ($jumlah_jk as $v) {
            switch ($v['jk']) {
                case 'L' :
                    $jumlah_pria=$v['jumlah'];
                break;
                case 'P' :
                    $jumlah_wanita=$v['jumlah'];
                break;
            }
        }
        $this->labelJumlahMHSPria->Text=$jumlah_pria;
        $this->labelJumlahMHSWanita->Text=$jumlah_wanita;
    }
    public function populateKonsentrasi () {			
        $datakonsentrasi=$this->DMaster->getListKonsentrasiProgramStudi();        
        $r=array();
        $i=1;
        $tahun_masuk=$_SESSION['tahun_masuk'];        
        $str_tahun_masuk=$tahun_masuk == 'none' ?'':"AND tahun=$tahun_masuk";
        $kelas=$_SESSION['kelas'];
        $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
        $status=$_SESSION['currentPageDaftarMahasiswa']['k_status'];
        $str_status = $status == 'none'?'':" AND k_status='$status'";
        while (list($k,$v)=each($datakonsentrasi)) {                        
            if ($v['kjur']==$_SESSION['kjur']){
                $idkonsentrasi=$v['idkonsentrasi'];
                $jumlah = $this->DB->getCountRowsOfTable("register_mahasiswa WHERE idkonsentrasi=$idkonsentrasi $str_tahun_masuk $str_kelas $str_status",'nim');
                $v['jumlah_mhs']=$jumlah > 10000 ? 'lebih dari 10.000' : $jumlah;
                $r[$i]=$v;
                $i+=1;
            }
        }        
        $this->RepeaterKonsentrasi->DataSource=$r;
        $this->RepeaterKonsentrasi->DataBind();
    }
    public function resetKonsentrasi ($sender,$param) {
		$_SESSION['currentPageDaftarMahasiswa']['idkonsentrasi']='none';
        $this->redirect('kemahasiswaan.DaftarMahasiswa',true);
	} 
	public function populateData ($search=false) {			
        $kjur=$_SESSION['kjur'];        
        if ($search) {
            $str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,tempat_lahir,tanggal_lahir,alamat_rumah,kjur,idkonsentrasi,iddosen_wali,tahun_masuk,k_status,idkelas FROM v_datamhs";			
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $cluasa="WHERE nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs $cluasa",'nim');
                    $str = "$str $cluasa";
                break;
                case 'nirm' :
                    $cluasa="WHERE nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs $cluasa",'nim');
                    $str = "$str $cluasa";
                break;
                case 'nama' :
                    $cluasa="WHERE nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs $cluasa",'nim');
                    $str = "$str $cluasa";
                break;
            }
        }else{
            $tahun_masuk=$_SESSION['tahun_masuk'];        
            $str_tahun_masuk=$tahun_masuk == 'none' ?'':"AND tahun_masuk=$tahun_masuk";
            $idkonsentrasi=$_SESSION['currentPageDaftarMahasiswa']['idkonsentrasi'];
            $str_konsentrasi = $idkonsentrasi == 'none'?'':" AND idkonsentrasi=$idkonsentrasi";
            $kelas=$_SESSION['kelas'];
            $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
            $status=$_SESSION['currentPageDaftarMahasiswa']['k_status'];
            $str_status = $status == 'none'?'':" AND k_status='$status'";
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_datamhs WHERE kjur=$kjur $str_tahun_masuk $str_konsentrasi $str_kelas $str_status",'nim');		
            $str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,tempat_lahir,tanggal_lahir,alamat_rumah,kjur,idkonsentrasi,iddosen_wali,tahun_masuk,k_status,idkelas FROM v_datamhs WHERE kjur='$kjur' $str_tahun_masuk $str_konsentrasi $str_kelas $str_status";			
        }		
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDaftarMahasiswa']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=6;$_SESSION['currentPageDaftarMahasiswa']['page_num']=0;}
        $str = "$str ORDER BY nim DESC,nama_mhs ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','alamat_rumah','kjur','idkonsentrasi','iddosen_wali','tahun_masuk','k_status','idkelas'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result = array();
        while (list($k,$v)=each($r)) {
            $nim=$v['nim'];
            $dataMHS['nim']=$nim;
            $dataMHS['tahun_masuk']=$v['tahun_masuk'];
            $dataMHS['kjur']=$v['kjur'];
            $dataMHS['iddata_konversi']=$this->Nilai->isMhsPindahan($nim,true);            
            $this->Nilai->setDataMHS($dataMHS);
            $v['konsentrasi']=$this->DMaster->getNamaKonsentrasiByID($v['idkonsentrasi'],$v['kjur']);
            $this->Nilai->getTranskrip();
            $v['ips']=$this->Nilai->getIPSAdaNilai();
            $v['sks']=$this->Nilai->getTotalSKSAdaNilai();
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }
    public function printOut ($sender,$param) {	
        $this->createObj('reportakademik');
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :
                $messageprintout="";
                $dataReport['kjur']=$_SESSION['kjur'];
                $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$_SESSION['kjur']];                
                
                $dataReport['tahun_masuk']=$_SESSION['tahun_masuk'];                 
                $dataReport['nama_tahun']=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);     
                
                $dataReport['linkoutput']=$this->linkOutput;
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                
                $this->report->printDaftarMahasiswa($this->Demik,$this->DMaster); 
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Daftar Mahasiswa';
        $this->modalPrintOut->show();
    }
}
?>