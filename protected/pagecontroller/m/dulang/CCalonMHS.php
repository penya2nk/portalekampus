<?php
prado::using ('Application.MainPageM');
class CCalonMHS Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showCalonMHS=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageCalonMHS'])||$_SESSION['currentPageCalonMHS']['page_name']!='m.dulang.CalonMHS') {
				$_SESSION['currentPageCalonMHS']=array('page_name'=>'m.dulang.CalonMHS','page_num'=>0,'search'=>false,'semester_masuk'=>1,'DataMHS'=>array());												
			}
            $_SESSION['currentPageCalonMHS']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $semester=array('1'=>'GANJIL','2'=>'GENAP');  				
			$this->tbCmbSemesterMasuk->DataSource=$semester;
			$this->tbCmbSemesterMasuk->Text=$_SESSION['currentPageCalonMHS']['semester_masuk'];
			$this->tbCmbSemesterMasuk->dataBind();  
            
            $this->populateData();
            $this->setInfoToolbar();
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
    }
    public function setInfoToolbar() {                
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
        $semester = $this->setup->getSemester($_SESSION['currentPageCalonMHS']['semester_masuk']);		
		$this->lblModulHeader->Text="Program Studi $ps Tahun Masuk $tahunmasuk Semester $semester ";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageCalonMHS']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageCalonMHS']['search']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
    public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function changeTbSemesterMasuk ($sender,$param) {		
		$_SESSION['currentPageCalonMHS']['semester_masuk']=$this->tbCmbSemesterMasuk->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageKHS']['search']=true;
		$this->populateData($_SESSION['currentPageKHS']['search']);
	}
    public function populateData($search=false) {
        $kjur=$_SESSION['kjur']; 
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $semester_masuk=$_SESSION['currentPageCalonMHS']['semester_masuk'];
        if ($search) {
            $str = "SELECT DISTINCT(fp.no_formulir),fp.nama_mhs,t.idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk,t.kjur FROM transaksi t JOIN formulir_pendaftaran fp ON (fp.no_formulir=t.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=t.no_formulir) WHERE t.kjur=$kjur AND fp.ta=$tahun_masuk AND fp.idsmt=$semester_masuk AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk AND rm.no_formulir IS NULL";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'no_formulir' :
                    $clausa=" AND fp.no_formulir='$txtsearch'";                    
                    $jumlah_baris=$this->DB->getCountRowsOfTable (" transaksi t JOIN formulir_pendaftaran fp ON (fp.no_formulir=t.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=t.no_formulir) WHERE t.kjur=$kjur AND fp.ta=$tahun_masuk AND fp.idsmt=$semester_masuk AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk AND rm.no_formulir IS NULL$clausa",'DISTINCT(fp.no_formulir)');                    
                    $str = "$str $clausa";
                break;                
                case 'nama' :
                    $clausa=" AND fp.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable (" transaksi t JOIN formulir_pendaftaran fp ON (fp.no_formulir=t.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=t.no_formulir) WHERE t.kjur=$kjur AND fp.ta=$tahun_masuk AND fp.idsmt=$semester_masuk AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk AND rm.no_formulir IS NULL$clausa",'DISTINCT(fp.no_formulir)');                    
                    $str = "$str $clausa";
                break;
            }
        }else{            
            $str = "SELECT DISTINCT(fp.no_formulir),fp.nama_mhs,t.idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk,t.kjur FROM transaksi t JOIN formulir_pendaftaran fp ON (fp.no_formulir=t.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=t.no_formulir) WHERE t.kjur=$kjur AND fp.ta=$tahun_masuk AND fp.idsmt=$semester_masuk AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk AND rm.no_formulir IS NULL";
            $jumlah_baris=$this->DB->getCountRowsOfTable (" transaksi t JOIN formulir_pendaftaran fp ON (fp.no_formulir=t.no_formulir) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=t.no_formulir) WHERE t.kjur=$kjur AND fp.ta=$tahun_masuk AND fp.idsmt=$semester_masuk AND t.tahun=$tahun_masuk AND t.idsmt=$semester_masuk AND rm.no_formulir IS NULL",'DISTINCT(fp.no_formulir)');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageCalonMHS']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageCalonMHS']['page_num']=0;}
		$str = "$str ORDER BY fp.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('no_formulir','nama_mhs','idkelas','tahun_masuk','semester_masuk','kjur'));
		$r=$this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {            
            $v['nkelas']=$this->DMaster->getNamaKelasByID($v['idkelas']);
            $this->Finance->setDataMHS($v);
            $data=$this->Finance->getTresholdPembayaran($tahun_masuk,$semester_masuk,true);
            $v['total_bayar']='('.$this->Finance->toRupiah($data['total_biaya']).')'.$this->Finance->toRupiah($data['total_bayar']);
            $v['bool']=$data['bool'];
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function cekNomorFormulir ($sender,$param) {		
        $no_formulir=addslashes($param->Value);		
        if ($no_formulir != '') {
            try {
                if (!isset($_SESSION['currentPageCalonMHS']['DataMHS']['no_formulir'])) {
                    $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.kjur1,fp.kjur2,idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE fp.no_formulir=pm.no_formulir AND fp.no_formulir='$no_formulir'";
                    $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','kjur1','kjur2','idkelas','tahun_masuk','semester_masuk'));
                    $r=$this->DB->getRecord($str);
                    if (!isset($r[1])) {                                
                        throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
                    }
                    $datamhs=$r[1];     
                    $this->Finance->setDataMHS($datamhs);
                    if (!$spmb=$this->Finance->isLulusSPMB(true)) {
                        throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak lulus dalam SPMB.");		
                    }       
                    $datamhs['nama_ps1']=$_SESSION['daftar_jurusan'][$datamhs['kjur1']];
                    $datamhs['nama_ps2']=$datamhs['kjur2'] == '' ?'N.A' : $_SESSION['daftar_jurusan'][$datamhs['kjur2']];
                    if ($spmb['kjur']==$datamhs['kjur1'])
                        $datamhs['diterima_ps1']='<span class="label label-info">DITERIMA</span>';
                    else
                        $datamhs['diterima_ps2']='<span class="label label-info">DITERIMA</span>';
                    $datamhs['kjur']=$spmb['kjur'];
                    $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                    $this->Finance->setDataMHS($datamhs);                               
                    if ($this->Finance->isMhsRegistered()){
                        throw new Exception ("Calon Mahasiswa a.n ".$datamhs['nama_mhs']." dengan no formulir $no_formulir sudah terdaftar di P.S ".$_SESSION['daftar_jurusan'][$datamhs['kjur']]);
                    }
                    $data=$this->Finance->getTresholdPembayaran($datamhs['tahun_masuk'],$datamhs['semester_masuk'],true);						                                
                    if (!$data['bool']) {
                        throw new Exception ("Calon Mahasiswa a.n ".$this->Finance->dataMhs['nama_mhs']."($no_formulir) tidak bisa daftar ulang karena baru membayar(".$this->Finance->toRupiah($data['total_bayar'])."), harus minimal setengahnya sebesar (".$this->Finance->toRupiah($data['ambang_pembayaran']).") dari total (".$this->Finance->toRupiah($data['total_biaya']).")");
                    }
                    $_SESSION['currentPageCalonMHS']['DataMHS']=$datamhs;
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function Go($sender,$param) {	
        if ($this->Page->isValid) {            
            $no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
            $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.kjur1,fp.kjur2,idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE fp.no_formulir=pm.no_formulir AND fp.no_formulir='$no_formulir'";
            $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','kjur1','kjur2','idkelas','tahun_masuk','semester_masuk'));
            $r=$this->DB->getRecord($str);
            
            $datamhs=$r[1];
            $this->Finance->setDataMHS($datamhs);
            $spmb=$this->Finance->isLulusSPMB(true);
            $datamhs['nama_ps1']=$_SESSION['daftar_jurusan'][$datamhs['kjur1']];
            $datamhs['nama_ps2']=$datamhs['kjur2'] == '' ?'N.A' : $_SESSION['daftar_jurusan'][$datamhs['kjur2']];
            if ($spmb['kjur']==$datamhs['kjur1'])
                $datamhs['diterima_ps1']='<span class="label label-info">DITERIMA</span>';
            else
                $datamhs['diterima_ps2']='<span class="label label-info">DITERIMA</span>'; 
            
            $datamhs['kjur']=$spmb['kjur'];
            $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
            $_SESSION['currentPageDulangMHSBaru']['DataMHS']=$datamhs;
            $this->redirect('dulang.DetailDulangMHSBaru',true,array('id'=>$no_formulir));
        }
	}
}
?>