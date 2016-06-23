<?php
prado::using ('Application.pagecontroller.m.dulang.CDetailDulangMHSBaru');
class DetailDulangMHSBaru Extends CDetailDulangMHSBaru {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    /**
     * digunakan untuk mendapatkan nim maksimal saat ini tiap program studi
     * @param type $kjur
     * @param type $ta
     * @return type array
     * @throws Exception
     */
	public function getMaxNimAndNirm ($kjur,$ta) {        
		$str = "SELECT MAX(NIM) AS nim FROM register_mahasiswa WHERE tahun='$ta' AND kjur='$kjur'";
		$this->DB->setFieldTable(array('nim'));
		$r=$this->DB->getRecord($str);
		if ($r[1]['nim']=='') {		
            $tahun=substr($ta,2,2);
            switch($kjur) {
                case 1 :
                    $kode=201;
                break;
                case 2 :
                    $kode=204;
                break;
                case 3 :
                    $kode=203;
                break;            
            }
			$data['nim']=$tahun.'10'.$kjur.'001';			
			$data['nirm']=$tahun.'103035'.$kode.'001';
		}else {
			$nim='1'.$r[1]['nim'];
			$nim+=1;
			$nim=substr($nim,1,strlen($nim));
			$data['nim']=$nim;
			$str = "SELECT MAX(NIRM) AS nirm FROM register_mahasiswa WHERE tahun='$ta' AND kjur='$kjur'";
			$this->DB->setFieldTable(array('nirm'));
			$r=$this->DB->getRecord($str);            
            $old_nirm=$r[1]['nirm'];
			$nomor_urut_nirm=substr($old_nirm,-4,  strlen($old_nirm));
            $part_nim=substr($old_nirm,0,  strlen($old_nirm)-4);
            $nomor_urut_nirm+=1;
            $nirm=$part_nim.$nomor_urut_nirm;
			$data['nirm']=$nirm;
		}
		return $data;
	}
}
?>