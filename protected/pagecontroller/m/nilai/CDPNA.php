<?php
prado::using ('Application.MainPageM');
class CDPNA extends MainPageM {
	public $pnlDaftarKelas=false;
	public $plnViewKelas=false;
	public $data_kelas;
	public function onLoad($param) {		
		parent::onLoad($param);				
		$this->showSubMenuAkademikNilai=true;
        $this->showDPNA=true;
        
        $this->createObj('Nilai');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDPNA'])||$_SESSION['currentPageDPNA']['page_name']!='m.nilai.DPNA') {
				$_SESSION['currentPageDPNA']=array('page_name'=>'m.nilai.DPNA','page_num'=>0,'search'=>false);
			}  
            $_SESSION['currentPageDPNA']['search']=false;
            $_SESSION['currentPageDetailDPNA']=array();
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
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDPNA']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageDPNA']['search']=true;
		$this->populateData($_SESSION['currentPageDPNA']['search']);
	}	
	public function populateData($search=false) {				
		$ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];		
        if ($search) {            
            $txtsearch=addslashes($this->txtKriteria->Text);
            switch ($this->cmbKriteria->Text) { 
                case 'kmatkul' :
                    $clausa=" AND kmatkul LIKE '%$txtsearch%'";
                    $str="SELECT vp.idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen,jumlahmhs FROM v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta'$clausa";				            
                    $jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'vp.idpenyelenggaraan');						
                break;				
                case 'nmatkul':
                    $clausa=" AND nmatkul LIKE '%$txtsearch%'";
                    $str="SELECT vp.idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen,jumlahmhs FROM v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta'$clausa";				            
                    $jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'vp.idpenyelenggaraan');						                    
                break;
                case 'nama_dosen':
                    $clausa=" AND nama_dosen LIKE '%$txtsearch%'";
                    $str="SELECT vp.idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen,jumlahmhs FROM v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta'$clausa";				            
                    $jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND sah=1 AND batal=0$clausa GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'$clausa",'vp.idpenyelenggaraan');						                                        
                break;                
            }
        }else{
            $str="SELECT vp.idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen,jumlahmhs FROM v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND sah=1 AND batal=0 GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'";				
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_penyelenggaraan vp, (SELECT idpenyelenggaraan,COUNT(idpenyelenggaraan) AS jumlahmhs FROM v_krsmhs  WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND sah=1 AND batal=0 GROUP BY idpenyelenggaraan) AS vkm  WHERE vkm.idpenyelenggaraan=vp.idpenyelenggaraan AND idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur'",'vp.idpenyelenggaraan');						
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDPNA']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageDPNA']['page_num']=0;}
        $str="$str ORDER BY nmatkul ASC LIMIT $offset,$limit";				
		$this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nidn','nama_dosen','jumlahmhs'));			
		$r=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($r)) {
            $idpenyelenggaraan=$v['idpenyelenggaraan'];            
            $jumlah_baris= $this->DB->getCountRowsOfTable("krsmatkul km JOIN nilai_matakuliah nm ON (nm.idkrsmatkul=km.idkrsmatkul) WHERE km.idpenyelenggaraan=$idpenyelenggaraan",'km.idkrsmatkul');                        
            $v['belum_ada_nilai']=$v['jumlahmhs']-$jumlah_baris;
            $result[$k]=$v;
        }      
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
}