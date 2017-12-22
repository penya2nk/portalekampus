<?php
prado::using ('Application.MainPageDW');
class CPengampuLain extends MainPageDW {	
	public function onLoad($param) {
		parent::onLoad($param);	
		$this->showSubMenuAkademikPerkuliahan=true;
        $this->showPenyelenggaraan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {				
            if (!isset($_SESSION['currentPagePengampuLain'])||$_SESSION['currentPagePengampuLain']['page_name']!='m.perkuliahan.PengampuLain') {
				$_SESSION['currentPagePengampuLain']=array('page_name'=>'m.perkuliahan.PengampuLain','page_num'=>0,'search'=>false);
			}
			$this->populateData();
		}			
	}
	public function populateData () {		
		try {
            $id=addslashes($this->request['id']);         
            $iddosen_wali=$this->iddosen_wali;
            $this->Demik->getInfoMatkul($id,'penyelenggaraan');  
            $this->Demik->InfoMatkul['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($id," AND iddosen_wali=$iddosen_wali");
            if (!isset($this->Demik->InfoMatkul['idpenyelenggaraan'])) {                                                
                throw new Exception ("Kode penyelenggaraan dengan id ($id) tidak terdaftar.");		
            }               
            $str = "SELECT pp.idpengampu_penyelenggaraan,p.iddosen AS iddosen_p,pp.iddosen AS iddosen_pp,d.nidn,CONCAT(d.gelar_depan,' ',d.nama_dosen,d.gelar_belakang) AS nama_dosen FROM pengampu_penyelenggaraan pp,penyelenggaraan p,dosen d WHERE pp.idpenyelenggaraan=p.idpenyelenggaraan AND pp.iddosen=d.iddosen AND pp.idpenyelenggaraan=$id";
            $this->DB->setFieldTable (array('idpengampu_penyelenggaraan','iddosen_p','iddosen_pp','nidn','nama_dosen'));
            $r=$this->DB->getRecord($str);	
            
            $this->RepeaterS->DataSource=$r;
            $this->RepeaterS->dataBind();
        }catch (Exception $e) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$e->getMessage();			
        }	
	}    
   
}