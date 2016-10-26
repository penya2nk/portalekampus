<?php
prado::using ('Application.pagecontroller.k.pembayaran.CDetailPembayaranSemesterGenap');
class DetailPembayaranSemesterGenap Extends CDetailPembayaranSemesterGenap {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    public function checkPembayaranSemesterLalu () { 
        $datamhs=$_SESSION['currentPagePembayaranSemesterGenap']['DataMHS'];
		$tahun_masuk=$datamhs['tahun_masuk'];
		$semester_masuk=$datamhs['semester_masuk'];
		$ta=$datamhs['ta'];			
		if ($tahun_masuk == $ta && $semester_masuk==2) {						
			return true;
		}else{			
            $semester=1;
            $ta=($ta == $tahun_masuk)?$tahun_masuk:$ta;		
			$idkelas=$this->Finance->getKelasFromTransaksi($ta,$semester);
			$datamhs['idkelas']=$idkelas===false?$datamhs['idkelas']:$idkelas;
            $this->Finance->setDataMHS($datamhs);
			if ($idkelas!='C') {
				$totalbiaya=($tahun_masuk==$ta&&$semester_masuk==$semester)?$this->Finance->getTotalBiayaMhs ():$this->Finance->getTotalBiayaMhs ('lama');
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
}
?>