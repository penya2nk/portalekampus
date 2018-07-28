<?php
prado::using('Application.api.BaseWS');
class Finance extends BaseWS {	
	/**
	* digunakan untuk memperoleh jumlah tagihan berdasarkan no_transaksi
	*/
	public function getTotalTagihanByNoTransaksi ($no_transaksi) {
		return $this->DB->getSumRowsOfTable('dibayarkan',"transaksi_detail WHERE no_transaksi='$no_transaksi'");
	}
}