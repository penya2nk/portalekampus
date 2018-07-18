<?php
prado::using ('Application.MainPageSA');
class CHome extends MainPageSA {    
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
            if (!isset($_SESSION['currentPageHome'])||$_SESSION['currentPageHome']['page_name']!='sa.Home') {                                                                                
                $tahun_pendaftaran=$_SESSION['tahun_pendaftaran'];

                for ($i=$tahun_pendaftaran-10;$i <=$tahun_pendaftaran;$i+=1) {
                    $data_mendaftar[$i]=0;
                    $data_dulang[$i]=0;
                }

                $str = "SELECT ta,COUNT(no_formulir) AS jumlah FROM formulir_pendaftaran WHERE ($tahun_pendaftaran-10) <= ta GROUP BY ta ORDER BY ta ASC";
                $this->DB->setFieldTable(array('ta','jumlah'));
                $r = $this->DB->getRecord($str);	                                
                while (list($k,$v)=each($r)) {
                    $data_mendaftar[$v['ta']]=$v['jumlah'];
                }

                $str = "SELECT tahun AS ta,COUNT(no_formulir) AS jumlah FROM register_mahasiswa WHERE ($tahun_pendaftaran-10) <= tahun GROUP BY tahun ORDER BY tahun ASC";                
                $r = $this->DB->getRecord($str);                
                while (list($k,$v)=each($r)) {
                    $data_dulang[$v['ta']]=$v['jumlah'];
                }
                $_SESSION['currentPageHome']=array('page_name'=>'sa.Home',
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
        $data1='';
        foreach ($datamendaftar as $tahun=>$jumlah) {
            $ta=$tahun-1;
            $data1=$data1."[gd($ta,12,31), $jumlah],";     
            $totalpendaftaran+=$jumlah;
        }
        $datadulang=$_SESSION['currentPageHome']['datadulang'];        
        $data2='';
        foreach ($datadulang as $tahun=>$jumlah) {
            $ta=$tahun-1;
            $data2=$data2."[gd($ta,12,31), $jumlah],";      
            $totaldulang+=$jumlah;
        }
        $this->datamendaftar1->Text="var data1 = [$data1];";
        $this->datamendaftar2->Text="var data2 = [$data2];";
        
        $this->literalTotalPendaftaran->Text=$totalpendaftaran;
        $this->literalTotalDulang->Text=$totaldulang;
        
        $mendaftartahunini=$datamendaftar[$_SESSION['tahun_pendaftaran']];
        $this->TotalMendaftarTahunINI=$mendaftartahunini;
        $dulangtahunini=isset($datadulang[$_SESSION['tahun_pendaftaran']]) ? $datadulang[$_SESSION['tahun_pendaftaran']] :0;
        $this->TotalDulangTahunINI=$dulangtahunini;
        
        $mendaftartahunlalu=isset($datamendaftar[$_SESSION['tahun_pendaftaran']-1])?$datamendaftar[$_SESSION['tahun_pendaftaran']-1]:0;
        $dulangtahunlalu=isset($datadulang[$_SESSION['tahun_pendaftaran']-1])?$datadulang[$_SESSION['tahun_pendaftaran']-1]:0;
        
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
        $tahun_pendaftaran=$_SESSION['tahun_pendaftaran'];
                                
        for ($i=$tahun_pendaftaran-10;$i <=$tahun_pendaftaran;$i+=1) {
            $data_mendaftar[$i]=0;
            $data_dulang[$i]=0;
        }

        $str = "SELECT ta,COUNT(no_formulir) AS jumlah FROM formulir_pendaftaran WHERE ($tahun_pendaftaran-10) <= ta GROUP BY ta ORDER BY ta ASC";
        $this->DB->setFieldTable(array('ta','jumlah'));
        $r = $this->DB->getRecord($str);                                    
        while (list($k,$v)=each($r)) {
            $data_mendaftar[$v['ta']]=$v['jumlah'];
        }

        $str = "SELECT tahun AS ta,COUNT(no_formulir) AS jumlah FROM register_mahasiswa WHERE ($tahun_pendaftaran-10) <= tahun GROUP BY tahun ORDER BY tahun ASC";                
        $r = $this->DB->getRecord($str);                
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
        $this->redirect('Home',true);
    }
}