<?php
prado::using ('Application.pagecontroller.k.pembayaran.CDetailPembayaranSemesterPendek');
class DetailPembayaranSemesterPendek Extends CDetailPembayaranSemesterPendek {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    public function checkPembayaranSemesterLalu () { 
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];	
		$ta=$datamhs['ta'];			
        $semester=2;
            
        $idkelas=$this->Finance->getKelasFromTransaksi($ta,$semester);
        $datamhs['idkelas']=$idkelas===false?$datamhs['idkelas']:$idkelas;
        $this->Finance->setDataMHS($datamhs);
        if ($idkelas!='C') {
            $totalbiaya=$this->Finance->getTotalBiayaMhs ('lama');
            $totalbayar=$this->Finance->getTotalBayarMhs($ta,$semester);
            $sisa=$totalbiaya-$totalbayar;

            $datadulang=$this->Finance->getDataDulang($semester,$ta);
            if ($sisa>0 && $datadulang['k_status'] != 'C') {
                $sisa=$this->Finance->toRupiah($sisa);
                $tasmt="T.A ".$this->DMaster->getNamaTA($ta).' semester '.$this->setup->getSemester($semester);
                throw new Exception ('Mahasiswa a.n '.$datamhs['nama_mhs']." Memiliki tunggakan sebesar ($sisa) pada $tasmt, harap untuk dilunasi terlebih dahulu.");
            }
        }	
	}
}
?>