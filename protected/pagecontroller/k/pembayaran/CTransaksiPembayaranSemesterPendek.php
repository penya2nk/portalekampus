<?php
prado::using ('Application.MainPageK');
class CTransaksiPembayaranSemesterPendek Extends MainPageK {
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showMenuPembayaran=true;
        $this->showPembayaranSemesterPendek=true;                
        $this->createObj('Finance');
		if (!$this->IsPostBack&&!$this->IsCallBack) {            
            try {                
                $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];                                
                if (!isset($datamhs['no_transaksi']) || $datamhs['no_transaksi'] == 'none') {              
                    throw new Exception ("Tidak ada data No. Transaksi di Sesi ini");		
                }  
                $this->Finance->setDataMHS($datamhs);
                $no_transaksi=$datamhs['no_transaksi'];
                $str = "SELECT no_faktur,tanggal FROM transaksi WHERE no_transaksi=$no_transaksi";
                $this->DB->setFieldTable(array('no_faktur','tanggal'));
                $d=$this->DB->getRecord($str);
                $this->hiddennofaktur->Value=$d[1]['no_faktur'];
                $this->txtAddNomorFaktur->Text=$d[1]['no_faktur'];
                $this->cmbAddTanggalFaktur->Text=$this->TGL->tanggal('d-m-Y',$d[1]['tanggal']);
                $this->populateData();
            }catch (Exception $ex) {
                $this->idProcess='view';	
                $this->errorMessage->Text=$ex->getMessage();
            }      
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Finance->getDataMHS($idx);
    }
    public function populateData () {
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];        
        $no_transaksi=$datamhs['no_transaksi'];
        $tahun_masuk=$datamhs['tahun_masuk'];   
        $kelas=$datamhs['idkelas'];                
        
        
        $str = "SELECT td.idkombi,td.dibayarkan,td.jumlah_sks,t.commited FROM transaksi t,transaksi_detail td WHERE t.no_transaksi=td.no_transaksi AND td.no_transaksi=$no_transaksi ORDER BY td.idkombi+1 ASC";
        $this->DB->setFieldTable(array('idkombi','dibayarkan','jumlah_sks','commited'));
        $k=$this->DB->getRecord($str);
        
        $transaksi=array();
        while (list($m,$n)=each($k)) {              
            $transaksi[$n['idkombi']]=array('dibayarkan'=>$n['dibayarkan'],'jumlah_sks'=>$n['jumlah_sks']);
        }
        
        $str = "SELECT k.idkombi,k.nama_kombi,kpt.biaya FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND tahun=$tahun_masuk AND idsmt=1 AND kpt.idkelas='$kelas' AND kpt.idkombi=14 ORDER BY periode_pembayaran,nama_kombi ASC";
        $this->DB->setFieldTable(array('idkombi','nama_kombi','biaya'));
        $r=$this->DB->getRecord($str);
        
        while (list($k,$v)=each($r)) {
            $biaya=$v['biaya'];
            $idkombi=$v['idkombi'];            
            $v['nama_kombi']=  strtoupper($v['nama_kombi']); 
            $v['biaya_alias']=$this->Finance->toRupiah($biaya);
            $v['jumlah_sks']=$transaksi[$idkombi]['jumlah_sks'];
            $jumlah_bayar=$biaya*$v['jumlah_sks'];
            $v['jumlah_bayar']=$this->Finance->toRupiah($jumlah_bayar);
            $v['dibayarkan']=$v['commited'] == true ? $this->Finance->toRupiah($transaksi[$idkombi]['dibayarkan']) :0;
            $result[$k]=$v;
        }		
        $this->GridS->DataSource=$result;
		$this->GridS->dataBind();
        
    }
	public function editItem($sender,$param) {                   
        $this->GridS->EditItemIndex=$param->Item->ItemIndex;
        $this->populateData ();        
    }
    public function cancelItem($sender,$param) {                
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();        
    }		
    public function deleteItem($sender,$param) {                
        $id=$this->GridS->DataKeys[$param->Item->ItemIndex]; 
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];
        $no_transaksi=$datamhs['no_transaksi'];
        $this->DB->updateRecord("UPDATE transaksi_detail SET dibayarkan=0 WHERE idkombi=14 AND no_transaksi=$no_transaksi");
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();
    }  
    public function saveItem($sender,$param) {                        
        $item=$param->Item;
        $id=$this->GridS->DataKeys[$item->ItemIndex];  
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];
        $no_transaksi=$datamhs['no_transaksi'];
        $tahun_masuk=$datamhs['tahun_masuk'];    
        $kelas=$datamhs['idkelas'];       
       
        $str = "SELECT biaya FROM kombi_per_ta kpt,kombi k WHERE k.idkombi=kpt.idkombi AND tahun=$tahun_masuk AND idsmt=1 AND kpt.idkelas='$kelas' AND kpt.idkombi=$id";
        $this->DB->setFieldTable(array('biaya'));
        $r=$this->DB->getRecord($str);
        $biaya=$r[1]['biaya'];
        
        $jumlah_sks=$this->Finance->toInteger(addslashes($item->ColumnJumlahSKS->TextBox->Text));                         
        $jumlah_bayar=$jumlah_sks*$biaya;
        
        $this->DB->query ('BEGIN');
        $str = "UPDATE transaksi_detail SET dibayarkan='$jumlah_bayar',jumlah_sks=$jumlah_sks WHERE no_transaksi=$no_transaksi AND idkombi=$id";
        if ($this->DB->updateRecord($str) ) {
            $str = "UPDATE transaksi SET jumlah_sks=$jumlah_sks WHERE no_transaksi=$no_transaksi";
            $this->DB->updateRecord($str);
            
            $this->DB->query('COMMIT');    
        }else{
            $this->DB->query('ROLLBACK');
        }       
       
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();
    }
	public function checkNomorFaktur ($sender,$param) {
		$this->idProcess=$sender->getId()=='addNomorFaktur'?'add':'edit';
        $no_faktur=$param->Value;		
        if ($no_faktur != '') {
            try {
                if ($this->hiddennofaktur->Value != $no_faktur) {
                    if ($this->DB->checkRecordIsExist('no_faktur','transaksi',$no_faktur)) {                                
                        throw new Exception ("Nomor Faktur dari ($no_faktur) sudah tidak tersedia silahkan ganti dengan yang lain.");		
                    }
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function saveData ($sender,$param) {
		if ($this->Page->isValid) {	
            $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];
            $no_transaksi=$datamhs['no_transaksi'];
            $nim=$datamhs['nim'];
            
            $no_faktur=addslashes($this->txtAddNomorFaktur->Text);            
            $tanggal=date('Y-m-d',$this->cmbAddTanggalFaktur->TimeStamp);
            
            $str = "UPDATE transaksi SET no_faktur='$no_faktur',tanggal='$tanggal',date_modified=NOW() WHERE no_transaksi=$no_transaksi";
            $this->DB->updateRecord($str);
            unset($_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']);
            $this->redirect('pembayaran.DetailPembayaranSemesterPendek',true,array('id'=>$nim));
        }
    }
    public function commitData ($sender,$param) {
		if ($this->Page->isValid) {	
            $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];
            $no_transaksi=$datamhs['no_transaksi'];
            $nim=$datamhs['nim'];
            $ta=$datamhs['ta'];
            $idsmt=$_SESSION['currentPagePembayaranSemesterPendek']['semester'];
            $kelas=$datamhs['idkelas'];
            $k_status=$datamhs['k_status'];
            $no_faktur=addslashes($this->txtAddNomorFaktur->Text);            
            $tanggal=date('Y-m-d',$this->cmbAddTanggalFaktur->TimeStamp);
            
            $this->DB->query('BEGIN');
            $str = "UPDATE transaksi SET no_faktur='$no_faktur',tanggal='$tanggal',commited=1,date_modified=NOW() WHERE no_transaksi=$no_transaksi";
            $this->DB->updateRecord($str);
            
            $datadulang=$this->Finance->getDataDulang($ta,$idsmt);
            if (!isset($datadulang['iddulang'])) {
                $this->Finance->setDataMHS($datamhs);
                $bool=$this->Finance->getTresholdPembayaran($ta,$idsmt);						                                
                if ($bool) {
                    $tasmt=$ta.$idsmt;
                    $str = "INSERT INTO dulang (iddulang,nim,tahun,idsmt,tasmt,tanggal,idkelas,status_sebelumnya,k_status) VALUES (NULL,'$nim','$ta','$idsmt','$tasmt','$tanggal','$kelas','$k_status','A')";
                    $this->DB->insertRecord($str);
                    
                    $str = "UPDATE register_mahasiswa SET k_status='A' WHERE nim='$nim'";
                    $this->DB->updateRecord($str);
                }
                               
            }
            $this->DB->query('COMMIT');
            unset($_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']);
            $this->redirect('pembayaran.DetailPembayaranSemesterPendek',true,array('id'=>$nim));
        }
    }
    public function closeTransaction ($sender,$param) {
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS'];            
        $nim=$datamhs['nim'];
        unset($_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']);
        $this->redirect('pembayaran.DetailPembayaranSemesterPendek',true,array('id'=>$nim));
    }
    public function cancelTrx ($sender,$param) {	
        $datamhs=$_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']; 
        $nim=$datamhs['nim'];
		$no_transaksi=$datamhs['no_transaksi'];		
		$this->DB->deleteRecord("transaksi WHERE no_transaksi='$no_transaksi'");
        unset($_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']);
		$this->redirect('pembayaran.DetailPembayaranSemesterPendek',true,array('id'=>$nim));
	}
    public function closeDetail ($sender,$param) {
        unset($_SESSION['currentPagePembayaranSemesterPendek']['DataMHS']);
        $this->redirect('pembayaran.PembayaranSemesterPendek',true);
    }
}