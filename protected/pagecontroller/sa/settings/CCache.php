<?php
prado::using ('Application.MainPageM');
class CCache extends MainPageM {    
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuSettingSistem=true;
		$this->showCache=true;                
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageCache'])||$_SESSION['currentPageCache']['page_name']!='sa.settings.Cache') {
				$_SESSION['currentPageCache']=array('page_name'=>'sa.settings.Cache','page_num'=>0);												
			}            
		}
	}    
    public function hapusCache ($sender,$param) {
        if ($this->Application->Cache) {
            $this->Application->Cache->flush();           
            $this->message->Text='<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><strong>Success!</strong> Cache cleared.</div>';            
        }
    }    
}