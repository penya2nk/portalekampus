<?php
prado::using ('Application.MainPageMHS');
class CHome extends MainPageMHS {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showDashboard=true;       
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
            if (!isset($_SESSION['currentPageHome'])||$_SESSION['currentPageHome']['page_name']!='mh.Home') {                                                                                
                $this->Nilai->setDataMHS($this->Pengguna->getDataUser());
                $this->Nilai->getTranskripFromKRS();
                
                $_SESSION['currentPageHome']=array('page_name'=>'mh.Home',
                                                   'jumlahipk'=>$this->Nilai->getIPSAdaNilai(),                                                   
                                                   'jumlahmatakuliah'=>$this->Nilai->getTotalMatkul(),
                                                   'jumlahsks'=>$this->Nilai->getTotalSKS(),                                                                                                      
                                                   );
            }                       
		}        
        
	} 
    public function getDataMHS($idx) {		        
        return $this->Pengguna->getDataUser($idx);
    }
    public function refreshPage ($sender,$param) {
        unset($_SESSION['currentPageHome']);
        $this->redirect('Home',true);
    }
    
}
?>