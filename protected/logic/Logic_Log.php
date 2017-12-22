<?php
prado::using ('Application.Logic.Logic_Users');
class Logic_Log extends Logic_Users {	
	/**
	* id log master
	*/
	private $idlogmaster;
		
	public function __construct ($db) {
		parent::__construct ($db);
        $this->getIdLogMaster();
	}
	
	/**
	* digunakan untuk mendapatkan idlogmaster
	* @return integer idlogmaster
	*/
	public function getIdLogMaster () {		
		$userid=$this->getUserid();
		$tipe=$this->getTipeUser();
		$this->db->setFieldTable(array('idlog_master'));
		$r=$this->db->getRecord("SELECT idlog_master FROM log_master WHERE userid=$userid");
		if (isset($r[1])){
			$this->idlogmaster=$r[1]['idlog_master'];
		}else {
			$this->db->insertRecord("INSERT INTO log_master (userid,tipe_id) VALUES ('$userid','$tipe')");
			$r=$this->db->getRecord("SELECT idlog_master FROM log_master WHERE userid=$userid");
			$this->idlogmaster=$r[1]['idlog_master'];
		}
		return $this->idlogmaster;
	}
	/**
	* digunakan untuk masukan data log ke tabel transkrip asli
	* @return void
	*/
	public function insertLogIntoTranskripFinal($nim,$kmatkul,$nmatkul,$aktivitas,$nilai_awal,$nilai_akhir='') {		
        $idlogmaster=$this->idlogmaster;		
		$str = "INSERT INTO log_transkrip_asli (idlog_master,tanggal,nim,kmatkul,nmatkul,aktivitas,keterangan) VALUES ";
		$ket='';
		switch (strtolower($aktivitas)) {
            case 'delete' :
				$ket = "hapus nilai $nilai_awal";
			break;
            case 'deleteall' :
				$ket = "hapus nilai keseluruhan";
			break;
			case 'input' :
				$ket = "menginputkan nilai $nilai_awal";
			break;
			case 'update' :
				$nilai_akhir=$nilai_akhir == ''?'tidak ada nilai':$nilai_akhir;
				$ket = "mengubah nilai $nilai_awal menjadi $nilai_akhir";
			break;
		}		
		$str = "$str ('$idlogmaster',NOW(),'$nim','$kmatkul','$nmatkul','$aktivitas','$ket')";
		$this->db->insertRecord($str);
	}
}