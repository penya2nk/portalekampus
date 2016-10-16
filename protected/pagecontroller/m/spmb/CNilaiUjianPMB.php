<?php
prado::using ('Application.MainPageM');
class CNilaiUjianPMB extends MainPageM {
    public $DataUjian;
	public function onLoad($param) {
		parent::onLoad($param);			
        $this->showSubMenuSPMBUjianPMB=true;
		$this->showNilaiUjianPMB=true;
        $this->createObj('Akademik');
		if (!$this->IsPostBack && !$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageNilaiUjianPMB'])||$_SESSION['currentPageNilaiUjianPMB']['page_name']!='m.spmb.NilaiUjianPMB') {
				$_SESSION['currentPageNilaiUjianPMB']=array('page_name'=>'m.spmb.NilaiUjianPMB','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false,'kjur'=>'none','passinggrade'=>array());												
			}
            $_SESSION['currentPageNilaiUjianPMB']['search']=false;
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

            $daftar_prodi=$_SESSION['daftar_jurusan'];                        
            $daftar_prodi['none']='BELUM DITERIMA DI PRODI MANAPUN';
			$this->tbCmbPs->DataSource=$daftar_prodi;
			$this->tbCmbPs->Text=$_SESSION['currentPageNilaiUjianPMB']['kjur'];			
			$this->tbCmbPs->dataBind();
            
            $tahun_masuk=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
			$this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind();

            
            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();
            
            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();
            
            $_SESSION['currentPageNilaiUjianPMB']['passinggrade']=$this->DMaster->getDataPassingGrade($_SESSION['tahun_masuk']);
            
            $this->lblModulHeader->Text=$this->getInfoToolbar();            
            $this->populateData ();	
		}	
	}    
	public function getDataMHS($idx) {
        return $this->Demik->getDataMHS($idx);
    }
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $_SESSION['currentPageNilaiUjianPMB']['passinggrade']=$this->DMaster->getDataPassingGrade($_SESSION['tahun_masuk']);
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
        $_SESSION['currentPageNilaiUjianPMB']['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
	public function getInfoToolbar() {        
        $kjur=$_SESSION['currentPageNilaiUjianPMB']['kjur'];        		
        $ps=$kjur=='none'?'Yang belum diterima di Prodi Manapun':'Program Studi '.$_SESSION['daftar_jurusan'][$kjur];
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="$ps Tahun Masuk $tahunmasuk";
		return $text;
	}
    
	public function searchRecord ($sender,$param) {
		$_SESSION['currentPageNilaiUjianPMB']['search']=true;
		$this->populateData($_SESSION['currentPageNilaiUjianPMB']['search']);
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNilaiUjianPMB']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageNilaiUjianPMB']['search']);
	}		
	public function populateData ($search=false) {	
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $kjur=$_SESSION['currentPageNilaiUjianPMB']['kjur'];        
        if ($search) {                        
            $str_kjur=$kjur=='none'?' WHERE (num.kjur=0 OR num.kjur IS NULL)':" AND num.kjur=$kjur";	                
            $str = "SELECT fp.no_formulir,fp.nama_mhs,ku.tgl_ujian,ts.nama_tempat,num.kjur,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.nilai,fp.kjur1,fp.kjur2,num.passinggrade,num.kjur AS diterima_di_prodi FROM kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir)$str_kjur";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {
                case 'no_formulir' :
                    $clausa=" AND fp.no_formulir='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir)$clausa",'fp.no_formulir');                    
                    $str="$str $clausa";
                break;
                case 'nama_mhs' :
                    $clausa=" AND fp.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir)$clausa",'fp.no_formulir');
                    $str = "$str $clausa";
                break;
            }
        }else{
            $str_kjur=$kjur=='none'?' AND (num.kjur=0 OR num.kjur IS NULL)':" AND num.kjur=$kjur";	                
            $str = "SELECT fp.no_formulir,fp.nama_mhs,ku.tgl_ujian,ts.nama_tempat,num.kjur,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.nilai,fp.kjur1,fp.kjur2,num.passinggrade,num.kjur AS diterima_di_prodi FROM kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir) WHERE fp.ta='$tahun_masuk'$str_kjur";
            $jumlah_baris=$this->DB->getCountRowsOfTable("kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir) WHERE fp.ta='$tahun_masuk'$str_kjur",'ku.no_formulir');			            
        }	
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageNilaiUjianPMB']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageNilaiUjianPMB']['page_num']=0;}
		$str = $str . " ORDER BY fp.nama_mhs ASC LIMIT $offset,$limit";				
        $_SESSION['currentPageNilaiUjianPMB']['offset']=$offset;
        $_SESSION['currentPageNilaiUjianPMB']['limit']=$limit;
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tgl_ujian','jumlah_soal','jawaban_benar','jawaban_salah','nilai','kjur1','kjur2','passinggrade','diterima_di_prodi'));				
		$r = $this->DB->getRecord($str,$offset+1);
        $result=array();
        $passinggrade=$_SESSION['currentPageNilaiUjianPMB']['passinggrade'];        
        while (list($k,$v)=each($r)) { 
            if ($kjur=='none') {
                $pil1='N.A';
                $bool1=false;
                if ($v['kjur1'] > 0) {                  
                    $nama_ps=$_SESSION['daftar_jurusan'][$v['kjur1']];      
                    $bool1=($v['nilai'] >= $passinggrade[$v['kjur1']]);
                    $ket=$bool1 == true ? 'LULUS' : 'GAGAL';                            
                    $pil1='<a href="#" OnClick="return false;" Title="'.$nama_ps.'">'.$ket.'</a';

                }
                $v['pil1']=$pil1;
                $pil2='N.A';
                $bool2=false;
                if ($v['kjur2'] > 0) {
                    $nama_ps=$_SESSION['daftar_jurusan'][$v['kjur2']];      
                    $bool2=($v['nilai'] >= $passinggrade[$v['kjur2']]);
                    $ket=$bool2 == true ? 'LULUS' : 'GAGAL';                            
                    $pil2='<a href="#" OnClick="return false;" Title="'.$nama_ps.'">'.$ket.'</a';
                }            
                $v['pil2']=$pil2;
                $v['bool']=$bool1 || $bool2;
            }else{
                $pil1='N.A';
                if ($v['kjur1'] == $v['diterima_di_prodi']) {
                    $nama_ps=$_SESSION['daftar_jurusan'][$v['diterima_di_prodi']];     
                    $ket='DI TERIMA';
                    $pil1='<a href="#" OnClick="return false;" Title="'.$nama_ps.'">'.$ket.'</a';
                }
                $v['pil1']=$pil1;                
                $pil2='N.A';
                if ($v['kjur2'] == $v['diterima_di_prodi']) {
                    $nama_ps=$_SESSION['daftar_jurusan'][$v['diterima_di_prodi']];     
                    $ket='DI TERIMA';
                    $pil1='<a href="#" OnClick="return false;" Title="'.$nama_ps.'">'.$ket.'</a';
                }
                $v['pil2']=$pil2;
            }            
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS); 
	}	
	public function itemCreated ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {		            
            $item->btnEdit->Enabled=$item->DataItem['bool'];
		}
	}  
    public function setKelulusan ($sender,$param) {		
		$this->idProcess='add';
		$no_formulir=$this->getDataKeyField($sender,$this->RepeaterS);
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,k.nkelas,fp.kjur1,fp.kjur2,fp.ta AS tahun_masuk,fp.idsmt AS semester_masuk FROM formulir_pendaftaran fp,profiles_mahasiswa pm,kelas k WHERE fp.no_formulir=pm.no_formulir AND fp.idkelas=k.idkelas AND fp.no_formulir='$no_formulir'";
        $this->DB->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','nkelas','kjur1','kjur2','tahun_masuk','semester_masuk'));
        $r=$this->DB->getRecord($str);
        $r[1]['nim']='N.A';
        $r[1]['nirm']='N.A';
        $pilihan2=$r[1]['kjur2']==0 ?'N.A' :$_SESSION['daftar_jurusan'][$r[1]['kjur2']];
        $r[1]['nama_ps']='<strong>PILIHAN 1 : </strong>'.$_SESSION['daftar_jurusan'][$r[1]['kjur1']] .'<br/> <strong>PILIHAN 2 : </strong>'.$pilihan2;
        $r[1]['nama_konsentrasi']='N.A';
        $r[1]['nama_dosen']='N.A';
        $r[1]['status']='N.A';          
        
        $this->Demik->setDataMHS($r[1]);
        $str = "SELECT num.idnilai_ujian_masuk,ku.tgl_ujian,ku.tgl_selesai_ujian,ku.isfinish,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.soal_tidak_terjawab,num.nilai FROM kartu_ujian ku JOIN nilai_ujian_masuk num ON ku.no_formulir=num.no_formulir WHERE ku.no_formulir=$no_formulir";
        $this->DB->setFieldTable(array('idnilai_ujian_masuk','tgl_ujian','tgl_selesai_ujian','isfinish','jumlah_soal','jawaban_benar','jawaban_salah','soal_tidak_terjawab','nilai'));
        $dataujian = $this->DB->getRecord($str);         
        $this->DataUjian=$dataujian[1];
        $this->hiddenid->Value=$this->DataUjian['idnilai_ujian_masuk'];
        $ps=array('none'=>' ',$r[1]['kjur1']=>$_SESSION['daftar_jurusan'][$r[1]['kjur1']]);        
        if ($r[1]['kjur2']!='' && $r[1]['kjur2']!=0) {
           $ps[$r[1]['kjur2']]=$_SESSION['daftar_jurusan'][$r[1]['kjur2']];            
        }
        $this->cmbAddKjur->DataSource=$ps;
        $this->cmbAddKjur->dataBind();
	}	
    public function checkPassingGradeExist ($sender,$param) {
		$this->idProcess='add';
        $kjur=$param->Value;		
        if ($kjur != '') {
            try {
                $passinggrade=$_SESSION['currentPageNilaiUjianPMB']['passinggrade'][$kjur];
                if (!($passinggrade > 0)) {
                    throw new Exception ("Untuk Program studi ini, Nilai passing grade belum disetting.");	
                }               
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function saveData ($sender,$param) {
		if ($this->IsValid) {		            
            $id=$this->hiddenid->Value;
            $prodi=$this->cmbAddKjur->Text;
            $passinggrade=$_SESSION['currentPageNilaiUjianPMB']['passinggrade'][$prodi];
			$str = "UPDATE nilai_ujian_masuk SET passinggrade=$passinggrade,ket_lulus=1,kjur=$prodi WHERE idnilai_ujian_masuk=$id";
			$this->DB->updateRecord($str);
			$this->redirect('spmb.NilaiUjianPMB',true);
		}
	}
    public function printOut ($sender,$param) {
        $this->createObj('reportspmb');
        $tahun_masuk=$_SESSION['tahun_masuk'];
        $kjur=$_SESSION['currentPageNilaiUjianPMB']['kjur'];                       
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';
        switch ($_SESSION['outputreport']) {
            case  'summarypdf' :
                $messageprintout="Mohon maaf Print out pada mode summary pdf tidak kami support.";                
            break;
            case  'summaryexcel' :
                $messageprintout="Mohon maaf Print out pada mode summary excel tidak kami support.";                
            break;
            case  'pdf' :
                $messageprintout="Mohon maaf Print out pada mode pdf belum kami support.";                
            break;        
            case  'excel2007' :
                $messageprintout='';
                $dataReport['tahun_masuk']=$tahun_masuk;
                $dataReport['kjur']=$kjur;
                $dataReport['linkoutput']=$this->linkOutput; 
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);                
                $this->report->printNilaiUjian($_SESSION['currentPageNilaiUjianPMB']['passinggrade'],$_SESSION['daftar_jurusan']);
            break;        
            
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text="Hasil Ujian PMB Tahun Masuk $tahun_masuk";
        $this->modalPrintOut->show();
    }
}

?>