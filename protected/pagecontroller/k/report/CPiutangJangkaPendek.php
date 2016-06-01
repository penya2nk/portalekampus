<?php
prado::using ('Application.MainPageK');
class CPiutangJangkaPendek extends MainPageK {
	public function onLoad($param) {		
		parent::onLoad($param);						
        $this->showReport=true;
        $this->showReportPiutangJangkaPendek=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPagePiutangJangkaPendek'])||$_SESSION['currentPagePiutangJangkaPendek']['page_name']!='k.report.PiutangJangkaPendek') {
				$_SESSION['currentPagePiutangJangkaPendek']=array('page_name'=>'k.report.PiutangJangkaPendek','page_num'=>0,'search'=>false,'kelas'=>'none','tahun_masuk'=>$_SESSION['tahun_masuk']);												
			}
            $_SESSION['currentPagePiutangJangkaPendek']['search']=false;                       
            $daftar_ps=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');            
			$this->tbCmbPs->DataSource=$daftar_ps;
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();				
                        
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
            $this->tbCmbTA->Text=$_SESSION['ta'];
            $this->tbCmbTA->dataBind();	
            
			$tahun_masuk=$this->getAngkatan (false);	
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();
            
            $kelas=$this->DMaster->getListKelas();
            $kelas['none']='All';
			$this->tbCmbKelas->DataSource=$kelas;
			$this->tbCmbKelas->Text=$_SESSION['currentPagePiutangJangkaPendek']['kelas'];			
			$this->tbCmbKelas->dataBind();		
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

