<?php
prado::using ('Application.MainPageF');
class PendaftaranSuccess extends MainPageF {
	public function onLoad($param) {		
		parent::onLoad($param);	         
		$this->createObj('Dmaster');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
		    try {
		        if (!isset($this->request['id'])) {
		            throw new Exception("No. Registrasi tidak ada.");
		        }		        
		        $no_registrasi=addslashes($this->request['id']);
		        $str = "SELECT no_pendaftaran,nama_mhs,tempat_lahir,tanggal_lahir,jk,email,telp_hp,kjur1,kjur2,idkelas,ta,idsmt,waktu_mendaftar FROM formulir_pendaftaran_temp WHERE no_pendaftaran='$no_registrasi'";
		        $this->DB->setFieldTable(array('no_pendaftaran','nama_mhs','tempat_lahir','tanggal_lahir','jk','email','telp_hp','kjur1','kjur2','idkelas','ta','idsmt','waktu_mendaftar'));
		        $r=$this->DB->getRecord($str);
		        
		        if (!isset($r[1])) {
		            throw new Exception("No. Registrasi ($no_registrasi) tidak terdaftar.");
		        }
		        $this->literalNoRegistrasi->Text=$no_registrasi;
		    }catch (Exception $e){
		        $this->idProcess='view';
		        $this->errorMessage->Text=$e->getMessage();
		    }
		   
		}
	}    
	
}