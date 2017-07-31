<?php
prado::using ('Application.MainPageM');
class CProfilMahasiswa extends MainPageM {
    /**
	* Data Mahasiswa
	*/
    public $DataMHS;
    /**
	* total SKS
	*/
	static $totalSKS=0;
    /**
	* total M
	*/
	static $totalM=0;	
	/**
	* jumlah matakuliah
	*/
	static $jumlahMatkul=0;	
	public function onLoad($param) {		
		parent::onLoad($param);						
        $this->showSubMenuAkademikKemahasiswaan=true;
        $this->showProfilMahasiswa=true;
        $this->createObj('Nilai');
        if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageProfilMahasiswa'])||$_SESSION['currentPageProfilMahasiswa']['page_name']!='m.kemahasiswaan.ProfilMahasiswa') {
				$_SESSION['currentPageProfilMahasiswa']=array('page_name'=>'m.kemahasiswaan.ProfilMahasiswa','page_num'=>0,'DataMHS'=>array(),'activeviewindex'=>0);												
			}

            $this->MVProfilMahasiswa->ActiveViewIndex=$_SESSION['currentPageProfilMahasiswa']['activeviewindex'];             
		}		
	}  
    public function changeView ($sender,$param) {                
        try {
            $nim=addslashes($this->request['id']);
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas,vdm.theme FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
            $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','k_status','status','idkelas','nkelas','theme'));
            $r=$this->DB->getRecord($str);	           
            if (!isset($r[1])) {
                unset($_SESSION['currentPageProfilMahasiswa']);
                throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
            }
            $datamhs=$r[1];
            $datamhs['iddata_konversi']=$this->Nilai->isMhsPindahan($nim,true);
            $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];

            $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
            $datamhs['nama_dosen']=$nama_dosen;
            
            //theme
            $this->cmbTheme->DataSource=$this->setup->getListThemes();
            $this->cmbTheme->Text=$datamhs['theme'];
            $this->cmbTheme->DataBind();
            
            //nilai
            $this->Nilai->setDataMHS($datamhs);
            $this->Nilai->getTranskrip();
            $datamhs['ipk']=$this->Nilai->getIPKAdaNilai();
            $datamhs['totalmatkul']=$this->Nilai->getTotalMatkulAdaNilai();
            $datamhs['totalsks']=$this->Nilai->getTotalSKSAdaNilai ();            
            
            $this->DataMHS=$datamhs;
            $_SESSION['currentPageProfilMahasiswa']['DataMHS']=$this->DataMHS;
            $activeview = $_SESSION['currentPageProfilMahasiswa']['activeviewindex'];                
            if ($activeview == $this->MVProfilMahasiswa->ActiveViewIndex) {
                switch ($activeview) {
                    case 0 : //ips dan ipk
                        $this->populateIPSdanIPK();
                    break;   
                    case 1 : //daftar ulang mahasiswa
                        $this->createObj('KRS');                    
                        $this->populateDulang();
                    break;
                    case 2 : //KRS mahasiswa
                        $this->createObj('KRS');                    
                        $this->populateKRS();
                    break;
                    case 3 : //Ganti NIM dan NIRM
                        
                    break;
                }
            }else{
                $_SESSION['currentPageProfilMahasiswa']['activeviewindex']=$this->MVProfilMahasiswa->ActiveViewIndex;
                $this->redirect('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$datamhs['nim']));
            }       
            
        }catch (Exception $ex) {
            $this->idProcess='view';	                
            $this->errorMessage->Text=$ex->getMessage();
        }          
    }
    public function getDataMHS($idx) {		
        return $this->Nilai->getDataMHS($idx);
    }
    public function itemCreatedRepeaterIPSdanIPK ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {   
            CProfilMahasiswa::$totalM += $item->DataItem['jumlah_m'];
            CProfilMahasiswa::$totalSKS += $item->DataItem['jumlah_sks_ada_nilai'];
            $ipk=@ bcdiv(CProfilMahasiswa::$totalM,CProfilMahasiswa::$totalSKS,2);
            $item->literalIPK->Text=$ipk;
        }
    }   
    public function populateIPSdanIPK() {
        $nim=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim'];        
        $str = "SELECT idkrs,tahun,idsmt FROM krs WHERE nim='$nim' AND sah=1 ORDER BY idkrs ASC";
        $this->DB->setFieldTable(array('idkrs','tahun','idsmt'));        
		$r=$this->DB->getRecord($str);                
        
        $result=array();
        while(list($k,$v)=each($r)) {
            $this->Nilai->getKHS($v['tahun'],$v['idsmt']);
            $v['ta']=$this->DMaster->getNamaTA($v['tahun']);		
            $v['semester'] = $this->setup->getSemester($v['idsmt']); 
            
            $jumlah_sks=$this->Nilai->getTotalSKS ();
            $jumlah_m=$this->Nilai->getTotalM ();
            $ips=$this->Nilai->getIPS();
            
            $ipk=0;
            $v['jumlah_sks'] = $jumlah_sks;
            $v['jumlah_sks_ada_nilai'] = $this->Nilai->getTotalSKSAdaNilai();
            $v['jumlah_m']=$jumlah_m;
            $v['ips'] = $ips;
            $v['ipk'] = $ipk;
            $result[$k]=$v;
        }        
        $this->RepeaterIPSdanIPK->DataSource=$result;
		$this->RepeaterIPSdanIPK->dataBind();
    }
    public function populateDulang() {
        $this->KRS->setDataMHS($_SESSION['currentPageProfilMahasiswa']['DataMHS']);
        $nim=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim'];
        $str = "SELECT d.iddulang,d.tahun,d.idsmt,d.tanggal,d.idkelas,k.nkelas,d.k_status,sm.n_status FROM dulang d LEFT JOIN kelas k ON (d.idkelas=k.idkelas) LEFT JOIN status_mhs sm ON (d.k_status=sm.k_status) WHERE nim='$nim' ORDER BY d.tahun DESC,d.idsmt DESC";				        
		$this->DB->setFieldTable(array('iddulang','tahun','idsmt','tanggal','idkelas','nkelas','k_status','n_status'));
		$r=$this->DB->getRecord($str);                
        $result=array();
        while(list($k,$v)=each($r)) {
            $v['tanggal']=$v['tanggal'] == '0000-00-00 00:00:00' ? '-' :$this->TGL->tanggal('l, d F Y',$v['tanggal']);
            $isikrs='tidak isi';
            if ($v['k_status']=='A') {
                $this->KRS->getDataKRS($v['tahun'],$v['idsmt']);  
                $datakrs=$this->KRS->DataKRS;
                $isikrs='belum isi';
                if (isset($datakrs['idkrs'])) {
                    $isikrs=$this->KRS->DataKRS['sah']==true ? 'sudah isi [sah]':'sudah isi [belum disahkan]';
                }                
            }
            $v['tahun']=$this->DMaster->getNamaTA($v['tahun']);		
            $v['semester'] = $this->setup->getSemester($v['idsmt']);
            $v['kelas']=$v['nkelas'];
            $v['status']=$v['n_status'];
            
            $v['isikrs']=$isikrs;
            $result[$k]=$v;
        }
		$this->RepeaterDulang->DataSource=$result;
		$this->RepeaterDulang->dataBind();
    }
    public function populateKRS() {        
        $nim=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim'];        
        $str = "SELECT idkrs,tahun,idsmt FROM krs WHERE nim='$nim' ORDER BY idkrs DESC";
        $this->DB->setFieldTable(array('idkrs','tahun','idsmt'));        
		$r=$this->DB->getRecord($str);                
        
        $result=array();
        while(list($k,$v)=each($r)) {            
            $v['ta']=$this->DMaster->getNamaTA($v['tahun']);		
            $v['semester'] = $this->setup->getSemester($v['idsmt']);            
            $result[$k]=$v;
        }        
        $this->RepeaterKRS->DataSource=$result;
		$this->RepeaterKRS->dataBind();
    }
	public function itemCreatedRepeaterKRS ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {   
            $idkrs=$item->DataItem['idkrs'];
            $str = "SELECT idpenyelenggaraan,idkrsmatkul,kmatkul,nmatkul,sks,semester,batal,nidn,nama_dosen FROM v_krsmhs WHERE idkrs=$idkrs ORDER BY semester ASC,kmatkul ASC";
            $this->DB->setFieldTable(array('idpenyelenggaraan','idkrsmatkul','kmatkul','nmatkul','sks','semester','batal','nidn','nama_dosen'));
            $r=$this->DB->getRecord($str);
            $result=array();
            
            CProfilMahasiswa::$totalSKS=0;
            CProfilMahasiswa::$jumlahMatkul=0;
            while (list($k,$v)=each ($r)) {
                $v['kmatkul']=$this->Nilai->getKMatkul($v['kmatkul']);
                CProfilMahasiswa::$totalSKS+=$v['sks'];
                CProfilMahasiswa::$jumlahMatkul+=1;
                $result[$k]=$v;
            }           

            $item->RepeaterKRSDetail->DataSource=$result;
            $item->RepeaterKRSDetail->DataBind();
        }
    }   
    public function populatekeuangan() {
        
    }
    public function resetPassword ($sender,$param) {
        $password_default = md5(1234);
        $no_formulir=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['no_formulir'];
        $str = "UPDATE profiles_mahasiswa SET userpassword='$password_default' WHERE no_formulir='$no_formulir'";
        $this->DB->updateRecord($str);
        $this->lblInfo->Text='Reset Password Mahasiswa';
        $this->lblMessageInfo->Text='Password mahasiswa sukses direset menjadi 1234';        
        $this->modalMessage->show();
    }
    public function changeTheme ($sender,$param) {
        if ($this->IsValid) {
            $theme=  addslashes($sender->Text);
            $nim=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim'];
            $str = "UPDATE profiles_mahasiswa SET theme='$theme' WHERE nim='$nim'";            
            $this->DB->updateRecord($str);            
           
            $this->redirect('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$nim));
        }
    }
    public function checkNIM ($sender,$param) {					
		$nim=$param->Value;		
        if ($nim != '') {
            try {   
                if ($nim != $_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim']) {
                    $str = "SELECT nama_mhs,tahun_masuk,nama_ps FROM v_datamhs WHERE nim='$nim'";
                    $this->DB->setFieldTable(array('nama_mhs','tahun_masuk','nama_ps'));
                    $r = $this->DB->getRecord($str);
                    if (isset($r[1])) {  
                        $nama_mhs=$r[1]['nama_mhs'];
                        $tahun_masuk=$r[1]['tahun_masuk'];
                        $nama_ps=$r[1]['nama_ps'];
                        throw new Exception ("NIM ($nim) sudah terdaftar atas nama $nama_mhs P.S $nama_ps registrasi pada tahun $tahun_masuk");                                                   
                    }                
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
	}
	
	public function checkNIRM ($sender,$param) {
        $nirm=$param->Value;		
        if ($nirm != '') {
            try {   
                if ($nirm != $_SESSION['currentPageProfilMahasiswa']['DataMHS']['nirm']) {
                    $str = "SELECT nama_mhs,tahun_masuk,nama_ps FROM v_datamhs WHERE nirm='$nirm'";
                    $this->DB->setFieldTable(array('nama_mhs','tahun_masuk','nama_ps'));
                    $r = $this->DB->getRecord($str);
                    if (isset($r[1])) {  
                        $nama_mhs=$r[1]['nama_mhs'];
                        $tahun_masuk=$r[1]['tahun_masuk'];
                        $nama_ps=$r[1]['nama_ps'];
                        throw new Exception ("NIRM ($nirm) sudah terdaftar atas nama $nama_mhs P.S $nama_ps registrasi pada tahun $tahun_masuk");                                                   
                    }          
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }
	}
    public function updateDataNIMNIRM ($sender,$param) {
		if ($this->Page->isValid) {	
            $nimasal=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nim'];
            $nim=addslashes($this->txtEditNIM->Text);
			$nirm=addslashes($this->txtEditNIRM->Text);		
			$str = "UPDATE register_mahasiswa SET nim='$nim',nirm='$nirm' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);	
          
            $str = "UPDATE transaksi SET nim='$nim' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $nama_mhs=$_SESSION['currentPageProfilMahasiswa']['DataMHS']['nama_mhs'];
            $str = "UPDATE forumposts SET userid='$nim',nama_user='$nama_mhs [$nim]' WHERE userid='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE log_nilai_matakuliah SET nim='$nim' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE log_transkrip_asli SET nim='$nim' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE profiles_mahasiswa SET nim='$nim' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE profiles_ortu SET nim='$nim' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE tweets SET userid='$nim' WHERE userid='$nimasal' AND tipe='mh'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE tweetscomment SET userid='$nim' WHERE userid='$nimasal' AND tipe='mh'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE rekap_status_mahasiswa SET nim='$nim',nirm='$nirm' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $str = "UPDATE rekap_laporan_pembayaran_per_semester SET nim='$nim',nirm='$nirm' WHERE nim='$nimasal'";
			$this->DB->updateRecord($str);
            
            $this->redirect('kemahasiswaan.ProfilMahasiswa',true,array('id'=>$nim));
		}
	}
}
?>