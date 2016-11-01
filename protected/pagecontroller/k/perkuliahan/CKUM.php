<?php
prado::using ('Application.MainPageK');
class CKUM extends MainPageK {
	public function onLoad($param) {		
		parent::onLoad($param);		            
        $this->showMenuAkademik=true; 
        $this->showKUM=true; 
        $this->createObj('Finance');
        $this->createObj('KRS');
		if (!$this->IsPostBack&&!$this->IsCallBack) {   
            if (!isset($_SESSION['currentPageKUM'])||$_SESSION['currentPageKUM']['page_name']!='k.perkuliahan.KUM') {
				$_SESSION['currentPageKUM']=array('page_name'=>'k.perkuliahan.KUM','page_num'=>0,'search'=>false,'jenisujian'=>'uts');												
			}            
            $_SESSION['currentPageKUM']['search']=false;   
            $daftar_ps=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');            
			$this->tbCmbPs->DataSource=$daftar_ps;
			$this->tbCmbPs->Text=$_SESSION['kjur'];			
			$this->tbCmbPs->dataBind();	
            
            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
			$this->tbCmbTA->Text=$_SESSION['ta'];
			$this->tbCmbTA->dataBind();	
            
            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
			$this->tbCmbSemester->DataSource=$semester;
			$this->tbCmbSemester->Text=$_SESSION['semester'];
			$this->tbCmbSemester->dataBind();
            
            $this->tbCmbTahunMasuk->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');					
			$this->tbCmbTahunMasuk->Text=$_SESSION['tahun_masuk'];						
			$this->tbCmbTahunMasuk->dataBind(); 
            
            $this->populateData();
            $this->setInfoToolbar();
		}                
	}
    public function setInfoToolbar() {       
        $jenisujian=strtoupper($_SESSION['currentPageKUM']['jenisujian']);
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);        		
		$this->lblModulHeader->Text="$jenisujian Program Studi $ps T.A $ta";        
	}
    public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
    public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;
        $this->setInfoToolbar();
		$this->populateData();
	} 
    public function changeTbSemester($sender,$param) {    	
        $_SESSION['semester']=$this->tbCmbSemester->Text;			
		$this->setInfoToolbar(); 
        $this->populateData();
	}
    public function changeTbTahunMasuk($sender,$param) {    				
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;		                
		$this->setInfoToolbar(); 
        $this->populateData();
	}
    public function changeJenisUjian ($sender,$param) {		
		$_SESSION['currentPageKUM']['jenisujian']=$this->cmbJenisUjian->Text;
		$this->cmbJenisUjian->Text=$_SESSION['currentPageKUM']['jenisujian'];
        $this->lblModulHeader->Text=strtoupper($_SESSION['currentPageKUM']['jenisujian']);
		$this->populateData();
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKUM']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageKUM']['search']);
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageKUM']['search']=true;
		$this->populateData($_SESSION['currentPageKUM']['search']);
	}
    public function populateData($search=false) {
        $ta=$_SESSION['ta'];
		$semester=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];
		$tahun_masuk=$_SESSION['tahun_masuk'];
        $str_tahun_masuk=" AND fp.ta=$tahun_masuk";
        if ($search) {
            $str = "SELECT k.idkrs,k.nim,fp.nama_mhs,fp.jk,d.idkelas,kelas.nkelas,fp.idsmt AS semester_masuk FROM krs k,dulang d,formulir_pendaftaran fp,register_mahasiswa rm,kelas WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND d.nim=k.nim AND kelas.idkelas=d.idkelas AND k.sah=1 AND k.tahun=$ta AND k.idsmt=$semester ";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND rm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("krs k,formulir_pendaftaran fp,register_mahasiswa rm WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND k.tahun=$ta AND k.idsmt=$semester $clausa",'k.nim');		
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND rm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("krs k,formulir_pendaftaran fp,register_mahasiswa rm WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND k.tahun=$ta AND k.idsmt=$semester $clausa",'k.nim');		
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND fp.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable("krs k,formulir_pendaftaran fp,register_mahasiswa rm WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND k.tahun=$ta AND k.idsmt=$semester $clausa",'k.nim');		
                    $str = "$str $clausa";
                break;
            }
        }else{
            $str = "SELECT k.idkrs,k.nim,fp.nama_mhs,fp.jk,d.idkelas,kelas.nkelas,fp.idsmt AS semester_masuk FROM krs k,dulang d,formulir_pendaftaran fp,register_mahasiswa rm,kelas WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND d.nim=k.nim AND kelas.idkelas=d.idkelas AND k.sah=1 AND k.tahun=$ta AND k.idsmt=$semester AND rm.kjur=$kjur $str_tahun_masuk";            
            $jumlah_baris=$this->DB->getCountRowsOfTable("krs k,formulir_pendaftaran fp,register_mahasiswa rm WHERE fp.no_formulir=rm.no_formulir AND k.nim=rm.nim AND k.tahun=$ta AND k.idsmt=$semester AND rm.kjur=$kjur $str_tahun_masuk",'k.nim');		
            
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKUM']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageKUM']['page_num']=0;}
        $str = "$str ORDER BY d.idkelas ASC,fp.nama_mhs ASC LIMIT $offset,$limit";	
        $this->DB->setFieldTable(array('idkrs','nim','nama_mhs','jk','idkelas','nkelas','semester_masuk'));
        $r = $this->DB->getRecord($str,$offset+1);	        
        
        $komponen_biaya=array();
        if ($semester == 3) {
            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'A'));
            $komponen_biaya['A']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');
            $komponen_biaya['A']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');         

            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'B'));
            $komponen_biaya['B']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');            
            $komponen_biaya['B']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');       

            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'C'));
            $komponen_biaya['C']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');            
            $komponen_biaya['C']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('sp');
        }else{
            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'A'));
            $komponen_biaya['A']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('baru');
            $komponen_biaya['A']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('lama');         

            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'B'));
            $komponen_biaya['B']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('baru');            
            $komponen_biaya['B']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('lama');       

            $this->Finance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>$semester,'idkelas'=>'C'));
            $komponen_biaya['C']['baru']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('baru');            
            $komponen_biaya['C']['lama']=$this->Finance->getTotalBiayaMhsPeriodePembayaran('lama');
        }
        $result=array ();
        while (list($k,$v)=each($r)) {
            $idkrs=$v['idkrs'];
            $nim=$v['nim'];
            $str = "SELECT COUNT(idkrsmatkul) AS jumlah_matkul,SUM(sks) AS jumlah_sks FROM v_krsmhs WHERE idkrs='$idkrs'";						
            $this->DB->setFieldTable (array('jumlah_matkul','jumlah_sks'));
			$r2=$this->DB->getRecord($str);
            $v['jumlah_matkul']=$r2[1]['jumlah_matkul'] > 0 ?$r2[1]['jumlah_matkul']:0;
            $v['jumlah_sks']=$r2[1]['jumlah_sks'] > 0 ?$r2[1]['jumlah_sks']:0;
            
            $idkelas=$v['idkelas'];
            if ($semester == 3) {
                
            }else{
                $str2 = "SELECT SUM(dibayarkan) AS dibayarkan FROM transaksi t,transaksi_detail td WHERE td.no_transaksi=t.no_transaksi AND t.nim=$nim AND t.idsmt=$semester AND t.tahun=$ta AND t.commited=1";			
                $this->DB->setFieldTable(array('dibayarkan'));
                $r3=$this->DB->getRecord($str2);				
                $dibayarkan=$r3[1]['dibayarkan'];
                $kewajiban=($ta==$tahun_masuk && $v['semester_masuk'] == $semester) ? $komponen_biaya[$idkelas]['baru']:$komponen_biaya[$idkelas]['lama'];
                $sisa=$kewajiban-$dibayarkan;
            }
            $v['kewajiban']=$kewajiban;
            $v['dibayarkan']=$dibayarkan;
            $v['sisa']=$sisa;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource= $result;
		$this->RepeaterS->dataBind();     
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
    }
    public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType==='Item' || $item->ItemType==='AlternatingItem') {
            $bool=false;
            if ($item->DataItem['sisa'] > 0 ) {
                $half_payment=$item->DataItem['kewajiban']/2;                     
                if ($item->DataItem['sisa']<=$half_payment) {
                    $bool=true;
                    $keterangan="SISA ".number_format($item->DataItem['sisa'],0,0,'.');
                    $btnstyle=' class="text-primary-600"';
                }
            }else{
                $bool=true;
                $keterangan='LUNAS';
                $btnstyle=' class="text-primary-600"';
            }
            $item->btnPrintOutR->Enabled=$bool;
            $item->literalBTNStyle->Text=$btnstyle;
            $item->literalKet->Text=$keterangan;
        }
    }
    public function printOutR ($sender,$param) {
        $idkrs=$this->getDataKeyField($sender, $this->RepeaterS);
        $this->createObj('reportkrs');
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
                $messageprintout="Mohon maaf Print out pada mode excel belum kami support.";                 
            break;
            case  'pdf' :
                $messageprintout="";
                $str = "SELECT krs.idkrs,vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,d.idkelas,d.k_status,krs.idsmt,krs.tahun,krs.tasmt,krs.sah FROM krs JOIN dulang d ON (d.nim=krs.nim) LEFT JOIN v_datamhs vdm ON (krs.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE krs.idkrs='$idkrs'";
                $this->DB->setFieldTable(array('idkrs','no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status','idsmt','tahun','tasmt','sah'));
                $r=$this->DB->getRecord($str);	           
                $dataReport=$r[1];
                
                $dataReport['nama_ps']=$_SESSION['daftar_jurusan'][$dataReport['kjur']];                
                $nama_tahun = $this->DMaster->getNamaTA($dataReport['tahun']);   
                $nama_semester = $this->setup->getSemester($dataReport['idsmt']);
                $dataReport['nama_tahun']=$nama_tahun; 
                $dataReport['nama_semester']=$nama_semester;
                
                $nama_dosen=$this->DMaster->getNamaDosenWaliByID($dataReport['iddosen_wali']);				                    
                $dataReport['nama_dosen']=$nama_dosen;
                
                $kaprodi=$this->KRS->getKetuaPRODI($dataReport['kjur']);
                print_R($kaprodi);
                $dataReport['nama_kaprodi']=$kaprodi['nama_dosen'];
                $dataReport['jabfung_kaprodi']=$kaprodi['nama_jabatan'];
                $dataReport['nidn_kaprodi']=$kaprodi['nidn'];
                
                $dataReport['linkoutput']=$this->linkOutput;
                $this->report->setDataReport($dataReport); 
                $this->report->setMode($_SESSION['outputreport']);
                
                $this->report->printKUM($_SESSION['currentPageKUM']['jenisujian'],$this->KRS);                
            break;
        }
        $this->lblMessagePrintout->Text=$messageprintout;
        $this->lblPrintout->Text='Kartu Ujian Mahasiswa';
        $this->modalPrintOut->show();
    }
}
?>