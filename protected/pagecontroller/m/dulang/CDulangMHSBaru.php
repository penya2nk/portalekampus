<?php
prado::using ('Application.MainPageM');
class CDulangMHSBaru Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSBaru=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSBaru'])||$_SESSION['currentPageDulangMHSBaru']['page_name']!='m.dulang.DulangMHSBaru') {
				$_SESSION['currentPageDulangMHSBaru']=array('page_name'=>'m.dulang.DulangMHSBaru','page_num'=>0,'search'=>false,'semester_masuk'=>1,'DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSBaru']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $semester=array('1'=>'GANJIL','2'=>'GENAP');  				
			$this->tbCmbSemesterMasuk->DataSource=$semester;
			$this->tbCmbSemesterMasuk->Text=$_SESSION['currentPageDulangMHSBaru']['semester_masuk'];
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
        $semester = $this->setup->getSemester($_SESSION['currentPageDulangMHSBaru']['semester_masuk']);		
		$this->lblModulHeader->Text="Program Studi $ps Tahun Masuk $tahunmasuk Semester $semester ";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDulangMHSBaru']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDulangMHSBaru']['search']);
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
		$_SESSION['currentPageDulangMHSBaru']['semester_masuk']=$this->tbCmbSemesterMasuk->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function searchRecord ($sender,$param){
        $_SESSION['currentPageDulangMHSBaru']['search']=true;
        $this->populateData($_SESSION['currentPageDulangMHSBaru']['search']);
    }
    public function populateData($search=false) {
        $kjur=$_SESSION['kjur']; 
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $semester_masuk=$_SESSION['currentPageDulangMHSBaru']['semester_masuk'];  
        if ($search) {
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' AND d.k_status='A'";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'no_formulir' :
                    $clausa="AND vdm.no_formulir='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nim' :
                    $clausa="AND d.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.k_status='A' AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                   
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND d.tahun=$tahun_masuk AND d.idsmt=$semester_masuk AND vdm.kjur='$kjur' AND d.k_status='A'";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND vdm.tahun_masuk='$tahun_masuk' AND vdm.semester_masuk='$semester_masuk' AND kjur='$kjur'",'vdm.nim');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDulangMHSBaru']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDulangMHSBaru']['page_num']=0;}
		$str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','iddosen_wali','tanggal'));
		$result=$this->DB->getRecord($str);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function cekNomorFormulir ($sender,$param) {		
        $no_formulir=addslashes($param->Value);		
        if ($no_formulir != '') {
            try {
                if (!isset($_SESSION['currentPageDulangMHSBaru']['DataMHS']['no_formulir'])) {
                    $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.kjur1,fp.kjur2,idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE fp.no_formulir=pm.no_formulir AND fp.no_formulir='$no_formulir'";
                    $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','kjur1','kjur2','idkelas','tahun_masuk','semester_masuk'));
                    $r=$this->DB->getRecord($str);
                    if (!isset($r[1])) {                                
                        throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
                    }
                    $datamhs=$r[1];     
                    $datamhs['idsmt']=$datamhs['semester_masuk'];
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
                    $_SESSION['currentPageDulangMHSBaru']['DataMHS']=$datamhs;
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function Go($sender,$param) {	
        if ($this->Page->isValid) {            
            $no_formulir=addslashes($this->txtNoFormulir->Text);
            $this->redirect('dulang.DetailDulangMHSBaru',true,array('id'=>$no_formulir));
        }
	}
    public function viewRecord($sender,$param) {	
		$this->idProcess='view';		
		$nim=$this->getDataKeyField($sender,$this->RepeaterS);	
        $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,semester_masuk,iddosen_wali,d.idkelas,d.k_status FROM v_datamhs vdm JOIN dulang d ON (d.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE vdm.nim='$nim'";
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status'));
        $r=$this->DB->getRecord($str);	           
        $datamhs=$r[1];
        $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
        $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
        $this->hiddensemestermasuk->Value=$datamhs['semester_masuk'];
        $this->hiddentahunmasuk->Value=$datamhs['tahun_masuk'];
        $this->Demik->setDataMHS($datamhs);
	}
    public function deleteRecord ($sender,$param) {			
		$nim=$sender->CommandParameter;		
		$idsmt=$this->hiddensemestermasuk->Value;
		$ta=$this->hiddentahunmasuk->Value;
		$this->DB->query ('BEGIN');
		if ($this->DB->deleteRecord("dulang WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'")) {			
			$this->DB->deleteRecord("krs WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'");		
			$this->DB->query ('COMMIT');
            $this->redirect('dulang.DulangMHSBaru',true);
		}else {
			$this->DB->query ('ROLLBACK');
		}		
	}
}
?>