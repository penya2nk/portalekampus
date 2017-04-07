<?php
prado::using ('Application.MainPageM');
class CDetailDulangMHSBaru Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSBaru=true;                
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            try {
                if (isset($_SESSION['currentPageDulangMHSBaru']['DataMHS']['no_formulir'])) {
                    $datamhs=$_SESSION['currentPageDulangMHSBaru']['DataMHS'];
                    
                    $this->Demik->setDataMHS($datamhs);
                    $this->cmbAddDosenWali->DataSource=$this->DMaster->getListDosenWali();
                    $this->cmbAddDosenWali->dataBind();			
            
                    $kjur=$datamhs['kjur'];                    
                    $nim_nirm=$this->getMaxNimAndNirm ($kjur,$datamhs['tahun_masuk']);
                    $this->txtAddNIM->Text=$nim_nirm['nim'];
                    $this->txtAddNIRM->Text=$nim_nirm['nirm'];
                }else{
                    throw new Exception("No. Formulir belum ada di session.");
                }
            } catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
    }
    public function checkNIM ($sender,$param) {					
		$nim=$param->Value;		
        if ($nim != '') {
            try {   
                $str = "SELECT nama_mhs,tahun_masuk,nama_ps FROM v_datamhs WHERE nim='$nim'";
                $this->DB->setFieldTable(array('nama_mhs','tahun_masuk','nama_ps'));
                $r = $this->DB->getRecord($str);
                if (isset($r[1])) {  
                    $nama_mhs=$r[1]['nama_mhs'];
                    $tahun_masuk=$r[1]['tahun_masuk'];
                    $nama_ps=$r[1]['nama_ps'];
                    throw new Exception ("NIM ($nim) sudah terdaftar atas nama $nama_mhs P.S $nama_ps registrasi pada tahun $tahun_masuk");                                                   
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
                $str = "SELECT nama_mhs,tahun_masuk,nama_ps FROM v_datamhs WHERE nirm='$nirm'";
                $this->DB->setFieldTable(array('nama_mhs','tahun_masuk','nama_ps'));
                $r = $this->DB->getRecord($str);
                if (isset($r[1])) {  
                    $nama_mhs=$r[1]['nama_mhs'];
                    $tahun_masuk=$r[1]['tahun_masuk'];
                    $nama_ps=$r[1]['nama_ps'];
                    throw new Exception ("NIRM ($nirm) sudah terdaftar atas nama $nama_mhs P.S $nama_ps registrasi pada tahun $tahun_masuk");                                                   
                }                               
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }
	}
    public function saveData ($sender,$param) {		
		if ($this->IsValid) {	
            $datamhs=$_SESSION['currentPageDulangMHSBaru']['DataMHS'];						
			$ta=$datamhs['tahun_masuk'];							
			$semester=$datamhs['semester_masuk'];
			$tanggal=date ('Y-m-d H:m:s');
			$no_formulir=$datamhs['no_formulir'];
            $nim=  addslashes($this->txtAddNIM->Text);
            $nirm=addslashes($this->txtAddNIRM->Text);
            $kjur=$datamhs['kjur'];
			$kelas=$datamhs['idkelas'];
            $iddosen_wali=$this->cmbAddDosenWali->Text;
			$str = "INSERT INTO register_mahasiswa (nim,nirm,no_formulir,tahun,idsmt,tanggal,kjur,iddosen_wali,k_status,idkelas) VALUES ('$nim','$nirm','$no_formulir','$ta','$semester','$tanggal','$kjur','$iddosen_wali','A','$kelas')";			
			$this->DB->query ('BEGIN');
			if ($this->DB->insertRecord($str)) {
                $tasmt=$ta.$semester;
				$str = "INSERT INTO dulang (iddulang,nim,tahun,idsmt,tasmt,tanggal,idkelas,status_sebelumnya,k_status) VALUES (NULL,'$nim','$ta','$semester','$tasmt','$tanggal','$kelas','A','A')";
				$this->DB->insertRecord($str);
				$password=md5(1234);
				$str="UPDATE profiles_mahasiswa SET nim='$nim',userpassword='$password' WHERE no_formulir='$no_formulir'";
				$this->DB->updateRecord($str);
				$str = "UPDATE transaksi SET nim='$nim' WHERE no_formulir='$no_formulir'";
				$this->DB->updateRecord($str);			
				$this->DB->query('COMMIT');
                unset($_SESSION['currentPageDulangMHSBaru']);
                $this->redirect('dulang.DulangMHSBaru',true);
			}else {
				$this->DB->query('ROLLBACK');
			}
		}
	}
    public function closeDetailDulang ($sender,$param) {
        unset($_SESSION['currentPageDulangMHSBaru']);
        $this->redirect('dulang.DulangMHSBaru',true);
    }
}
?>