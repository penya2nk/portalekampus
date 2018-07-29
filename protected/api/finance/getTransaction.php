<?php
prado::using('Application.api.finance.Finance');
class getTransaction extends Finance {	
	public function getJsonContent() {
		$this->validate ();
		if ($this->payload['connection'] >= 1) {
			$data=$_POST;
			if (isset($data['no_transaksi']) && isset($data['tipe_transaksi'])) {
				$no_transaksi=addslashes($data['no_transaksi']);
				switch (addslashes($data['tipe_transaksi'])) {
					case 'common':
						$str = "SELECT no_transaksi,no_faktur,kjur,tahun,idsmt,idkelas,no_formulir,nim,commited,tanggal,date_added FROM transaksi WHERE no_transaksi='$no_transaksi'";
						$this->DB->setFieldTable(array('no_transaksi','no_faktur','kjur','tahun','idsmt','idkelas','no_formulir','nim','commited','tanggal','date_added'));		
						$r=$this->DB->getRecord($str);						
						if (isset($r[1])) {
							$this->payload['payload']=$r[1];
							$this->payload['payload']['totaltagihan']=$this->getTotalTagihanByNoTransaksi($no_transaksi);
							$this->payload['message']="Proses Login telah berhasil, transaksi dengan nomor ($no_transaksi) berhasil diperoleh !!!";
						}else{
							$this->payload['message']="Proses Login telah berhasil, namun transaksi dengan nomor ($no_transaksi) tidak ada di database !!!";
						}			
					break;
					case 'cuti' :
									
					break;
					default :
						$this->payload['message']="Proses Login telah berhasil, namun ada error yaitu tipe_transaksi tidak dikenal. hanya tersedia opsi (biasa | cuti) !!!";
				}
				
			}else {
				$this->payload['message']="Proses Login telah berhasil, namun ada error yaitu data POST no_transaksi atau POST tipe_transaksi tidak ada !!!";
			}
		}		
		return $this->payload;
	}
}