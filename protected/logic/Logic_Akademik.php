<?php
prado::using ('Application.logic.Logic_Mahasiswa');
class Logic_Akademik extends Logic_Mahasiswa {			
    /**
     * daftar semester matakuliah
     * @var type 
     */
    public static $SemesterMatakuliah = array ('none'=>' ',1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>'Matkul Pilihan Ganjil',10=>'Matkul Pilihan Genap');
    /**
     * daftar semester matakuliah bentuk romawi
     * @var type 
     */
    public static $SemesterMatakuliahRomawi = array (1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII');
    /**
     * daftar sks matakuliah
     * @var type 
     */
    public static $sks = array (0=>'0',1=>'1',2=>'2',3=>'3',4=>'4',5=>'5',6=>'6');   
    /**
     * informasi matakuliah
     * @var type array
     */
    public $InfoMatkul = array();
    /**
     * informasi kelas
     * @var type array
     */
    public $InfoKelas = array();
	public function __construct ($db) {
		parent::__construct ($db);				
	}
    /**
     * digunakan untuk mendapatkan daftar semester matakuliah dalam bentuk romawi
     */
    public function getSemesterMatakuliahRomawi () {
        return Logic_Akademik::$SemesterMatakuliahRomawi;
    }
    /**
	* digunakan untuk mendapatkan kode kurikulum program studi yang berlaku
    * @return $idkurikulum
    * @version 1.0 beta
	*/
	public function getIDKurikulum ($kjur) {
        switch ($kjur) {
            case 1 :
                return 22;            
            case 2 :
                return 23;            
            case 3 :
                return 24;            
            case 4 : //s2
                return 30;
        }
    }
    /**
	* digunakan untuk mendapatkan nama kurikulum program studi yang berlaku
    * @return $idkurikulum
    * @version 1.0 beta
	*/
	public function getKurikulumName ($kjur) {
        switch ($kjur) {
            case 1 :
                return '2014/2015';            
            case 2 :
                return '2014/2015';            
            case 3 :
                return 24;            
            case 4 : //s2
                return '2014/2015';
        }
    }
    /**
     * membersihkan kode matakuliah dari kurikulum
     * @param type $kode
     * @return kode matakuliah
     */
    public function getKMatkul ($kode) {
		$kmatkul=explode('_',$kode);
		return $kmatkul[1];
	}
    /**
     * digunakan untuk mendapatkan semester dan ta selanjutnya
     * @param type $tahun_sekarang
     * @param type $semester_sekarang
     * @return array
     */
	public function getNextSemesterAndTa ($tahun_sekarang,$semester_sekarang) {
		$data=array();
		if ($semester_sekarang == 1) {
			$data['semester']='Genap';
			$data['ta']=$tahun_sekarang . '-'.($tahun_sekarang+1);
		}elseif($semester_sekarang == 2) {
			$data['semester']='Ganjil';
			$data['ta']=($tahun_sekarang+1) . '-'.($tahun_sekarang+2);
		}
		return $data;
	}
    /**
     * digunakan untuk mendapatkan informasi suatu matakuliah berdasarkan idpenyelenggaraan
     * @param type $id
     * @param type $mode_info
     * @return array
     */
	public function getInfoMatkul($id,$mode_info) {        
		switch ($mode_info) {
			case 'penyelenggaraan' :
				$this->db->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nama_dosen','nidn','kjur','tahun','idsmt'));
				$str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nama_dosen,nidn,kjur,tahun,idsmt FROM v_penyelenggaraan WHERE idpenyelenggaraan='$id'";
				$r=$this->db->getRecord($str);
				if (isset($r[1])) {
					$r[1]['kmatkul']=$this->getKmatkul($r[1]['kmatkul']);					
					$r[1]['jumlah_peserta']=$this->getJumlahMhsInPenyelenggaraan($id);
					$this->InfoMatkul=$r[1]; 
				}
			break;
			case 'pengampu_penyelenggaraan' :
				$this->db->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nama_dosen','nidn'));
				$str = "SELECT pp.idpenyelenggaraan,vp.kmatkul,vp.nmatkul,vp.sks,vp.semester,pp.iddosen,d.nama_dosen,d.nidn FROM v_penyelenggaraan vp,pengampu_penyelenggaraan pp,dosen d WHERE d.iddosen=pp.iddosen AND pp.idpenyelenggaraan=vp.idpenyelenggaraan AND pp.idpengampu_penyelenggaraan='$id'";
				$r=$this->db->getRecord($str);
				if (isset($r[1])) {
					$r[1]['kmatkul']=$this->getKmatkul($r[1]['kmatkul']);					
					$r[1]['jumlah_peserta']=$this->getJumlahMhsInPengampuPenyelenggaraan($id);
					$this->InfoMatkul=$r[1]; 
				}
			break;
			case 'krsmatkul' :
				$this->db->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nama_dosen','nidn'));
				$str = "SELECT vp.idpenyelenggaraan,vp.kmatkul,vp.nmatkul,vp.sks,vp.semester,vp.iddosen,vp.nama_dosen,vp.nidn FROM v_penyelenggaraan vp,krsmatkul k WHERE k.idpenyelenggaraan=vp.idpenyelenggaraan AND k.idkrsmatkul='$id'";
				$r=$this->db->getRecord($str);				
				if (isset($r[1])) {
					$r[1]['kmatkul']=$this->getKmatkul($r[1]['kmatkul']);					
					$this->InfoMatkul=$r[1]; 					
				}
			break;			
		}	
        
        return $this->InfoMatkul; 
	}   
    /**
     * digunakan untuk mendapatkan informasi kelas berdasarkan idkelas_mhs
     * @param type $id
     * @return array
     */
	public function getInfoKelas($id) {
        $str = "SELECT km.idkelas_mhs,km.idkelas,km.nama_kelas,km.hari,km.jam_masuk,km.jam_keluar,vpp.iddosen,vpp.nama_dosen,vpp.nidn,vpp.kmatkul,vpp.nmatkul,vpp.sks,vpp.semester,rk.namaruang,rk.kapasitas FROM kelas_mhs km JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) LEFT JOIN ruangkelas rk ON (rk.idruangkelas=km.idruangkelas) WHERE idkelas_mhs=$id";
        $this->db->setFieldTable(array('idkelas_mhs','iddosen','iddosen','nama_dosen','nidn','kmatkul','nmatkul','sks','semester','idkelas','nama_kelas','hari','jam_masuk','jam_keluar','namaruang','kapasitas'));
        $r = $this->db->getRecord($str);
        if (isset($r[1])) {
            $r[1]['kmatkul']=$this->getKmatkul($r[1]['kmatkul']);            
            $r[1]['jumlah_peserta']=$this->db->getCountRowsOfTable("kelas_mhs_detail WHERE idkelas_mhs=$id",'idkelas_mhs');
            $this->InfoKelas=$r[1];
        }
        return $this->InfoKelas;
    }
    /**
     * digunakan untuk mendapatkan jumlah mahasiswa dalam penyelenggaraan
     * @param type int $idpenyelenggaraan
     * @param type int $status 
     * @return type int
     */
	public function getJumlahMhsInPenyelenggaraan ($idpenyelenggaraan,$status=null) {        
		$str = "SELECT COUNT(vkm.idpenyelenggaraan) AS jmlh_peserta FROM v_krsmhs vkm WHERE vkm.idpenyelenggaraan='$idpenyelenggaraan'$status";
        $this->db->setFieldTable(array ('jmlh_peserta'));
        $r=$this->db->getRecord($str);
        return $r[1]['jmlh_peserta'];	
	}	
    /**
     * digunakan untuk mendapatkan jumlah mahasiswa dalam pengampu penyelenggaraan
     * @param type $idpengampu_penyelenggaraan
     * @return type int
     */
	public function getJumlahMhsInPengampuPenyelenggaraan ($idpengampu_penyelenggaraan) {
		$str = "SELECT COUNT(vkm.idpenyelenggaraan) AS jmlh_peserta FROM kelas_mhs_detail kmd,v_krsmhs vkm,v_datamhs vdm,kelas_mhs km WHERE vkm.sah=1 AND vkm.batal=0 AND km.idkelas_mhs=kmd.idkelas_mhs AND kmd.idkrsmatkul=vkm.idkrsmatkul AND vkm.nim=vdm.nim AND km.idpengampu_penyelenggaraan='$idpengampu_penyelenggaraan'";
        $this->db->setFieldTable(array ('jmlh_peserta'));
        $result=$this->db->getRecord($str);
        return $result[1]['jmlh_peserta'];		
	}
    /**
     * digunakan untuk mengecek apakah krs telah sah atau belum
     * @return boolean
     * @throws Exception
     */
    public function isKrsSah ($tahun,$idsmt) {		
        $nim=$this->DataMHS['nim'];
        $str = "SELECT sah FROM krs WHERE idsmt=$idsmt AND tahun=$tahun AND nim='$nim'";			
        $this->db->setFieldTable(array('sah'));
        $r=$this->db->getRecord($str);
        $bool=false;
        if (isset($r[1])) {
            $bool=$r[1]['sah'];			
        }
        return $bool;
	}
    /**
     * digunakan untuk mendapatkan ketua program studi
     * @param type $kjur
     * @return type
     */
    public function getKetuaPRODI ($kjur) {
		$str = "SELECT k.idkjur,d.nidn,CONCAT(d.gelar_depan,' ',d.nama_dosen,' ',d.gelar_belakang) AS nama_dosen,d.nipy,ja.nama_jabatan FROM kjur k,dosen d,jabatan_akademik ja WHERE d.idjabatan=ja.idjabatan AND k.iddosen=d.iddosen AND k.kjur='$kjur' AND default_=1";
		$this->db->setFieldTable(array('idkjur','nidn','nama_dosen','nipy','nama_jabatan')); 
		$result=$this->db->getRecord($str);		
		return $result[1];
	}
    /**
     * digunakan untuk mendapatkan daftar dosen pada penyelenggaraan
     * @param type $idsmt
     * @param type $tahun
     * @return daftar dosen
     */
    public function getListDosenFromPenyelenggaraan ($idsmt,$tahun) {
        $str = "SELECT DISTINCT(pp.iddosen) AS iddosen,CONCAT(gelar_depan,' ',nama_dosen,gelar_belakang) AS nama_dosen,nidn FROM penyelenggaraan p,pengampu_penyelenggaraan pp,dosen d WHERE p.idpenyelenggaraan=pp.idpenyelenggaraan AND d.iddosen=pp.iddosen AND p.idsmt=$idsmt AND p.tahun=$tahun ORDER BY d.nama_dosen ASC";
		$this->db->setFieldTable(array('iddosen','nidn','nama_dosen'));
		$r=$this->db->getRecord($str);
        $result=array('none'=>' ');	
		if (isset($r[1])) {			
            while (list($k,$v)=each($r)) {
                $result[$v['iddosen']]=$v['nama_dosen']. ' ['.$v['nidn'].']';
            }			
		}
        return $result;
    }
    /**
     * digunakan untuk mendapatkan daftar dosen pada penyelenggaraan
     * @param type $idsmt
     * @param type $tahun
     * @return daftar jadwal
     */
    public function getJadwalKuliahMHS ($idsmt,$tahun) {
        $nim=$this->DataMHS['nim'];
        $str = "SELECT * FROM krs k JOIN krsmatkul km ON (km.idkrsmatkul=k.idkrsmatkul) LEFT JOIN kelas_mhs_detail kmd ON (kmd.idkrsmatkul=km.idkrsmatkul) JOIN kelas_mhs km (km.idkelas_mhs=kmd.idkelas_mhs) JOIN v_pengampu_penyelenggaraan vpp ON (km.idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) WHERE k.nim='$nim' AND k.tahun='$tahun' AND k.idsmt='$idsmt' ";
    }    
}
?>