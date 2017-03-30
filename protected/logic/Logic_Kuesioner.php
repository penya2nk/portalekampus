<?php
prado::using ('Application.logic.Logic_Akademik');
class Logic_Kuesioner extends Logic_Akademik {			    
	public function __construct ($db) {
		parent::__construct ($db);				
	}
    /**
     * digunakan untuk menghitung hasil kuesioner     
     * @param type $idpengampu_penyelenggaraan
     * @param type $commandparameter 
     * @return type integer
     */
    public function hitungKuesioner($idpengampu_penyelenggaraan,$commandparameter) {        
        $str="(SELECT idkuesioner_jawaban FROM kuesioner_jawaban kj WHERE idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan GROUP BY idkrsmatkul) AS temp";
        $jumlah_mhs=$this->db->getCountRowsOfTable($str,'idkuesioner_jawaban');
        $str="kuesioner_jawaban kj,kuesioner_indikator ki WHERE ki.idindikator=kj.idindikator AND kj.idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";
        $totalnilai=$this->db->getSumRowsOfTable('nilai_indikator',$str);
        $str = "SELECT tahun,idsmt FROM v_pengampu_penyelenggaraan WHERE idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";
        $this->db->setFieldTable(array('tahun','idsmt'));
        $r=$this->db->getRecord($str);
        $ta=$r[1]['tahun'];
        $idsmt=$r[1]['idsmt'];
        $str="kuesioner k WHERE tahun='$ta' AND idsmt='$idsmt'";
        $jumlahsoal=$this->db->getCountRowsOfTable($str,'idkuesioner');
        $skor_tertinggi=$jumlahsoal*$jumlah_mhs*5;
        $skor_terendah=$jumlahsoal*$jumlah_mhs*1;
        $interval=($skor_tertinggi-$skor_terendah)/5;
        
        $maks_sangatburuk=$skor_terendah+$interval;
        $maks_buruk=$maks_sangatburuk+$interval;
        $maks_sedang=$maks_buruk+$interval;
        $maks_baik=$maks_sedang+$interval;
        $maks_sangatbaik=$maks_baik+$interval;
        
        if ($totalnilai >= $skor_terendah && $totalnilai < $maks_sangatburuk) {
            $n_kuan=1;
            $keterangan='SANGAT BURUK';
        }elseif ($totalnilai >= $maks_sangatburuk && $totalnilai < $maks_buruk) {
            $n_kuan=2;
            $keterangan='BURUK';
        }elseif ($totalnilai >= $maks_buruk && $totalnilai < $maks_sedang) {
            $n_kuan=3;
            $keterangan='SEDANG';
        }elseif ($totalnilai >= $maks_sedang && $totalnilai < $maks_baik) {
            $n_kuan=4;
            $keterangan='BAIK';
        }elseif ($totalnilai >= $maks_baik){
            $n_kuan=5;
            $keterangan='SANGAT BAIK';
        }else{
            $n_kuan=1;
            $keterangan='SANGAT BURUK';
        }
        
        if ($commandparameter == 'insert') {
            $str = "INSERT INTO kuesioner_hasil (idpengampu_penyelenggaraan, jumlah_mhs, total_nilai, jumlah_soal, skor_tertinggi, skor_terendah, intervals, maks_sangatburuk, maks_buruk, maks_sedang, maks_baik, maks_sangatbaik, n_kuan, n_kual) VALUES ($idpengampu_penyelenggaraan,$jumlah_mhs,$totalnilai,$jumlahsoal,$skor_tertinggi,$skor_terendah,$interval,$maks_sangatburuk,$maks_buruk,$maks_sedang,$maks_baik,$maks_sangatbaik,$n_kuan,'$keterangan')";
            $this->db->insertRecord($str);
        }elseif($commandparameter == 'update') {
            $str = "UPDATE kuesioner_hasil SET jumlah_mhs=$jumlah_mhs, total_nilai=$totalnilai, jumlah_soal=$jumlahsoal, skor_tertinggi=$skor_tertinggi, skor_terendah=$skor_terendah, intervals=$interval, maks_sangatburuk=$maks_sangatburuk, maks_buruk=$maks_buruk, maks_sedang=$maks_sedang, maks_baik=$maks_baik, maks_sangatbaik=$maks_sangatbaik, n_kuan=$n_kuan, n_kual='$keterangan' WHERE idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";            
            $this->db->updateRecord($str);
        }
    }
    
}
?>