<?php
prado::using ('Application.MainPageK');
class CDetailPembayaranFormulir Extends MainPageK {
    public static $TotalSudahBayar=0;
    public static $KewajibanMahasiswa=0;
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showMenuPembayaran=true;
        $this->showPembayaranFormulir=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePembayaranFormulir'])||$_SESSION['currentPagePembayaranFormulir']['page_name']!='k.pembayaran.PembayaranFormulir') {
				$_SESSION['currentPagePembayaranFormulir']=array('page_name'=>'k.pembayaran.PembayaranFormulir','page_num'=>0,'search'=>false,'kelas'=>'none','semester_masuk'=>1,'DataMHS'=>array());												
			}        
            try {
                $no_formulir=addslashes($this->request['id']);
                $str = "SELECT no_formulir,idkelas,tahun_masuk,1 AS semester_masuk FROM pin WHERE no_formulir='$no_formulir'";
                $this->DB->setFieldTable(array('no_formulir','idkelas','tahun_masuk','semester_masuk'));
                $r=$this->DB->getRecord($str);
                if (!isset($r[1])) {                                
                    throw new Exception ("Calon Mahasiswa dengan Nomor Formulir ($no_formulir) tidak terdaftar di Database, silahkan ganti dengan yang lain.");		
                }
                $datamhs=$r[1];                
                $this->Finance->setDataMHS($datamhs);
                
                $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                $this->Finance->setDataMHS($datamhs);                
                $datamhs['no_transaksi']=isset($_SESSION['currentPagePembayaranFormulir']['DataMHS']['no_transaksi']) ? $_SESSION['currentPagePembayaranFormulir']['DataMHS']['no_transaksi'] : 'none';
                $_SESSION['currentPagePembayaranFormulir']['DataMHS']=$datamhs;                
                CDetailPembayaranFormulir::$KewajibanMahasiswa=$this->Finance->getBiayaPendaftaran ($datamhs['tahun_masuk'],$datamhs['semester_masuk'],$datamhs['idkelas']);
                $this->populateTransaksi();
            }catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }      
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Finance->getDataMHS($idx);
    }
    public function populateTransaksi() {
        $datamhs=$_SESSION['currentPagePembayaranFormulir']['DataMHS'];
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
            CDetailPembayaranFormulir::$TotalSudahBayar+=$item->DataItem['total'];
		}
	}	
	public function addTransaction ($sender,$param) {
        $datamhs=$_SESSION['currentPagePembayaranFormulir']['DataMHS'];        
        if ($datamhs['no_transaksi'] == 'none') {
            $no_formulir=$datamhs['no_formulir'];
            $ta=$datamhs['tahun_masuk'];                        
            $idsmt=$datamhs['semester_masuk'];
            $this->Finance->setDataMHS($datamhs);
            if ($this->Finance->getLunasPembayaranFormulir()) {
                $this->lblContentMessageError->Text='Tidak bisa menambah Transaksi baru karena sudah lunas.';
                $this->modalMessageError->show();
            }elseif ($this->DB->checkRecordIsExist('no_formulir','transaksi',$no_formulir," AND tahun='$ta' AND idsmt='$idsmt' AND commited=0")) {
                $this->lblContentMessageError->Text='Tidak bisa menambah Transaksi baru karena ada transaksi yang belum di Commit.';
                $this->modalMessageError->show();
            }else{
                $no_transaksi=$this->DB->getMaxOfRecord('no_transaksi','transaksi')+1;
                $no_faktur=$ta.$no_transaksi;
                $ps=$datamhs['kjur'];                
                $idkelas=$datamhs['idkelas'];
                $userid=$this->Pengguna->getDataUser('userid');

                $this->DB->query ('BEGIN');
                $str = "INSERT INTO transaksi (no_transaksi,no_faktur,kjur,tahun,idsmt,idkelas,no_formulir,tanggal,userid,date_added,date_modified) VALUES ($no_transaksi,'$no_faktur','$ps','$ta','$idsmt','$idkelas','$no_formulir',NOW(),'$userid',NOW(),NOW())";					
                if ($this->DB->insertRecord($str)) {
                    $str = "SELECT idkombi,SUM(dibayarkan) AS sudah_dibayar FROM v_transaksi WHERE no_formulir=$no_formulir AND tahun=$ta AND idsmt=$idsmt AND commited=1 GROUP BY idkombi ORDER BY idkombi+1 ASC";
                    $this->DB->setFieldTable(array('idkombi','sudah_dibayar'));
                    $d=$this->DB->getRecord($str);

                    $sudah_dibayarkan=array();
                    while (list($o,$p)=each($d)) {            
                        $sudah_dibayarkan[$p['idkombi']]=$p['sudah_dibayar'];
                    }
                    $str = "SELECT k.idkombi,kpt.biaya FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND tahun=$ta AND idsmt=$idsmt AND kpt.idkelas='$idkelas' AND k.idkombi=1 ORDER BY periode_pembayaran,nama_kombi ASC";
                    $this->DB->setFieldTable(array('idkombi','biaya'));
                    $r=$this->DB->getRecord($str);

                    while (list($k,$v)=each($r)) {
                        $biaya=$v['biaya'];
                        $idkombi=$v['idkombi'];
                        $sisa_bayar=$biaya-$sudah_dibayarkan[$idkombi];
                        $str = "INSERT INTO transaksi_detail (idtransaksi_detail,no_transaksi,idkombi,dibayarkan) VALUES(NULL,$no_transaksi,$idkombi,$sisa_bayar)";
                        $this->DB->insertRecord($str);
                    }
                    
                    $this->DB->query('COMMIT');
                    $_SESSION['currentPagePembayaranFormulir']['DataMHS']['no_transaksi']=$no_transaksi;            
                    $this->redirect('pembayaran.TransaksiPembayaranFormulir',true);        
                }else{
                    $this->DB->query('ROLLBACK');
                }           
            }
        }else{            
            $this->redirect('pembayaran.TransaksiPembayaranFormulir',true); 
        }
	}
    public function editRecord ($sender,$param) {	        
        $datamhs=$_SESSION['currentPagePembayaranFormulir']['DataMHS'];    
        if ($datamhs['no_transaksi'] == 'none') {
            $no_transaksi=$this->getDataKeyField($sender,$this->ListTransactionRepeater);		
            $_SESSION['currentPagePembayaranFormulir']['DataMHS']['no_transaksi']=$no_transaksi;
        }	
		$this->redirect('pembayaran.TransaksiPembayaranFormulir',true);
	}	
	public function deleteRecord ($sender,$param) {	
        $datamhs=$_SESSION['currentPagePembayaranFormulir']['DataMHS']; 
        $no_formulir=$datamhs['no_formulir'];
		$no_transaksi=$this->getDataKeyField($sender,$this->ListTransactionRepeater);		
		$this->DB->deleteRecord("transaksi WHERE no_transaksi='$no_transaksi'");		
		$this->redirect('pembayaran.DetailPembayaranFormulir',true,array('id'=>$no_formulir));
	}		
    public function closeDetail ($sender,$param) {
        unset($_SESSION['currentPagePembayaranFormulir']['DataMHS']);
        $this->redirect('pembayaran.PembayaranFormulir',true);
    }
}