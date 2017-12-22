<?php
prado::using ('Application.pagecontroller.k.pembayaran.CDetailPembayaranPiutangSemesterGanjil');
class DetailPembayaranPiutangSemesterGanjil Extends CDetailPembayaranPiutangSemesterGanjil {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    public function checkPembayaranSemesterLalu () { 
        $datamhs=$_SESSION['currentPagePembayaranSemesterGanjil']['DataMHS'];
		$tahun_masuk=$datamhs['tahun_masuk'];
		$semester_masuk=$datamhs['semester_masuk'];
		$ta=$datamhs['ta'];	
		$semester=$_SESSION['currentPagePembayaranSemesterGanjil']['semester'];
		if ($tahun_masuk == $ta && $semester_masuk==$semester) {						
			return true;
		}else{
			$semester=$semester-1;			
			if ($semester < 1) {
				$no_formulir=$datamhs['no_formulir'];
				$ta=($ta == $tahun_masuk)?$tahun_masuk:$ta-1;				
				$semester=$this->DB->checkRecordIsExist ('idsmt','transaksi',3," AND no_formulir=$no_formulir AND tahun=$ta")?3:2;									
			}else {
				$ta=($ta == $tahun_masuk)?$tahun_masuk:$ta;	
			}
			$idkelas=$this->Finance->getKelasFromTransaksi($ta,$semester);
			$datamhs['idkelas']=$idkelas===false?$datamhs['idkelas']:$idkelas;
            $this->Finance->setDataMHS($datamhs);
			if ($idkelas!='C') {
				$totalbiaya=($tahun_masuk==$ta&&$semester_masuk==$semester)?$this->Finance->getTotalBiayaMhs ():$this->Finance->getTotalBiayaMhs ('lama');
				$totalbayar=$this->Finance->getTotalBayarMhs($ta,$semester);
                $sisa=$totalbiaya-$totalbayar;
                if ($sisa>0) {
					$sisa=$this->Finance->toRupiah($sisa);
					$tasmt="T.A ".$this->DMaster->getNamaTA($ta).' semester '.$this->setup->getSemester($semester);
					throw new Exception ('Mahasiswa a.n '.$datamhs['nama_mhs']." Memiliki tunggakan sebesar ($sisa) pada $tasmt, harap untuk dilunasi terlebih dahulu.");
				}
			}
		}		
	}
}