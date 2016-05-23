<?php
prado::using ('Application.logic.Logic_Akademik');
class Logic_Kuesioner extends Logic_Akademik {			    
	public function __construct ($db) {
		parent::__construct ($db);				
	}
    /**
     * digunakan untuk mendapatkan hasil kuesioner dalam bentuk kualitatif
     * @param type $idpengampu_penyelenggaraan
     * @return type string
     */
    public function getResultInKualitatif($idpengampu_penyelenggaraan) {
        $str = "SELECT SUM(nilai_indikator)/COUNT(idkuesioner_jawaban) AS rata2 FROM kuesioner_jawaban kj,kuesioner_indikator ki WHERE ki.idindikator=kj.idindikator AND kj.idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";        
        $this->db->setFieldTable (array('rata2'));			
		$r=$this->db->getRecord($str);	
        $result='N.A';
        if (isset($r[1])) {
            $rata2=$r[1]['rata2'];
            if ($rata2 >= 85) {
                $result='SANGAT BAIK';
            }elseif ($rata2 >= 70 && $rata2 < 85) {
                $result='BAIK';
            }elseif ($rata2 >= 55 && $rata2 < 70) {
                $result='CUKUP';
            }elseif ($rata2 >= 40 && $rata2 < 55) {            
                $result='KURANG';
            }else{
                $result='SANGAT KURANG';
            }
        }
        return $result;
    }
}
?>