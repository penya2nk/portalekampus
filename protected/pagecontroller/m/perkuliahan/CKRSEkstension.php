<?php
prado::using ('Application.MainPageM');
class CKRSEkstension Extends MainPageM {	
	/**
	* total  sks
	*/
	public $totalSks=0;
	/**
	* total  matakuliah
	*/
	public $jumlahMatkul=0;
	/**
	* data krs
	*/
	public $dataKrs;
	
	public function onLoad($param) {		
		parent::onLoad($param);								
		$this->showSubMenuAkademikPerkuliahan=true;
        $this->showKRSEkstension = true;   
        $this->createObj('KRS');
		$this->createObj('Nilai');
        $this->createObj('Finance');
        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageKRSEkstension'])||$_SESSION['currentPageKRSEkstension']['page_name']!='m.perkuliahan.KRSEkstension') {					
                $_SESSION['currentPageKRSEkstension']=array('page_name'=>'m.perkuliahan.KRSEkstension','page_num'=>0,'mode_krs'=>'sudah','iddosen_wali'=>'none','tahun_masuk'=>$_SESSION['tahun_masuk'],'DataKRS'=>array(),'DataMHS'=>array());												
            }
            $_SESSION['currentPageKRSEkstension']['search']=false;
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	

            $tahun_masuk=$this->getAngkatan ();			            
            $this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
            $this->tbCmbTahunMasuk->Text=$_SESSION['currentPageKRSEkstension']['tahun_masuk'];						
            $this->tbCmbTahunMasuk->dataBind();

            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
            $this->tbCmbTA->Text=$_SESSION['ta'];
            $this->tbCmbTA->dataBind();			

            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
            $this->tbCmbSemester->DataSource=$semester;
            $this->tbCmbSemester->Text=$_SESSION['semester'];
            $this->tbCmbSemester->dataBind();

            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();

            $dosen_wali=$this->DMaster->getListDosenWali();				            
            $this->cmbDosenWali->DataSource=$dosen_wali;
            $this->cmbDosenWali->Text=$_SESSION['currentPageKRSEkstension']['iddosen_wali'];
            $this->cmbDosenWali->dataBind();

            $this->cmbModeKRS->Text=$_SESSION['currentPageKRSEkstension']['mode_krs'];

