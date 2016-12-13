<?php
prado::using ('Application.MainPageM');
class CHome extends MainPageM {    
    public $TotalMendaftarTahunINI=0;
    public $TotalDulangTahunINI=0;
    public $PersentaseMendaftarTahunINI=0;
    public $PersentaseDulangTahunINI=0;
    public $TooltipMendaftar;
    public $TooltipDulang;
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;       
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {              
            if (!isset($_SESSION['currentPageHome'])||$_SESSION['currentPageHome']['page_name']!='m.Home') {                                                                                
                $tahun_masuk=$_SESSION['ta'];
                $str = "SELECT ta,COUNT(no_formulir) AS jumlah FROM formulir_pendaftaran WHERE ($tahun_masuk-10) <= ta GROUP BY ta ORDER BY ta ASC";
                $this->DB->setFieldTable(array('ta','jumlah'));
                $r = $this->DB->getRecord($str);	
                $data_mendaftar=array();
                while (list($k,$v)=each($r)) {
                    $data_mendaftar[$v['ta']]=$v['jumlah'];
                }
                $str = "SELECT tahun AS ta,COUNT(no_formulir) AS jumlah FROM register_mahasiswa WHERE ($tahun_masuk-10) <= tahun GROUP BY tahun ORDER BY tahun ASC";                
                $r = $this->DB->getRecord($str);	
                $data_dulang=array();
                while (list($k,$v)=each($r)) {
                    $data_dulang[$v['ta']]=$v['jumlah'];
                }
                $_SESSION['currentPageHome']=array('page_name'=>'m.Home',
                                                   'jumlahmhsaktif'=>$this->Demik->getJumlahSeluruhMHS('A'),                                                   
                                                   'jumlahmhslulus'=>$this->Demik->getJumlahSeluruhMHS('L'),
                                                   'jumlahmhscuti'=>$this->Demik->getJumlahSeluruhMHS('C'),                                                   
                                                   'jumlahmhsnonaktif'=>$this->Demik->getJumlahSeluruhMHS('N'),                                                   
                                                   'datamendaftar'=>$data_mendaftar,
                                                   'datadulang'=>$data_dulang
                                                   );
            }
            $this->populateData();
		}                
	}  
    public function populateData () {
        $totalpendaftaran=0;
        $totaldulang=0;
        $datamendaftar=$_SESSION['currentPageHome']['datamendaftar'];
        foreach ($datamendaftar as $tahun=>$jumlah) {
            $ta=$tahun-1;
            $data1=$data1."[gd($ta), $jumlah],";     
            $totalpendaftaran+=$jumlah;
        }
        $datadulang=$_SESSION['currentPageHome']['datadulang'];
        foreach ($datadulang as $tahun=>$jumlah) {
            $ta=$tahun-1;
            $data2=$data2."[gd($ta), $jumlah],";      
            $totaldulang+=$jumlah;
        }
        $this->datamendaftar1->Text="var data1 = [$data1];";
        $this->datamendaftar2->Text="var data2 = [$data2];";
        
        $this->literalTotalPendaftaran->Text=$totalpendaftaran;
        $this->literalTotalDulang->Text=$totaldulang;
        
        $mendaftartahunini=$datamendaftar[$_SESSION['ta']];
        $this->TotalMendaftarTahunINI=$mendaftartahunini;
        $dulangtahunini=$datadulang[$_SESSION['ta']];
        $this->TotalDulangTahunINI=$dulangtahunini;
        
        $mendaftartahunlalu=$datamendaftar[$_SESSION['ta']-1];
        $dulangtahunlalu=$datadulang[$_SESSION['ta']-1];
        
        $persenmendaftartahunini=($mendaftartahunini > 0) ? @ number_format(($mendaftartahunini/$mendaftartahunlalu)*100,2):0;
        $this->PersentaseMendaftarTahunINI=$persenmendaftartahunini;
        $persendulangtahunini=($dulangtahunini > 0) ? @ number_format(($dulangtahunini/$dulangtahunlalu)*100,2):0;
        $this->PersentaseDulangTahunINI=$persendulangtahunini;
        if ($persenmendaftartahunini <= 100){
            $downmendaftar=number_format(100-$persenmendaftartahunini,2);
            $this->TooltipMendaftar = $downmendaftar.'% Down';
        }else{
            $upmendaftar=abs(number_format(100-$persenmendaftartahunini,2));
            $this->TooltipMendaftar = $upmendaftar.'% Up';
        }
        
        if ($persendulangtahunini <= 100){
            $downdulang=number_format(100-$persendulangtahunini,2);
            $this->TooltipDulang = $downdulang.'% Down';
        }else{
            $updulang=abs(number_format(100-$persendulangtahunini,2));
            $this->TooltipDulang = $updulang.'% Up';
        }
    }
    public function refreshPage ($sender,$param) {
        unset($_SESSION['currentPageHome']);
        $this->redirect('Home',true);
    }
}
?>