			$this->populateData();
            $this->setInfoToolbar();
		}		
	}
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $tahun_masuk=$this->DMaster->getNamaTA($_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk']);	
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		        
		$this->lblModulHeader->Text="Program Studi $ps Tahun Masuk $tahun_masuk T.A $ta ";        
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();        
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {    				
		$_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;		        
		$this->setInfoToolbar(); 
        $this->populateData();
	}
    public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;		        
		$_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk']=$_SESSION['ta'];
		$this->tbCmbTahunMasuk->DataSource=$this->getAngkatan(false);
		$this->tbCmbTahunMasuk->Text=$_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk'];
		$this->tbCmbTahunMasuk->dataBind();		
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbKelas ($sender,$param) {				
		$_SESSION['currentPagePiutangJangkaPendek']['kelas']=$this->tbCmbKelas->Text;
        $this->setInfoToolbar(); 
		$this->populateData();
	}    
    public function changeTbStatus ($sender,$param) {				
		$_SESSION['currentPagePiutangJangkaPendek']['currentPagePiutangJangkaPendek']['k_status']=$this->tbCmbStatus->Text;		        
		$this->populateData();
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePiutangJangkaPendek']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPagePiutangJangkaPendek']['search']);
	}    
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPagePiutangJangkaPendek']['search']=true;
        $this->populateData($_SESSION['currentPagePiutangJangkaPendek']['search']);
	}    
	public function populateData ($search=false) {			
        $kjur=$_SESSION['kjur'];  
        $ta=$_SESSION['ta'];                    
        $tahun_masuk=$_SESSION['currentPagePiutangJangkaPendek']['tahun_masuk'];                    
             
        $kelas=$_SESSION['currentPagePiutangJangkaPendek']['kelas'];
        $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
        $jumlah_baris=$this->DB->getCountRowsOfTable("v_datamhs WHERE kjur=$kjur AND tahun_masuk=$tahun_masuk AND k_status!='L' $str_kelas",'nim');		
        //$str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,idkelas,tahun_masuk,semester_masuk FROM v_datamhs WHERE kjur='$kjur'AND tahun_masuk=$tahun_masuk AND k_status!='L' AND nim='14101174' $str_kelas";			
        //$str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,idkelas,tahun_masuk,semester_masuk FROM v_datamhs WHERE kjur='$kjur'AND tahun_masuk=$tahun_masuk AND k_status!='L' AND nim='14101001' $str_kelas";			        
        $str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,idkelas,tahun_masuk,semester_masuk FROM v_datamhs WHERE kjur='$kjur'AND tahun_masuk=$tahun_masuk AND k_status!='L' $str_kelas";			
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePiutangJangkaPendek']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=6;$_SESSION['currentPagePiutangJangkaPendek']['page_num']=0;}
        $str = "$str ORDER BY nim ASC,nama_mhs ASC LIMIT $offset,$limit";				
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','idkelas','tahun_masuk','semester_masuk'));
		$r = $this->DB->getRecord($str,$offset+1);	
        $result = array();      
        
        $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idkelas'=>'A'));
        $komponen_biaya['A']['baru']=$this->getTotalBiayaMhs('baru');            
        $komponen_biaya['A']['lama']=$this->getTotalBiayaMhs('lama');            
        $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idkelas'=>'B'));
        $komponen_biaya['B']['baru']=$this->getTotalBiayaMhs('baru');            
        $komponen_biaya['B']['lama']=$this->getTotalBiayaMhs('lama');            
        $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idkelas'=>'C'));
        $komponen_biaya['C']['baru']=$this->getTotalBiayaMhs('baru');            
        $komponen_biaya['C']['lama']=$this->getTotalBiayaMhs('lama');            
                
        while (list($k,$v)=each($r)) {
            $no_formulir=$v['no_formulir'];                                          
            $nim=$v['nim'];                                          
            $idkelas=$v['idkelas'];            
            $v['n_kelas']=$this->DMaster->getNamaKelasByID($idkelas);            
            //status tiap semester ganjil dan genap
            $str = "SELECT k_status FROM dulang WHERE idsmt=1 AND tahun=$ta AND nim='$nim'";
            $this->DB->setFieldTable(array('k_status'));
            $dulang_ganjil=$this->DB->getRecord($str);            
            $v['n_status_ganjil']=isset($dulang_ganjil[1])?$this->DMaster->getNamaStatusMHSByID ($dulang_ganjil[1]['k_status']):'N.A';                                    
            $str = "SELECT k_status FROM dulang WHERE idsmt=2 AND tahun=$ta AND nim='$nim'";
            $this->DB->setFieldTable(array('k_status'));
            $dulang_genap=$this->DB->getRecord($str);            
            $v['n_status_genap']=isset($dulang_genap[1])?$this->DMaster->getNamaStatusMHSByID ($dulang_genap[1]['k_status']):'N.A';                                    
            
            //perhitungan
            $biaya=$this->getTotalBayarMHS($no_formulir,$ta,$tahun_masuk,$v['semester_masuk'],$komponen_biaya,$idkelas);
                   
            $v['sudah_bayar_ganjil']=$this->Finance->toRupiah($biaya[1]['sudahbayar']);
            $v['belum_bayar_ganjil']=$this->Finance->toRupiah($biaya[1]['belumbayar']);                        
            $v['sudah_bayar_genap']=$this->Finance->toRupiah($biaya[2]['sudahbayar']);
            $v['belum_bayar_genap']=$this->Finance->toRupiah($biaya[2]['belumbayar']);
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }
    
    public function getTotalBayarMHS ($no_formulir,$ta,$tahun_masuk,$semester_masuk,$komponen_biaya,$idkelas) {                        
        $sudahbayar=array(1=>array('sudahbayar'=>0,'belumbayar'=>0),2=>array('sudahbayar'=>0,'belumbayar'=>0));
        if ($ta==$tahun_masuk && $semester_masuk == 1) {            
            $str = "SELECT dibayarkan FROM bipend WHERE no_formulir=$no_formulir";						
            $this->DB->setFieldTable(array('dibayarkan'));
            $bipend=$this->DB->getRecord($str); 	
            $biaya_pendaftaran=$bipend[1]['dibayarkan'];  

            $kewajiban_ganjil=$komponen_biaya[$idkelas]['baru'];
            $pembayaran_ganjil=$this->DB->getSumRowsOfTable('dibayarkan',"v_transaksi WHERE no_formulir='$no_formulir' AND tahun=$ta AND idsmt=1")+$biaya_pendaftaran;
            $sudahbayar[1]['sudahbayar']=$pembayaran_ganjil;
            $sudahbayar[1]['belumbayar']=$kewajiban_ganjil-$pembayaran_ganjil;
            
            $kewajiban_genap=$komponen_biaya[$idkelas]['lama'];
            $pembayaran_genap=$this->DB->getSumRowsOfTable('dibayarkan',"v_transaksi WHERE no_formulir='$no_formulir' AND tahun=$ta AND idsmt=2");
            $sudahbayar[2]['sudahbayar']=$pembayaran_genap;
            $sudahbayar[2]['belumbayar']=$kewajiban_genap-$pembayaran_genap;
        }elseif ($ta==$tahun_masuk && $semester_masuk == 2) {
            $str = "SELECT dibayarkan FROM bipend WHERE no_formulir=$no_formulir";						
            $this->DB->setFieldTable(array('dibayarkan'));
            $bipend=$this->DB->getRecord($str); 	
            $biaya_pendaftaran=$bipend[1]['dibayarkan'];  
        
            $kewajiban=$komponen_biaya[$idkelas]['baru'];
            $pembayaran=$this->DB->getSumRowsOfTable('dibayarkan',"v_transaksi WHERE no_formulir='$no_formulir' AND tahun=$ta AND idsmt=2")+$biaya_pendaftaran;
            $sudahbayar[2]['sudahbayar']=$pembayaran;
            $sudahbayar[2]['belumbayar']=$kewajiban-$pembayaran;
        }else{
            $kewajiban_ganjil=$komponen_biaya[$idkelas]['lama'];
            $pembayaran_ganjil=$this->DB->getSumRowsOfTable('dibayarkan',"v_transaksi WHERE no_formulir='$no_formulir' AND tahun=$ta AND idsmt=1");
            $sudahbayar[1]['sudahbayar']=$pembayaran_ganjil;
            $sudahbayar[1]['belumbayar']=$kewajiban_ganjil-$pembayaran_ganjil;
            
            $kewajiban_genap=$komponen_biaya[$idkelas]['lama'];
            $pembayaran_genap=$this->DB->getSumRowsOfTable('dibayarkan',"v_transaksi WHERE no_formulir='$no_formulir' AND tahun=$ta AND idsmt=2");
            $sudahbayar[2]['sudahbayar']=$pembayaran_genap;
            $sudahbayar[2]['belumbayar']=$kewajiban_genap-$pembayaran_genap;
        }
        return $sudahbayar;
    }
    /**
     * digunakan untuk mendapatkan total biaya mahasiswa
     * @param $status baru atau lama
     * @return jumlah biaya mahasiswa
     */
    public function getTotalBiayaMhs ($status='baru') {        
		$tahun_masuk=$this->Finance->getDataMHS('tahun_masuk');
		$kelas=$this->Finance->getDataMHS('idkelas');
        switch ($status) {
            case 'lama' :
                $str = "SELECT SUM(biaya) AS jumlah FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND tahun=$tahun_masuk AND idkelas='$kelas' AND periode_pembayaran='semesteran'";
            break;
            case 'baru' :
                if ($this->Finance->getDataMhs('perpanjang')==true) {
                    $str = "SELECT SUM(biaya) AS jumlah FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND  tahun=$tahun_masuk AND idkelas='$kelas' AND periode_pembayaran!='none'";
                }else {
                    $str = "SELECT SUM(biaya) AS jumlah FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND  tahun=$tahun_masuk AND idkelas='$kelas' AND periode_pembayaran!='none'";								
                }		
            break;
            case 'sp' :
                $str = "SELECT biaya AS jumlah FROM kombi_per_ta WHERE tahun=$tahun_masuk AND idkelas='$kelas' AND idkombi=14";
            break;
        }		
		$this->DB->setFieldTable(array('jumlah'));
		$r=$this->DB->getRecord($str);	
        
		return $r[1]['jumlah'];
	}
}
?>