            $this->setInfoToolbar();
            $this->populateData();			
        }
	}	
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$tahunmasuk=$_SESSION['currentPageKRSEkstension']['tahun_masuk'] == 'none'?'':'Tahun Masuk '.$this->DMaster->getNamaTA($_SESSION['currentPageKRSEkstension']['tahun_masuk']);		        
		$this->lblModulHeader->Text="Program Studi $ps T.A $ta Semester $semester $tahunmasuk";        
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;		        
		echo $_SESSION['currentPageKRSEkstension']['tahun_masuk']=$_SESSION['ta'];
		$this->tbCmbTahunMasuk->DataSource=$this->getAngkatan();
		$this->tbCmbTahunMasuk->Text=$_SESSION['currentPageKRSEkstension']['tahun_masuk'];
		$this->tbCmbTahunMasuk->dataBind();		
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['currentPageKRSEkstension']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKRSEkstension']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
	public function changeModeKRS ($sender,$param) {
		$_SESSION['currentPageKRSEkstension']['mode_krs']=$this->cmbModeKRS->Text;
		$_SESSION['currentPageKRSEkstension']['page_num']=0;
		$this->populateData();
	} 
    public function populateData($search=false) {					
		$ta=$_SESSION['ta'];
		$semester=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];
		$tahun_masuk=$_SESSION['currentPageKRSEkstension']['tahun_masuk'];
        $iddosen_wali=$_SESSION['currentPageKRSEkstension']['iddosen_wali'];
        $str_dw = $iddosen_wali=='none'?'':" AND vdm.iddosen_wali=$iddosen_wali";
        $str_tahun_masuk=$tahun_masuk=='none'?'':" AND vdm.tahun_masuk=$tahun_masuk";   
        
        if ($search) {
            
        }else{
            if ($_SESSION['currentPageKRSEkstension']['mode_krs'] == 'belum') {
                
            }else{
                $str = "SELECT k.idkrs,k.tgl_krs,k.nim,vdm.nama_mhs,vdm.jk,vdm.tahun_masuk,k.sah,k.tgl_disahkan FROM krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND k.tahun=$ta AND k.idsmt=$semester AND vdm.kjur=$kjur AND idkelas='C' $str_dw $str_tahun_masuk";            
                $jumlah_baris=$this->DB->getCountRowsOfTable("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND k.tahun=$ta AND k.idsmt=$semester AND vdm.kjur=$kjur AND idkelas='C' $str_dw $str_tahun_masuk",'k.nim');		
                $this->DB->setFieldTable(array('idkrs','tgl_krs','nim','nama_mhs','jk','tahun_masuk','sah','tgl_disahkan'));
            }            
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKRSEkstension']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageKRSEkstension']['page_num']=0;}
        $str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";	
        $r = $this->DB->getRecord($str,$offset+1);	        

        $this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }
    public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {			
			$idkrs=$item->DataItem['idkrs'];
            $str = "SELECT COUNT(idkrsmatkul) AS jumlah_matkul,SUM(sks) AS jumlah_sks FROM v_krsmhs WHERE idkrs='$idkrs'";						
            $this->DB->setFieldTable (array('jumlah_matkul','jumlah_sks'));
			$r=$this->DB->getRecord($str);
            $item->literalMatkul->Text=$r[1]['jumlah_matkul'] > 0 ?$r[1]['jumlah_matkul']:0;
            $item->literalSKS->Text=$r[1]['jumlah_sks'] > 0 ?$r[1]['jumlah_sks']:0;            
            if ($item->DataItem['sah']) {                 
                $status='<span class="label label-success">sah</span>';	
            }elseif ($_SESSION['currentPageKRS']['mode_krs'] == 'belum'){
                $status='-';	
            }else{
                $trstyle=' class="danger"';
                $status='<span class="label label-info">Belum disahkan</span>';	
            }
            $item->literalTRStyle->Text=$trstyle;
            $item->literalStatus->Text=$status;                   
		}
	}
    
	public function checkNIM ($sender,$param) {
        $nim=addslashes($param->Value);
        try {
            if ($nim != '') {			            
                $datamhs=array('nim'=>$nim);
                $this->KRS->setDataMHS($datamhs);
                $this->KRS->getKRS($_SESSION['ta'],$_SESSION['semester']);                                
                if (isset($this->KRS->DataKRS['krs']['idkrs'])) {           
                    $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                    $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                    $r=$this->DB->getRecord($str);	           
                    if (!isset($r[1])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                    }
                    $datamhs=$r[1];
                    $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];

                    $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
                    $datamhs['nama_dosen']=$nama_dosen;
                    
                    $_SESSION['currentPageKRSEkstension']['DataMHS']=$datamhs;
                    
                    $_SESSION['currentPageKRSEkstension']['DataKRS']=$this->KRS->DataKRS;
                }else{
                    $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                    $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                    $r=$this->DB->getRecord($str);	           
                    if (!isset($r[1])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                    }                    
                    $datamhs=$r[1];                   		                    
                    if ($datamhs['idkelas']!='C') throw new Exception ("Mahasiswa Dengan NIM ($nim) Tidak Terdaftar Pada Kelas Ekstension");                    
                    $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];
                    $nama_dosen=$this->DMaster->getNamaDosenWaliByID($datamhs['iddosen_wali']);				                    
                    $datamhs['nama_dosen']=$nama_dosen; 
                    
                    $_SESSION['currentPageKRSEkstension']['DataMHS']=$datamhs;                    
                    
                    $idsmt=$_SESSION['semester'];
                    $tahun=$_SESSION['ta'];                    
                    $datadulang=$this->KRS->getDataDulang($idsmt,$tahun);                    
                    $nama_tahun = $this->DMaster->getNamaTA($tahun);
                    $nama_semester = $this->setup->getSemester($idsmt);
                    if (!isset($datadulang['iddulang']))throw new Exception ("Mahasiswa dengan NIM ($nim) belum melakukan daftar ulang pada T.A $nama_tahun Semester $nama_semester. Silahkan hubungi Prodi (Bukan Keuangan).");
                    $status=$datamhs['k_status'];
                    if ($status== 'K'||$status== 'L'||$status== 'D') throw new Exception ("Status Anda tidak aktif, sehingga tidak bisa mengisi KRS.");						
                    if ($datadulang['k_status'] != 'A')throw new Exception ("Anda pada tahun akademik dan semester sekarang tidak aktif.");									                                        
                    
                    $_SESSION['currentPageKRSEkstension']['DataKRS']=array();                    
                }                
            }
        }catch(Exception $e) {			
            $sender->ErrorMessage=$e->getMessage();				
            $param->IsValid=false;			
		}
	}
		
	public function isiKRS ($sender,$param) {
		if ($this->Page->IsValid) {			
            $krs=$_SESSION['currentPageKRSEkstension']['DataKRS']['krs'];  
            $datamhs= $_SESSION['currentPageKRSEkstension']['DataMHS'];
            $nim=$datamhs['nim'];
            $this->Nilai->setDataMHS($datamhs);
            if (isset($krs['idkrs']) && $krs['sah']==0) {       
                $idsmt=$krs['idsmt'];
                $tahun=$krs['tahun'];
                                
                $krs['maxSKS']=$idsmt==3?$this->Finance->getSKSFromSP($tahun,$idsmt):$this->Nilai->getMaxSKS($tahun,$idsmt);                
                $krs['ipstasmtbefore']=$this->Nilai->getIPS();                                                   
                                
                $_SESSION['currentPageKRSEkstension']['DataKRS']['krs']=$krs;
                
                $this->redirect ('perkuliahan.TambahKRSEkstension',true);
            }elseif(isset($krs['idkrs']) && $krs['sah']==1){
                $idkrs=$krs['idkrs'];
                $this->redirect ('perkuliahan.DetailKRSEkstension',true,array('id'=>$idkrs));
            }else{
                $idsmt=$_SESSION['semester'];
                $tahun=$_SESSION['ta'];
                
                $tanggal=date('Y-m-d');
                $no_krs=mt_rand();                    
                $tasmt=$tahun.$idsmt;
                
                $str = "INSERT INTO krs (idkrs,tgl_krs,no_krs,nim,idsmt,tahun,tasmt) VALUES (NULL,'$tanggal',$no_krs,'$nim','$idsmt','$tahun','$tasmt')";
                $this->DB->insertRecord($str);					
                $this->KRS->DataKRS['krs'] = array('idkrs'=>$this->DB->getLastInsertID(),
                                                    'tgl_krs'=>$tanggal,
                                                    'no_krs'=>$no_krs,
                                                    'nim'=>$nim,
                                                    'idsmt'=>$idsmt,
                                                    'tahun'=>$tahun,
                                                    'tasmt'=>$tasmt);		   
                
                $this->KRS->DataKRS['krs']['maxSKS']=$idsmt==3?$this->Finance->getSKSFromSP($tahun,$idsmt):$this->Nilai->getMaxSKS($tahun,$idsmt);                
                $this->KRS->DataKRS['krs']['ipstasmtbefore']=$this->Nilai->getIPS();                                                   
                
                $_SESSION['currentPageKRSEkstension']['DataKRS']=$this->KRS->DataKRS;
                
                $this->redirect ('perkuliahan.TambahKRSEkstension',true);
            }
		}
	}	
}

?>