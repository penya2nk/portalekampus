<?php
prado::using ('Application.MainPageM');
class CDetailKHS extends MainPageM {	
    public static $TotalSKS=0;
	public static $TotalM=0;
    public $NilaiSemesterLalu;
    public $NilaiSemesterSekarang;
	public function onLoad($param) {
		parent::onLoad($param);							
		$this->showSubMenuAkademikNilai=true;
        $this->showKHS=true;    
        $this->createObj('Nilai');
        $this->createObj('Finance');
        
		if (!$this->IsPostback&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageDetailKHS'])||$_SESSION['currentPageDetailKHS']['page_name']!='m.nilai.DetailKHS') {
				$_SESSION['currentPageDetailKHS']=array('page_name'=>'m.nilai.DetailKHS','page_num'=>0,'search'=>false,'DataMHS'=>array());												                                               
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
            $idkrs=addslashes($this->request['id']);            				
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,d.idkelas,d.k_status,krs.idsmt,krs.tahun,krs.tasmt,krs.sah FROM krs JOIN dulang d ON (d.nim=krs.nim) LEFT JOIN v_datamhs vdm ON (krs.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE krs.idkrs='$idkrs'";
            $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status','idsmt','tahun','tasmt','sah'));
            $r=$this->DB->getRecord($str);	           
            $datamhs=$r[1];
            if (!isset($r[1])) {
                $_SESSION['currentPageDetailKHS']['DataMHS']=array();
                throw new Exception("KRS dengan ID ($idkrs) tidak terdaftar.");
            }            
            $tahun=$datamhs['tahun'];
            $idsmt=$datamhs['idsmt'];
            if ($datamhs['sah']==0) {
                throw new Exception("KRS dengan ID ($idkrs) belum disahkan.");
            }
            $this->Finance->setDataMHS($datamhs);                
            $lunaspembayaran=$this->Finance->getLunasPembayaran($_SESSION['ta'],$_SESSION['semester']);
            if (($lunaspembayaran==false)&&$_SESSION['ta']>=2010) {                    					                    
                throw new Exception ("Pembayaran uang kuliah pada {$tahun}{$idsmt} belum lunas.");
            }
            $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
            $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
            $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
            $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
            $_SESSION['currentPageDetailKHS']['DataMHS']=$datamhs;
            $this->Nilai->setDataMHS($datamhs);
            $khs = $this->Nilai->getKHS($tahun,$idsmt);
            if(isset($khs[1])){
				$this->NilaiSemesterLalu=$this->Nilai->getKumulatifSksDanNmSemesterLalu($tahun,$idsmt);
                $this->NilaiSemesterSekarang=$this->Page->Nilai->getIPKSampaiTASemester($tahun,$idsmt,'ipksksnm');
                $this->RepeaterS->DataSource=$khs ;
                $this->RepeaterS->dataBind();							
			}else{				
				throw new Exception ('KRS dengan ID tidak ada matakuliahnya.');
			}		            
        } catch (Exception $ex) {
            $this->idProcess='view';	
			$this->errorMessage->Text=$ex->getMessage();
        }        
	}
    public function itemBound ($sender,$param) {
        $item=$param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') { 
            $sks=$item->DataItem['sks'];
            DetailKHS::$TotalSKS += $sks;            
            $m = (intval($sks)) * $this->Nilai->getAngkaMutu($item->DataItem['n_kual']);
            DetailKHS::$TotalM += $m;            
        }
    }
	public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'excel2007' :
                $messageprintout="Mohon maaf Print out pada mode excel 2007 belum kami support.";                
            break;
            case  'pdf' :                
                $messageprintout='';
                $tahun=$_SESSION['currentPageDetailKHS']['DataMHS']['tahun'];
                $semester=$_SESSION['currentPageDetailKHS']['DataMHS']['idsmt'];
                $nama_tahun = $this->DMaster->getNamaTA($tahun);
                $nama_semester = $this->setup->getSemester($semester);        
                $dataReport=$_SESSION['currentPageDetailKHS']['DataMHS'];

                $dataReport['ta']=$tahun;
                $dataReport['semester']=$semester;
                $dataReport['nama_tahun']=$nama_tahun;
                $dataReport['nama_semester']=$nama_semester;        

                $dataReport['nama_jabatan_khs']=$this->setup->getSettingValue('nama_jabatan_khs');
                $dataReport['nama_penandatangan_khs']=$this->setup->getSettingValue('nama_penandatangan_khs');
                $dataReport['jabfung_penandatangan_khs']=$this->setup->getSettingValue('jabfung_penandatangan_khs');
                $dataReport['nidn_penandatangan_khs']=$this->setup->getSettingValue('nidn_penandatangan_khs');

                $kaprodi=$this->Nilai->getKetuaPRODI($dataReport['kjur']);
                $dataReport['nama_kaprodi']=$kaprodi['nama_dosen'];
                $dataReport['jabfung_kaprodi']=$kaprodi['nama_jabatan'];
                $dataReport['nidn_kaprodi']=$kaprodi['nidn'];

                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                $this->report->printKHS($this->Nilai,true);		
                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text="Kartu Hasil Studi T.A $nama_tahun Semester $nama_semester";
        $this->modalPrintOut->show();
	}
}