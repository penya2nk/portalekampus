<?php
prado::using ('Application.MainPageM');
class CPengampuLain extends MainPageM {	
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
            $this->hiddenid->Value=$id;
            $this->Demik->getInfoMatkul($id,'penyelenggaraan');            
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
    public function itemBound ($sender,$param) {
		$item=$param->item;
		if ($item->itemType==='Item' || $item->itemType === 'AlternatingItem') {
			if ($item->DataItem['iddosen_p']==$item->DataItem['iddosen_pp']) {
				$item->btnEdit->Enabled=false;	
				$item->btnDelete->Enabled=false;
                $item->btnDelete->Attributes->OnClick='';
			}			
			
		}
	}
    public function addProcess ($sender,$param) {			
		$this->idProcess='add';		
        $id=$this->hiddenid->Value;
        $this->hiddenid->Value=$id;
        $this->Demik->getInfoMatkul($id,'penyelenggaraan');  		
        $str = "SELECT iddosen,CONCAT(gelar_depan,' ',nama_dosen,gelar_belakang) AS nama_dosen,nidn FROM dosen WHERE iddosen NOT IN (SELECT iddosen FROM pengampu_penyelenggaraan WHERE idpenyelenggaraan=$id)";
        $this->DB->setFieldTable(array('iddosen','nidn','nama_dosen'));
		$r=$this->DB->getRecord($str);
        
        $DataDosen=array('none'=>' ');	                
        while (list($k,$v)=each($r)) {           
            $DataDosen[$v['iddosen']]=$v['nama_dosen']. ' ['.$v['nidn'].']';           			
        }
        $this->cmbAddDaftarDosen->DataSource=$DataDosen;
		$this->cmbAddDaftarDosen->dataBind();
	}
	public function saveData($sender,$param) {
		if ($this->IsValid) {			
			$iddosen=$this->cmbAddDaftarDosen->Text;
			$idpenyelenggaraan=$this->hiddenid->Value;		
			$str = "INSERT INTO pengampu_penyelenggaraan (idpengampu_penyelenggaraan,idpenyelenggaraan,iddosen) VALUES (NULL,$idpenyelenggaraan,$iddosen)";
			$this->DB->insertRecord($str);
            $_SESSION['currentPagePembagianKelas']['iddosen']='none';
			$this->redirect('perkuliahan.PengampuLain',true,array('id'=>$idpenyelenggaraan));
		}
	}	
	public function editRecord ($sender,$param) {		
        $idpp=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->idProcess='edit';
		$id=$this->hiddenid->Value;
        $this->hiddenid->Value=$id;
        $this->Demik->getInfoMatkul($id,'penyelenggaraan');  			
        
		$this->hiddenidpp->Value=$idpp;	
        $this->DB->setFieldTable(array('iddosen','nidn','nama_dosen'));
		$r=$this->DB->getRecord("SELECT pp.iddosen,CONCAT(d.gelar_depan,' ',d.nama_dosen,d.gelar_belakang) AS nama_dosen,d.nidn FROM dosen d,pengampu_penyelenggaraan pp WHERE d.iddosen=pp.iddosen AND pp.idpengampu_penyelenggaraan=$idpp");        
		
        $iddosen=$r[1]['iddosen'];        
        $str = "SELECT iddosen,CONCAT(gelar_depan,' ',nama_dosen,gelar_belakang) AS nama_dosen,nidn FROM dosen WHERE iddosen NOT IN (SELECT iddosen FROM pengampu_penyelenggaraan WHERE idpenyelenggaraan=$id)";
        $dd=$this->DB->getRecord($str);        
        $DataDosen=array($r[1]['iddosen']=>$r[1]['nama_dosen']. ' ['.$r[1]['nidn'].']');	                
        while (list($k,$v)=each($dd)) {           
            $DataDosen[$v['iddosen']]=$v['nama_dosen']. ' ['.$v['nidn'].']';           			
        }
        
        $this->cmbEditDaftarDosen->DataSource=$DataDosen;
        $this->cmbEditDaftarDosen->Text=$iddosen;
		$this->cmbEditDaftarDosen->dataBind();		
	}
    public function updateData($sender,$param) {							
        $idpenyelenggaraan=$this->hiddenid->Value;
		$id=$this->hiddenidpp->Value;
		$iddosen=$this->cmbEditDaftarDosen->Text;		
		$str = "UPDATE pengampu_penyelenggaraan SET iddosen=$iddosen WHERE idpengampu_penyelenggaraan=$id";
		$this->DB->updateRecord($str);
        $_SESSION['currentPagePembagianKelas']['iddosen']='none';
		$this->redirect('perkuliahan.PengampuLain',true,array('id'=>$idpenyelenggaraan));
	}
    public function deleteRecord ($sender,$param) {		
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $idpenyelenggaraan=$this->hiddenid->Value;		
		$this->DB->deleteRecord("pengampu_penyelenggaraan WHERE idpengampu_penyelenggaraan=$id");
        $_SESSION['currentPagePembagianKelas']['iddosen']='none';
		$this->redirect('perkuliahan.PengampuLain',true,array('id'=>$idpenyelenggaraan));
	}	
}

?>