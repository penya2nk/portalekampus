<?php
prado::using ('Application.MainPageK');
class CDetailPembayaranMahasiswaBaru Extends MainPageK {
    public static $TotalSudahBayar=0;
    public static $KewajibanMahasiswa=0;
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showPembayaran=true;
        $this->showPembayaranMahasiswaBaru=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembayaranMahasiswaBaru'])||$_SESSION['currentPagePembayaranMahasiswaBaru']['page_name']!='k.pembayaran.PembayaranMahasiswaBaru') {
				$_SESSION['currentPagePembayaranMahasiswaBaru']=array('page_name'=>'k.pembayaran.PembayaranMahasiswaBaru','page_num'=>0,'search'=>false,'kelas'=>'none','tahun_masuk'=>$_SESSION['tahun_masuk'],'semester_masuk'=>$_SESSION['semester'],'DataMHS'=>array());												
			}        
            try {
                $no_formulir=addslashes($this->request['id']);
                $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.kjur1,fp.kjur2,idkelas,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk FROM formulir_pendaftaran fp,profiles_mahasiswa pm WHERE fp.no_formulir=pm.no_formulir AND fp.no_formulir='$no_formulir'";
                $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','kjur1','kjur2','idkelas','tahun_masuk','semester_masuk'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                
                    throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
                }
                $datamhs=$r[1];     
                $this->Finance->setDataMHS($datamhs);
                if (!$spmb=$this->Finance->isLulusSPMB(true)) {
                    throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak lulus dalam SPMB.");		
                }
                $datamhs['kjur']=$spmb['kjur'];
                $this->Finance->setDataMHS($datamhs);
                $data=$this->Finance->getTresholdPembayaran($datamhs['tahun_masuk'],$datamhs['semester_masuk'],true);						
                $datamhs['total_bayar']=$data['total_biaya'];
                $datamhs['ambang_pembayaran']=$data['ambang_pembayaran'];	
                $datamhs['no_transaksi']=isset($_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']['no_transaksi']) ? $_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']['no_transaksi'] : 'none';
                $_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']=$datamhs;                
                CDetailPembayaranMahasiswaBaru::$KewajibanMahasiswa=$this->Finance->getTotalBiayaMhsPeriodePembayaran ();
                $this->populateTransaksi();
            }catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }      
		}	
	}
    public function populateTransaksi() {
        $datamhs=$_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS'];
        $no_formulir=$datamhs['no_formulir'];
        $tahun=$datamhs['tahun_masuk'];
        $idsmt=$datamhs['semester_masuk'];
        $kjur=$datamhs['kjur'];
        $str = "SELECT no_transaksi,no_faktur,tanggal,commited,date_added FROM transaksi WHERE tahun='$tahun' AND idsmt='$idsmt' AND no_formulir='$no_formulir' AND kjur='$kjur'";
        $this->DB->setFieldTable(array('no_transaksi','no_faktur','tanggal','commited','date_added'));
        $r=$this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {
            $no_transaksi=$v['no_transaksi'];
            $v['total']=$this->DB->getSumRowsOfTable('dibayarkan',"transaksi_detail WHERE no_transaksi=$no_transaksi");
            $result[$k]=$v;
        }
        $this->ListTransactionRepeater->DataSource=$result;
        $this->ListTransactionRepeater->dataBind();        
    }
	public function dataBoundListTransactionRepeater ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {			
			if ($item->DataItem['commited']) {
                $item->btnDeleteFromRepeater->Enabled=false;				
                $item->btnEditFromRepeater->Enabled=false;				
			}else{
                $item->btnDeleteFromRepeater->Attributes->onclick="if(!confirm('Apakah Anda ingin menghapus Transaksi ini?')) return false;";
            }
            CDetailPembayaranMahasiswaBaru::$TotalSudahBayar+=$item->DataItem['total'];
		}
	}	
	public function addTransaction ($sender,$param) {
        $datamhs=$_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS'];    
        if ($datamhs['no_transaksi'] == 'none') {
            $no_transaksi=$this->DB->getMaxOfRecord('no_transaksi','transaksi')+1;	
            $no_formulir=$datamhs['no_formulir'];
            $ta=$datamhs['tahun_masuk'];                        
            $idsmt=$datamhs['semester_masuk'];     
            $ps=$datamhs['kjur'];                
            $idkelas=$datamhs['idkelas'];
            $userid=$this->Pengguna->getDataUser('userid');

            $this->DB->query ('BEGIN');
            $str = "INSERT INTO transaksi (no_transaksi,no_faktur,kjur,tahun,idsmt,idkelas,no_formulir,tanggal,userid,date_added,date_modified) VALUES ($no_transaksi,'$no_transaksi','$ps','$ta','$idsmt','$idkelas','$no_formulir',NOW(),'$userid',NOW(),NOW())";					
            if ($this->DB->insertRecord($str)) {
                $str = "INSERT INTO transaksi_detail (idtransaksi_detail,no_transaksi,idkombi,dibayarkan) SELECT NULL,$no_transaksi,k.idkombi,0 FROM kombi_per_ta kpt,kombi k WHERE  k.idkombi=kpt.idkombi AND tahun=$ta AND kpt.idkelas='$idkelas' AND (periode_pembayaran='sekali' OR periode_pembayaran='semesteran') ORDER BY periode_pembayaran,nama_kombi ASC";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']['no_transaksi']=$no_transaksi;            
                $this->redirect('pembayaran.TransaksiPembayaranMahasiswaBaru',true);        
            }else{
                $this->DB->query('ROLLBACK');
            }            
        }else{            
            $this->redirect('pembayaran.TransaksiPembayaranMahasiswaBaru',true); 
        }
	}
    public function editRecord ($sender,$param) {	        
        $datamhs=$_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS'];    
        if ($datamhs['no_transaksi'] == 'none') {
            $no_transaksi=$this->getDataKeyField($sender,$this->ListTransactionRepeater);		
            $_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']['no_transaksi']=$no_transaksi;
        }	
		$this->redirect('pembayaran.TransaksiPembayaranMahasiswaBaru',true);
	}	
	public function deleteRecord ($sender,$param) {	
        $datamhs=$_SESSION['currentPagePembayaranMahasiswaBaru']['DataMHS']; 
        $no_formulir=$datamhs['no_formulir'];
		$no_transaksi=$this->getDataKeyField($sender,$this->ListTransactionRepeater);		
		$this->DB->deleteRecord("transaksi WHERE no_transaksi='$no_transaksi'");		
		$this->redirect('pembayaran.DetailPembayaranMahasiswaBaru',true,array('id'=>$no_formulir));
	}		
}
