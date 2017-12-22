<?php
prado::using ('Application.pagecontroller.k.pembayaran.CTransaksiPembayaranSemesterPendek');
class TransaksiPembayaranSemesterPendek Extends CTransaksiPembayaranSemesterPendek {		
	public function onLoad($param) {
		parent::onLoad($param);							
    }
    public function itemCreated($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {   
            $item->ColumnJumlahSKS->TextBox->CssClass='form-control';                                   
            $item->ColumnJumlahSKS->TextBox->Width='70px'; 
            $item->ColumnJumlahSKS->TextBox->Attributes->OnKeyUp="formatangka(this,false)";
            
            $item->EditColumn->UpdateButton->ClientSide->OnPreDispatch="Pace.stop();Pace.start();";                       
            $item->EditColumn->CancelButton->ClientSide->OnPreDispatch="Pace.stop();Pace.start();";                                   
            $item->DeleteColumn->Button->ClientSide->OnPreDispatch="Pace.stop();Pace.start();";
               
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')  {            
            if ($item->DataItem['sudah_dibayar'] >= $item->DataItem['biaya']) {
                $item->EditColumn->Enabled=false;
                $item->DeleteColumn->Enabled=false;
            }
            $item->EditColumn->EditButton->ClientSide->OnPreDispatch="Pace.stop();Pace.start();";
            $item->EditColumn->EditButton->CssClass='btn btn-icon btn-xs';
            $item->EditColumn->EditButton->Attributes->Title='Ubah Jumlah Bayar';

            $item->DeleteColumn->Button->ClientSide->OnPreDispatch="Pace.stop();Pace.start();";                
            $item->DeleteColumn->Button->CssClass="btn btn-icon btn-xs";                
            $item->DeleteColumn->Button->Attributes->Title='Reset Jumlah Bayar';
        } 
    }
}