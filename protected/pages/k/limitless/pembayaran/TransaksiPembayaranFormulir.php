<?php
prado::using ('Application.MainController');
prado::using ('Application.pagecontroller.k.pembayaran.CTransaksiPembayaranFormulir');
class TransaksiPembayaranFormulir Extends CTransaksiPembayaranFormulir {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    public function itemCreated($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {   
            $item->ColumnJumlahBayar->TextBox->CssClass='form-control';                                   
            $item->ColumnJumlahBayar->TextBox->Width='150px'; 
            $item->ColumnJumlahBayar->TextBox->Attributes->OnKeyUp="formatangka(this,false)";
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')  {
            CTransaksiPembayaranFormulir::$TotalKomponenBiaya+=$item->DataItem['biaya'];
            CTransaksiPembayaranFormulir::$TotalJumlahBayar+=$item->DataItem['jumlah_bayar'];
        } 
        
    }
}