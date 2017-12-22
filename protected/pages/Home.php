<?php
prado::using ('Application.MainPageF');
class Home extends MainPageF {
	public function onLoad($param) {		
		parent::onLoad($param);	         
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
            $this->populateData ();
		}
	}
    public function populateData () {        
        $str = "SELECT fp.idpost,fp.userid,fk.nama_kategori,fp.title,fp.content,fp.nama_user,fp.tipe,file_name,file_type,file_size,file_url,fp.date_added FROM pengumuman fp, forumkategori fk WHERE fp.idkategori=fk.idkategori AND parentpost=0 ORDER BY date_added DESC";       
		$this->DB->setFieldTable (array('idpost','userid','nama_kategori','title','content','nama_user','tipe','file_name','file_type','file_size','file_url','date_added'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpost=$v['idpost'];           
            $v['jumlahcomment']=$this->DB->getCountRowsOfTable("pengumuman WHERE parentpost=$idpost",'idpost');
            $v['tanggal_post']=$this->page->TGL->tanggal('l, d F Y H:i',$v['date_added']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();        
    }    
    public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
            $bool=false;
            if ($item->DataItem['file_size']>0) {                
                $bool=true;
            }
            $item->literalattachment->Visible=$bool;
        }
    }
}