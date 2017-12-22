<?php
prado::using ('Application.MainPageM');
class CChangePenyelenggaraan extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showPenyelenggaraan=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageChangePenyelenggaraan'])||$_SESSION['currentPageChangePenyelenggaraan']['page_name']!='m.perkuliahan.ChangePenyelenggaraan') {                
				$_SESSION['currentPageChangePenyelenggaraan']=array('page_name'=>'m.perkuliahan.ChangePenyelenggaraan','page_num'=>0,'search'=>false,'idkur'=>$this->Demik->getIDKurikulum($_SESSION['kjur']));												
			}
            $_SESSION['currentPageChangePenyelenggaraan']['search']=false;            
                        
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();	
            
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTA->DataSource=$ta;					
			$this->tbCmbTA->Text=$_SESSION['ta'];						
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
           
            $kjur=$_SESSION['kjur'];        
            $listkurikulum=$this->getListKurikulum ($kjur);   
            $this->cmbFilterKurikulum->DataSource=$this->Demik->removeIdFromArray($listkurikulum,'none');
            $this->cmbFilterKurikulum->Text=$_SESSION['currentPageChangePenyelenggaraan']['idkur'];
            $this->cmbFilterKurikulum->dataBind();
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
			$this->populateData();		
		}			
	}
    public function getListKurikulum ($kjur=null) {		
		$str = $kjur===null?'':" WHERE kjur=$kjur";
		$str = "SELECT idkur,kjur,ta FROM kurikulum$str ORDER BY ta DESC";
		$this->DB->setFieldTable(array('idkur','kjur','ta'));	
		$r=$this->DB->getRecord($str);
		$result=array();
		$result['none']=' ';
		while (list($k,$v) =each ($r)) {
            $result[$v['idkur']]=$_SESSION['daftar_jurusan'][$v['kjur']]." Kurikulum Tahun ".$v['ta'];
		}	
		return $result;
	}
    public function changeTbTA ($sender,$param) {
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageChangePenyelenggaraan']['search']);
        
	}	
	public function changeTbSemester ($sender,$param) {
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData($_SESSION['currentPageChangePenyelenggaraan']['search']);
	}	
    public function changeTbPs ($sender,$param) {		
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $listkurikulum=$this->Demik->removeIdFromArray($this->getListKurikulum($_SESSION['kjur']));     
        $_SESSION['currentPageChangePenyelenggaraan']['idkur']=$this->Demik->getIDKurikulum($_SESSION['kjur']);
        $this->cmbFilterKurikulum->DataSource=$listkurikulum;
        $this->cmbFilterKurikulum->Text=$_SESSION['currentPageChangePenyelenggaraan']['idkur'];
        $this->cmbFilterKurikulum->DataBind();
        $this->populateData();
	}
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function filterRecord ($sender,$param) {
		$_SESSION['currentPageChangePenyelenggaraan']['idkur']=$this->cmbFilterKurikulum->Text;
        $this->populateData();
	}
	public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$_SESSION['currentPageChangePenyelenggaraan']['idkur'];
        
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur ORDER BY semester ASC,kmatkul ASC";
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen'));			
        $r= $this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($v['idpenyelenggaraan']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}	
    public function changeKurMatkul($sender,$param) {
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$_SESSION['currentPageChangePenyelenggaraan']['idkur'];
        
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur ORDER BY semester ASC,kmatkul ASC";
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen'));			
        $r= $this->DB->getRecord($str);        
        $newidkur=$this->Demik->getIDKurikulum($kjur);
        try {
            $this->DB->query('BEGIN');
            while (list($k,$v)=each($r)) {
                $idpenyelenggaraan=$v['idpenyelenggaraan'];
                $old_kmatkul=$v['kmatkul'];
                $nmatkul=$v['nmatkul'];
                $new_kmatkul="{$newidkur}_".$this->Demik->getKMatkul($old_kmatkul);
                $str = "UPDATE penyelenggaraan SET kmatkul='$new_kmatkul' WHERE idpenyelenggaraan=$idpenyelenggaraan";
                $this->DB->updateRecord($str);                
            }
            $this->DB->query('COMMIT');
            $this->redirect('perkuliahan.ChangePenyelenggaraan',true);   
        } catch (Exception $ex) {            
            $this->DB->query('ROLLBACK');
            $this->lblContentMessageError->Text="Matakuliah $nmatkul dengan kode $old_kmatkul belum terdaftar di Kurikulum saat ini. Mohon untuk ditambahkan di Data Master -> Matakuliah";
            $this->modalMessageError->show(); 
        }
    }
	
}