<?php
prado::using ('Application.MainPageM');
class CKuesionerSalin extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);		
        $this->showSubMenuAkademikPerkuliahan=true;
        $this->showKuesioner=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKuesioner'])||$_SESSION['currentPageKuesioner']['page_name']!='m.perkuliahan.Kuesioner') {                
				$_SESSION['currentPageKuesioner']=array('page_name'=>'m.perkuliahan.Kuesioner','page_num'=>0,'search'=>false,'DaftarDosen'=>array());												
			}
            $_SESSION['currentPageKuesioner']['search']=false; 
            $this->lblModulHeader->Text=$this->getInfoToolbar();
            
            $ta=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->cmbTA->DataSource=$ta;					
			$this->cmbTA->Text=$_SESSION['ta'];						
			$this->cmbTA->dataBind();
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->cmbSemester->DataSource=$semester;
			$this->cmbSemester->Text=$_SESSION['semester'];
			$this->cmbSemester->dataBind();           
		}			
	}    
    public function getInfoToolbar() {                
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="TA $ta Semester $semester";
		return $text;
	} 
    public function checkTaSemester ($sender,$param) {                
        $ta=$param->Value;        
        if ($_SESSION['ta'] == $ta && $_SESSION['semester'] == $this->cmbSemester->Text) {
            $sender->ErrorMessage="Tidak bisa menyalin dari T.A dan Semester KE T.A dan Semester yang sama.";
            $param->IsValid=false;
        }
    }
    public function salinKuesioner($sender,$param) {                
		if ($this->IsValid) {
            $ta_sekarang=$_SESSION['ta'];
            $semester_sekarang=$_SESSION['semester'];
                
            $ta=$this->cmbTA->Text;
            $semester=$this->cmbSemester->Text;
            
            $this->DB->query('BEGIN');  
            $this->DB->deleteRecord("kuesioner WHERE tahun=$ta_sekarang AND idsmt=$semester_sekarang");
            $str = "INSERT INTO kuesioner (old_idkuesioner,idsmt,tahun,idkelompok_pertanyaan,pertanyaan,orders,date_added,date_modified) SELECT idkuesioner,$semester_sekarang,$ta_sekarang,idkelompok_pertanyaan,pertanyaan,orders,date_added,date_modified FROM kuesioner WHERE tahun=$ta AND idsmt=$semester";
            if ($this->DB->insertRecord($str) ) {                
                $str = "SELECT idkuesioner,old_idkuesioner FROM kuesioner WHERE idsmt=$semester_sekarang AND tahun=$ta_sekarang";
                $this->DB->setFieldTable (array('idkuesioner','old_idkuesioner'));	        
                $r=$this->DB->getRecord($str);       
                while (list($k,$v)=each($r)) {
                    $idkuesioner=$v['idkuesioner'];
                    $old_idkuesioner=$v['old_idkuesioner'];
                    $str = "INSERT INTO kuesioner_indikator (idkuesioner,nilai_indikator,nama_indikator) SELECT $idkuesioner,nilai_indikator,nama_indikator FROM kuesioner_indikator WHERE idkuesioner=$old_idkuesioner";
                    $this->DB->insertRecord($str);               
                }
                $this->DB->query('COMMIT');
                $this->redirect('perkuliahan.KuesionerSalin', true);             
            }else{
                $this->DB->query('ROLLBACK');        
            }
        }
	}
	
}

?>