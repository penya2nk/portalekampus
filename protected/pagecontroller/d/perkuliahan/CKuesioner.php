<?php
prado::using ('Application.MainPageD');
class CKuesioner extends MainPageD {
	public function onLoad($param) {		
		parent::onLoad($param);				
		$this->showSubMenuAkademikPerkuliahan=true;
        $this->showKuesioner=true;        
        $this->createObj('Kuesioner');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKuesioner'])||$_SESSION['currentPageKuesioner']['page_name']!='d.perkuliahan.Kuesioner') {
				$_SESSION['currentPageKuesioner']=array('page_name'=>'d.perkuliahan.Kuesioner','page_num'=>0,'search'=>false);
			}  
            $_SESSION['currentPageKuesioner']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

			$this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();           
				
            $this->populateData();
            $this->setInfoToolbar();
		}
	}	
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$_SESSION['ta'];		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$ta='T.A '.$this->DMaster->getNamaTA($_SESSION['ta']);		        
		$this->lblModulHeader->Text="Program Studi $ps $ta Semester $semester";
        
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;				
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}		
	public function populateData() {
        $iddosen=$this->Pengguna->getDataUser('iddosen');
		$ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];		

        $str="SELECT vpp.idpengampu_penyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen FROM v_pengampu_penyelenggaraan vpp WHERE EXISTS (SELECT 1 FROM kuesioner_jawaban WHERE idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) AND vpp.idsmt='$idsmt' AND vpp.tahun='$ta' AND vpp.kjur='$kjur' AND vpp.iddosen=$iddosen ORDER BY nmatkul ASC";
		$this->DB->setFieldTable (array('idpengampu_penyelenggaraan','idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nidn','nama_dosen','jumlahmhs'));			
		$r=$this->DB->getRecord($str);	
        $r=$this->DB->getRecord($str);	
        $result=array();        
        while (list($k,$v)=each($r)) {
            $idpengampu_penyelenggaraan=$v['idpengampu_penyelenggaraan'];                                    
            $str="SELECT n_kual FROM kuesioner_hasil WHERE idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";				
            $this->DB->setFieldTable (array('n_kual'));			
            $r2=$this->DB->getRecord($str);	
            if (isset($r2[1])) {
                $v['hasil']=$r2[1]['n_kual'];
                $v['commandparameter']='update';
            }else{
                $v['hasil']='N.A';
                $v['commandparameter']='insert';
            }             
            $result[$k]=$v;
        }      
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
	}
    public function hitungKuesioner ($sender,$param) {
        $idpengampu_penyelenggaraan = $this->getDataKeyField($sender,$this->RepeaterS); 
        $this->Kuesioner->hitungKuesioner($idpengampu_penyelenggaraan,$sender->CommandParameter);
        $this->redirect('perkuliahan.Kuesioner', true);
    }
}