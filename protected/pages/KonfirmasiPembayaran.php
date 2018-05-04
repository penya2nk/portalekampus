<?php
prado::using ('Application.MainPageF');
class KonfirmasiPembayaran extends MainPageF {
	public $DataMHS;
	public function onLoad($param) {		
		parent::onLoad($param);	         
		$this->createObj('Dmaster');
		if (!$this->IsPostBack&&!$this->IsCallBack) { 
		   
		}
	}    
	public function cekNomorPendaftaran ($sender,$param) {		
        $no_pendaftaran=addslashes($param->Value);		
        if ($no_pendaftaran != '') {
            try {
				$str  = "SELECT no_pendaftaran FROM formulir_pendaftaran_temp WHERE no_pendaftaran='$no_pendaftaran'";				
				$this->DB->setFieldTable(array('no_pendaftaran'));
				$r=$this->DB->getRecord($str);
				if (!isset($r[1])) {                                
					throw new Exception ("Nomor Pendaftaran ($no_pendaftaran) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
				}
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
	}
	public function loadData ($sender,$param) {	
		if ($this->IsValid) {
			$this->idProcess='add';
			$no_pendaftaran=addslashes($this->txtAddNomorPendaftaran->Text);	
			$str  = "SELECT no_pendaftaran, nama_mhs,tempat_lahir,tanggal_lahir,jk,email,telp_hp,kjur1,kjur2,idkelas,ta,idsmt,waktu_mendaftar,file_bukti_bayar FROM formulir_pendaftaran_temp WHERE no_pendaftaran='$no_pendaftaran'";
			$this->DB->setFieldTable(array('no_pendaftaran','nama_mhs','tempat_lahir','tanggal_lahir','jk','email','telp_hp','kjur1','kjur2','idkelas','ta','idsmt','waktu_mendaftar','file_bukti_bayar'));
			$r=$this->DB->getRecord($str);
			$r[1]['nama_ps_1']=$this->DMaster->getNamaProgramStudiByID($r[1]['kjur1']);
			$r[1]['nama_ps_2']=($r[1]['kjur2']>0)?$this->DMaster->getNamaProgramStudiByID($r[1]['kjur2']):'N.A';
			$this->DataMHS=$r[1];
			$this->hiddenid->Value=$no_pendaftaran;
			
			$this->imgBuktiBayar->ImageUrl=$r[1]['file_bukti_bayar'];
			$this->imgBuktiBayar->Width='400px';			
		}
	}    
	
	public function getDataMHS($idx) {
		 return $this->DataMHS[$idx];
	}
	 public function uploadBuktiBayar ($sender,$param) {
		if ($sender->getHasFile()) {
            $this->lblTipeFileError->Text='';
            $mime=$sender->getFileType();
            if($mime!="image/png" && $mime!="image/jpg" && $mime!="image/jpeg"){
                $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>File ini bukan tipe gambar</p>
                        </div>'; 
                $this->lblTipeFileError->Text=$error;
                return;
            }         

            if($mime=="image/png")	{
                if(!(imagetypes() & IMG_PNG)) {
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing png support in gd library.</p>
                        </div>'; 
                    $this->lblTipeFileError->Text=$error;                    
                    return;
                }
            }
            if(($mime=="image/jpg" || $mime=="image/jpeg")){
                if(!(imagetypes() & IMG_JPG)){                    
                    $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>missing jpeg support in gd library.</p>
                        </div>'; 
                    $this->lblTipeFileError->Text=$error;
                    return;
                }
            }
            $filename=substr(hash('sha512',rand()),0,8);
            $name=$sender->FileName;
            $part=$this->setup->cleanFileNameString($name);            
            $path="resources/files/pendaftaran_online/$filename-$part";
            $sender->saveAs($path);            
            chmod(BASEPATH."/$path",0644); 
            $this->imgBuktiBayar->ImageUrl=$path; 
			$this->imgBuktiBayar->Width='400px';
			$no_pendaftaran=$this->hiddenid->Value;
            $this->DB->updateRecord("UPDATE formulir_pendaftaran_temp SET file_bukti_bayar='$path' WHERE no_pendaftaran='$no_pendaftaran'");           
        }else {                    
            //error handling
            switch ($sender->ErrorCode){
                case 1:
                    $err="file size too big (php.ini).";
                break;
                case 2:
                    $err="file size too big (form).";
                break;
                case 3:
                    $err="file upload interrupted.";
                break;
                case 4:
                    $err="no file chosen.";
                break;
                case 6:
                    $err="internal problem (missing temporary directory).";
                break;
                case 7:
                    $err="unable to write file on disk.";
                break;
                case 8:
                    $err="file type not accepted.";
                break;
            }
            $error =  '<div class="alert alert-warning">                
                            <p><strong>Error:</strong>'.$err.'</p>
                        </div>';   
            $this->lblTipeFileError->Text=$error;
            return;   
        }
    }
}