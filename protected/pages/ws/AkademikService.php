<?php
prado::using('Application.pages.ws.MainService');
class AkademikService extends MainService  {   
    public function __construct () {
        parent::__construct();	        
	}	
    /**
     * 
     * @param string $nim nomor induk mahasiswa
     * @param string $ta tahun akademik
     * @param string $idsmt semester
     * @return array Daftar KRS Mahasiswa
     * @soapmethod
     */
    public function getKRS ($nim,$ta,$idsmt) {           
        $this->connectDB();
        $str = "SELECT idpenyelenggaraan,idkrsmatkul,kmatkul,nmatkul,sks,semester,batal,nidn,nama_dosen FROM v_krsmhs WHERE nim='$nim' AND tahun=$ta AND semester=$idsmt ORDER BY semester ASC,kmatkul ASC";
        $this->setFieldTable(array('idpenyelenggaraan','idkrsmatkul','kmatkul','nmatkul','sks','semester','batal','nidn','nama_dosen'));
        $r=$this->getRecord($str);
        return $r;
    }    
}
?>