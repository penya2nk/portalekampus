<?php

prado::using ('Application.logic.Logic_Global');
class Logic_Mahasiswa extends Logic_Global {					
    /**
	* property DataMHS
	*/
	public $DataMHS=array();
	
	public function __construct ($db) {
		parent::__construct ($db);			
	}	
    /**	
	* setter DataMHS
	*/
	public function setDataMHS ($dataMHS) {
		$this->DataMHS=$dataMHS;						
	}	
    /**	
	* getter DataMHS
    * @var $idx 
	*/
	public function getDataMHS ($idx=null) {
        if ($idx === null) {
            return $this->DataMHS;
        }else{
            return $this->DataMHS[$idx];
        }							
	}
    /**
	* apakah mahasiswa telah lulus SPMB
	* @param $with_nilai bila true return dengan nilai
	*/
	public function isLulusSPMB ($with_nilai=false) {		
        $no_formulir=$this->DataMHS['no_formulir'];
        $str = "SELECT nilai,ket_lulus,kjur FROM nilai_ujian_masuk WHERE no_formulir='$no_formulir' AND ket_lulus='1'";
        $this->db->setFieldTable (array('nilai','ket_lulus','kjur'));
        $result=$this->db->getRecord($str);
        $nilai=false;
        if (isset($result[1])) {				
            if ($with_nilai) {
                $nilai['lulus']=true;
                $nilai['nilai']=$result[1]['nilai'];
                $nilai['kjur']=$result[1]['kjur'];
            }else {
                $nilai=true;
            }            
        }	
        return $nilai;
	}
    /**
	* digunakan untuk mengecek apakah no formulir telah memiliki nim
	*
	*/
	public function isMhsRegistered ($tanpaprodi=false) {
        $no_formulir=$this->DataMHS['no_formulir'];
        $kjur=isset($this->DataMHS['kjur'])?$this->DataMHS['kjur']:'';
		$bool=$tanpaprodi==true?$this->db->checkRecordIsExist('no_formulir','register_mahasiswa',$no_formulir):$this->db->checkRecordIsExist('no_formulir','register_mahasiswa',$no_formulir," AND kjur=$kjur");
        
        return $bool;

	}
    /**
	* digunakan untuk mengetahui apakah no formulir sudah ada atau belum
	* @return bool
	*/
	public function isNoFormulirExist() {		
        $no_formulir=$this->DataMHS['no_formulir'];        
		$bool=$this->db->checkRecordIsExist('no_formulir','formulir_pendaftaran',$no_formulir);			
        return $bool;			
		
	}
    /**
	* digunakan untuk mendapatkan apakah mahasiswa ini pindahan atau bukan
	* @return bool
	*/
	public function isMhsPindahan ($nim,$getid=false) {		        
        $str = "SELECT iddata_konversi FROM data_konversi WHERE nim='$nim'";
        $this->db->setFieldTable(array('iddata_konversi'));
        $result = $this->db->getRecord($str);                
        $value=false;
        if ($getid) {
            if (isset($result[1])) {
                $value=$result[1]['iddata_konversi'];
            }
        }else {				
            if (isset($result[1])) {
                $value=true;
            }
        }
        return $value;					
	}
    /**
     * digunakan untuk mendapatkan status kelas mahasiswa saat ini apakah ekstens,regular,karyawan ?
     * @return array
     */
    public function getKelasMhs () {
        $nim=$this->DataMHS['nim'];
        $str = "SELECT d.tahun,d.idsmt,k.idkelas,k.nkelas FROM kelas k,dulang d WHERE k.idkelas=d.idkelas AND d.iddulang=(SELECT MAX(iddulang) FROM dulang WHERE nim='$nim')";
        $this->db->setFieldTable(array('tahun','idsmt','idkelas','nkelas'));					
        $r=$this->db->getRecord($str);					
        if (isset($r[1])) {						
            $kelas['idkelas']=$r[1]['idkelas'];
            $kelas['nkelas']=$r[1]['nkelas'];
            $str = "SELECT k.idkelas,k.nkelas,pk.tahun,pk.idsmt FROM pindahkelas pk,kelas k WHERE pk.idkelas_baru=k.idkelas AND idpindahkelas=(SELECT MAX(idpindahkelas) FROM pindahkelas WHERE nim='$nim')";					
            $r2=$this->db->getRecord($str);						
            if (isset($r2[1])) {
                $tasmt_dulang=$r[1]['tahun'].$r[1]['idsmt'];
                $tasmt_pindahkelas=$r2[1]['tahun'].$r2[1]['idsmt'];									
                if ($tasmt_dulang<$tasmt_pindahkelas) {
                    $kelas['idkelas']=$r2[1]['idkelas'];
                    $kelas['nkelas']=$r2[1]['nkelas'];
                }				
            }
        }else {
            $str = "SELECT fp.idkelas AS idkelas_fp,k.nkelas AS nkelas_fp,rm.idkelas AS idkelas_rm,k2.nkelas AS nkelas_rm FROM formulir_pendaftaran fp LEFT JOIN kelas k ON (fp.idkelas=k.idkelas) LEFT JOIN register_mahasiswa rm ON (rm.no_formulir=fp.no_formulir) LEFT JOIN kelas k2 ON (k2.idkelas=rm.idkelas) WHERE rm.nim='$nim'"; 						
            $this->db->setFieldTable(array('idkelas_fp','nkelas_fp','idkelas_rm','nkelas_rm'));
            $r2=$this->db->getRecord($str);
            if (isset($r2[1])) {
                $kelas['idkelas']=$r2[1]['idkelas_rm']==''?$r2[1]['idkelas_fp']:$r2[1]['idkelas_rm'];
                $kelas['nkelas']=$r2[1]['idkelas_rm']==''?$r2[1]['nkelas_fp']:$r2[1]['nkelas_rm'];;
            }
        }
        return $kelas;
    }
    /**
	* digunakan untuk mengecek mahasiswa baru atau bukan
	* @return boolean
	*/
	public function isMhsBaru ($tahun_sekarang,$semester_sekarang) {						
		if ($this->DataMHS['tahun_masuk']==$tahun_sekarang&&$this->DataMHS['semester_masuk']==$semester_sekarang) {
			return true;
        }else{
			return false;
        }
	}    
    /**
     * digunakan untuk mendapatkan jumlah status mahasiswa 
     * @param type $k_status
     */
    public function getJumlahSeluruhMHS ($k_status=null) {
        $jumlah=0;
        switch ($k_status) {
            case 'A' :
                $jumlah=$this->db->getCountRowsOfTable("register_mahasiswa WHERE k_status='A'",'nim');		            
            break;            
            case 'L' :                
                $jumlah=$this->db->getCountRowsOfTable("register_mahasiswa WHERE k_status='L'",'nim');		            
            break;  
            case 'C' :                
                $jumlah=$this->db->getCountRowsOfTable("register_mahasiswa WHERE k_status='C'",'nim');		            
            break;
            case 'N' :                
                $jumlah=$this->db->getCountRowsOfTable("register_mahasiswa WHERE k_status='N'",'nim');		            
            break;
        }
        return $jumlah;
    }
    /**
     * digunakan untuk merubah  mahasiswa [status, kelas, atau kedua-duanya]
     * @param type $mode
     * @param type $status
     * @param type $kelas
     * @return type booleans
     */
	public function updateRegisterMHS ($mode,$status=null,$kelas=null) {		
		$nim=$this->DataMHS['nim'];
		switch ($mode) {
			case 'status' :
				$str = "UPDATE register_mahasiswa SET k_status='$status' WHERE nim='$nim'";
			break;
			case 'kelas' :
				$str = "UPDATE register_mahasiswa SET idkelas='$kelas' WHERE nim='$nim'";
			break;			
			case 'all' :
				$str = "UPDATE register_mahasiswa SET idkelas='$kelas',status='$status' WHERE nim='$nim'";
			break;			
		}		
		return $this->db->updateRecord($str);
	}
    /**
     * digunakan untuk mendapatkan data daftar ulang
     * @param type $idsmt
     * @param type $tahun
     * @return boolean atau id dulang
     */
	public function getDataDulang ($idsmt,$tahun) {				
        $nim=$this->DataMHS['nim'];
        $str = "SELECT iddulang,nim,tahun,idsmt,tanggal,idkelas,status_sebelumnya,k_status FROM dulang WHERE nim='$nim' AND idsmt='$idsmt' AND tahun='$tahun'";			
        $this->db->setFieldTable(array('iddulang','nim','tahun','idsmt','tanggal','idkelas','status_sebelumnya','k_status'));
        $r=$this->db->getRecord($str);						        
        if (isset($r[1])) {				            
            return $r[1];
        }else {
            return false;
        }		
	}
    /**
     * digunakan untuk mendapatkan data daftar dulang
     * @param type $idsmt
     * @param type $tahun
     * @return boolean atau id dulang
     */
	public function getDataDulangBeforeCurrentSemester ($idsmt,$tahun) {				
        $nim=$this->DataMHS['nim'];
        $current_tasmt=$tahun.$idsmt;
        $str = ($this->getDataMHS('tahun_masuk')==$tahun&&$this->getDataMHS('semester_masuk')==$idsmt)?"SELECT iddulang,nim,tahun,idsmt,tanggal,idkelas,status_sebelumnya,k_status FROM dulang WHERE nim='$nim' AND tasmt <= $current_tasmt ORDER BY iddulang DESC LIMIT 1":"SELECT iddulang,nim,tahun,idsmt,tanggal,idkelas,status_sebelumnya,k_status FROM dulang WHERE nim='$nim' AND tasmt < $current_tasmt ORDER BY iddulang DESC LIMIT 1";
        $this->db->setFieldTable(array('iddulang','nim','tahun','idsmt','tanggal','idkelas','status_sebelumnya','k_status'));
        $r=$this->db->getRecord($str);						        
        if (isset($r[1])) {				            
            return $r[1];
        }else {
            return false;
        }	
	}
}