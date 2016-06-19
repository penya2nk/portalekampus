<?php
prado::using ('Application.MainPageM');
class CDulangMHSBaru Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSBaru=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSBaru'])||$_SESSION['currentPageDulangMHSBaru']['page_name']!='m.dulang.DulangMHSBaru') {
				$_SESSION['currentPageDulangMHSBaru']=array('page_name'=>'m.dulang.DulangMHSBaru','page_num'=>0,'search'=>false,'semester_masuk'=>1,'DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSBaru']['search']=false;             
		}	
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
                    $this->Finance->setDataMHS($datamhs);
                    if (!$spmb=$this->Finance->isLulusSPMB(true)) {
                        throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak lulus dalam SPMB.");		
                    }                
                    $datamhs['kjur']=$spmb['kjur'];
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
    public function Go($param,$sender) {	
        if ($this->Page->isValid) {            
            $no_formulir=addslashes($this->txtNoFormulir->Text);
            $this->redirect('dulang.DetailDulangMHSBaru',true,array('id'=>$no_formulir));
        }
	}
}
?>