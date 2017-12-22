<?php
prado::using ('Application.MainPageM');
class CDetailKonversiMatakuliah extends MainPageM {	
    public static $TotalSKS=0;
	public static $TotalM=0;
    public $NilaiSemesterLalu;
    public $NilaiSemesterSekarang;
	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showKonversiMatakuliah=true;    
        $this->createObj('Nilai');
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailKonversiMatakuliah'])||$_SESSION['currentPageDetailKonversiMatakuliah']['page_name']!='m.spmb.DetailKonversiMatakuliah') {
				$_SESSION['currentPageDetailKonversiMatakuliah']=array('page_name'=>'m.spmb.DetailKonversiMatakuliah','page_num'=>0,'search'=>false,'DataKonversi'=>array());												                                               
			}  
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
			$this->populateData();	
		}
	}
    public function getDataMHS($idx) {		        
        return $this->Nilai->getDataMHS($idx);
    }
	protected function populateData() {		
        try {
            $iddata_konversi=addslashes($this->request['id']);  
            
            $str = "SELECT dk.iddata_konversi,dk.nama,dk.alamat,dk.no_telp,dk.nim_asal,dk.kode_pt_asal,dk.nama_pt_asal,js.njenjang,dk.kode_ps_asal,dk.nama_ps_asal,dk.tahun,dk.kjur,dk.idkur,date_added FROM data_konversi2 dk,jenjang_studi js WHERE dk.kjenjang=js.kjenjang AND dk.iddata_konversi=$iddata_konversi";
            $this->DB->setFieldTable(array('iddata_konversi','nama','alamat','no_telp','nim_asal','kode_pt_asal','nama_pt_asal','njenjang','kode_ps_asal','nama_ps_asal','tahun','kjur','idkur','date_added'));
            $r = $this->DB->getRecord($str);			
            $dataView=$r[1];	
            $dataView['jumlahmatkul']=$this->DB->getCountRowsOfTable("nilai_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $dataView['jumlahsks']=$this->DB->getSumRowsOfTable('sks',"v_konversi2 WHERE iddata_konversi=$iddata_konversi");
            if (!isset($r[1])) {
                $_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']=array();
                throw new Exception("Data Konversi dengan ID ($iddata_konversi) tidak terdaftar.");
            }
            $_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi']=$dataView;
            $this->Nilai->setDataMHS($dataView);
            $nilai=$this->Nilai->getNilaiKonversi($iddata_konversi,$dataView['idkur']);		
            $this->RepeaterS->dataSource=$nilai;
            $this->RepeaterS->dataBind();            	            
        } catch (Exception $ex) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$ex->getMessage();
        }        
	}
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        $dataReport=$_SESSION['currentPageDetailKonversiMatakuliah']['DataKonversi'];
        $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$dataReport['kjur']];
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :                
                $messageprintout='Hasil konversi matakuliah :';                
                
                $kaprodi=$this->Nilai->getKetuaPRODI($dataReport['kjur']);
                $dataReport['nama_kaprodi']=$kaprodi['nama_dosen'];
                $dataReport['jabfung_kaprodi']=$kaprodi['nama_jabatan'];
                $dataReport['nidn_kaprodi']=$kaprodi['nidn'];
                
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                $this->report->printKonversiMatakuliah($this->Nilai);
            break;
            case  'pdf' :                
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text="Konversi Matakuliah";
        $this->modalPrintOut->show();
	}
}