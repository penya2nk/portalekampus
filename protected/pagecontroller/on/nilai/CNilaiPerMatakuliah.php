<?php
prado::using ('Application.MainPageON');
class CNilaiPerMatakuliah extends MainPageON {	
	public function onLoad($param) {		
		parent::onLoad($param);				
        $this->showSubMenuAkademikNilai=true;
        $this->showNilaiPerMatakuliah=true;
        
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageNilaiPerMatakuliah'])||$_SESSION['currentPageNilaiPerMatakuliah']['page_name']!='on.nilai.NilaiPerMatakuliah') {
				$_SESSION['currentPageNilaiPerMatakuliah']=array('page_name'=>'on.nilai.NilaiPerMatakuliah','page_num'=>0,'search'=>false,'InfoMatkul'=>array());
			}  
            $_SESSION['currentPageNilaiPerMatakuliah']['search']=false;
            try {                     
                $id=addslashes($this->request['id']);                               
                $this->hiddenid->Value=$id;
                $infomatkul=$this->Demik->getInfoMatkul($id,'penyelenggaraan'); 
                if (!isset($infomatkul['idpenyelenggaraan'])) {                                                
                    throw new Exception ("Sebelum input nilai mohon pilih Kode penyelenggaraan.");		
                }
                $kjur=$infomatkul['kjur'];        
                $ps=$_SESSION['daftar_jurusan'][$kjur];
                $ta=$this->DMaster->getNamaTA($infomatkul['tahun']);
                $semester=$this->setup->getSemester($infomatkul['idsmt']);
                $text="Program Studi $ps TA $ta Semester $semester";
                
                $this->lblModulHeader->Text=$text;
                $_SESSION['currentPageNilaiPerMatakuliah']['InfoMatkul']=$infomatkul; 
                $this->RepeaterP->PageSize=$this->setup->getSettingValue('default_pagesize');
                $this->populateDataPeserta();		
            } catch (Exception $ex) {
                $this->idProcess='view';        
                
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
                $this->lblModulHeader->Text=$this->getInfoToolbar();
                $this->populateData();
                $this->errormessage->Text=$ex->getMessage();
            }
		}		
	}	
    public function getInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
		$ta=$this->DMaster->getNamaTA($_SESSION['ta']);
		$semester=$this->setup->getSemester($_SESSION['semester']);
		$text="Program Studi $ps TA $ta Semester $semester";
		return $text;
	}
    public function changeTbTA ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['ta']=$this->tbCmbTA->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['semester']=$this->tbCmbSemester->Text;		
        $this->lblModulHeader->Text=$this->getInfoToolbar();		
	}
    public function changeTbPs ($sender,$param) {		
        $this->idProcess='view';
        $_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();  
        $this->populateData();
	} 
    public function searchRecord ($sender,$param) {
        $this->idProcess='view';
		$_SESSION['currentPageNilaiPerMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPageNilaiPerMatakuliah']['search']);
	}       
    public function populateData($search=false) {	
        $ta=$_SESSION['ta'];
        $idsmt=$_SESSION['semester'];
        $kjur=$_SESSION['kjur'];        
        $idkur=$this->Demik->getIDKurikulum($kjur);
        
        if ($search) {
            $txtsearch=  addslashes($this->txtKriteria->Text);           
            switch ($this->cmbKriteria->Text) {                                
                case 'kmatkul' :
                    $clausa="AND kmatkul LIKE '%$txtsearch%'";
                break;
                case 'nmatkul' :
                    $clausa="AND nmatkul LIKE '%$txtsearch%'";
                break;
                case 'nidn' :
                    $clausa="AND nidn='$txtsearch'";
                break;
                case 'nama_dosen' :
                    $clausa="AND nama_dosen LIKE '%$txtsearch%'";
                break;
            }            			
        }
        $str = "SELECT idpenyelenggaraan,kmatkul,nmatkul,sks,semester,nama_dosen,iddosen FROM v_penyelenggaraan WHERE idsmt='$idsmt' AND tahun='$ta' AND kjur='$kjur' AND idkur=$idkur $clausa ORDER BY semester ASC,kmatkul ASC";        
        $this->DB->setFieldTable (array('idpenyelenggaraan','kmatkul','nmatkul','sks','semester','nama_dosen','iddosen'));			
        $r= $this->DB->getRecord($str);
        
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['jumlah_peserta']=$this->Demik->getJumlahMhsInPenyelenggaraan($v['idpenyelenggaraan']);
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterP->render($param->NewWriter);	
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNilaiPerMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateDataPeserta($_SESSION['currentPageNilaiPerMatakuliah']['search']);
	}    
	public function setData($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType == 'Item' || $item->ItemType=='AlternatingItem') {	
			$item->cmbNilai->Enabled=$item->DataItem['k_status'] == 'L'?false:true;
            if ($item->DataItem['batal']) {
				$item->literalKet->Text='Dibatalkan oleh dosen wali.';	
                $item->txtNilaiAngka->Enabled=false;
				$item->cmbNilai->Enabled=false;		
			}else {
                $item->literalKet->Text='-';
                $item->txtNilaiAngka->Text=$item->DataItem['n_kuan'];
				$item->cmbNilai->Text=$item->DataItem['n_kual'];
				$item->nilai_sebelumnya->Value=$item->DataItem['n_kual'];
                $bool=$item->DataItem['bydosen']==true?false:true;
                $item->txtNilaiAngka->Enabled=$bool;
				$item->cmbNilai->Enabled=$bool;
			}	
		}		
	}
    public function searchRecord2 ($sender,$param) {
		$_SESSION['currentPageNilaiPerMatakuliah']['search']=true;
		$this->populateDataPeserta($_SESSION['currentPageNilaiPerMatakuliah']['search']);
	}
    public function populateDataPeserta ($search=false) {      
        $id=$_SESSION['currentPageNilaiPerMatakuliah']['InfoMatkul']['idpenyelenggaraan'];
        $str = "SELECT vkm.idkrsmatkul,vkm.nim,vdm.nama_mhs,vdm.jk,nm.n_kuan,nm.n_kual,nm.userid_input,nm.tanggal_input,nm.userid_modif,nm.tanggal_modif,nm.bydosen,nm.ket,vkm.batal,vkm.sah,vdm.k_status FROM v_krsmhs vkm LEFT JOIN nilai_matakuliah nm ON (nm.idkrsmatkul=vkm.idkrsmatkul) JOIN v_datamhs vdm ON (vdm.nim=vkm.nim) WHERE vkm.idpenyelenggaraan='$id' AND vkm.sah=1";	
		
        if ($search) {            
            $txtsearch=$this->txtKriteria2->Text;
            switch ($this->cmbKriteria2->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' AND vkm.sah=1 $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' AND vkm.sah=1 $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' AND vkm.sah=1 $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                        
            $jumlah_baris=$this->DB->getCountRowsOfTable("v_krsmhs vkm,v_datamhs vdm WHERE vkm.nim=vdm.nim AND idpenyelenggaraan='$id' AND vkm.sah=1",'vdm.nim');
        }		
		$this->RepeaterP->CurrentPageIndex=$_SESSION['currentPageNilaiPerMatakuliah']['page_num'];
		$this->RepeaterP->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterP->CurrentPageIndex*$this->RepeaterP->PageSize;
		$limit=$this->RepeaterP->PageSize;
		if ($offset+$limit>$this->RepeaterP->VirtualItemCount) {
			$limit=$this->RepeaterP->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=$this->setup->getSettingValue('default_pagesize');$_SESSION['currentPageNilaiPerMatakuliah']['page_num']=0;}		
        $str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idkrsmatkul','nim','nama_mhs','jk','n_kuan','n_kual','userid_input','tanggal_input','userid_modif','tanggal_modif','bydosen','ket','batal','sah','k_status'));				
		$r=$this->DB->getRecord($str,$offset+1);
        $result=array();
        while (list($k,$v)=each($r)) {
            $idkrsmatkul=$v['idkrsmatkul'];
            if ($v['userid_input']==0) {
                $v['tanggal_input']='-';
                $v['tanggal_modif']='-';
            }else{
                $v['tanggal_input']=$this->TGL->tanggal ('j F Y',$v['tanggal_input']);
                $v['tanggal_modif']=$this->TGL->tanggal ('j F Y',$v['tanggal_modif']);
            }
            $str = "SELECT km.idkelas,km.nama_kelas FROM kelas_mhs km,kelas_mhs_detail kmd WHERE km.idkelas_mhs=kmd.idkelas_mhs AND kmd.idkrsmatkul=$idkrsmatkul LIMIT 1";
            $this->DB->setFieldTable(array('idkelas','nama_kelas'));				
            $datakelas=$this->DB->getRecord($str,$offset+1);
            $v['namakelas']='N.A';
            if (isset($datakelas[1])) {
                $v['namakelas']=$this->DMaster->getNamaKelasByID($datakelas[1]['idkelas']).'-'.chr($datakelas[1]['nama_kelas']+64);
            }
            $result[$k]=$v;
        }
		$this->RepeaterP->DataSource=$result;
		$this->RepeaterP->dataBind();        
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterP);
	}
    public function saveData ($sender,$param){
        if ($this->IsValid){
            $repeater=$this->RepeaterP;            	
            $userid=$this->Pengguna->getDataUser('userid');		
            foreach ($repeater->Items as $inputan) {	
                if ($inputan->cmbNilai->Enabled) {												
                    $idkrsmatkul=$inputan->idkrsmatkul->Value;		
                    $n_kuan=addslashes($inputan->txtNilaiAngka->Text);
                    $n_kual=$inputan->cmbNilai->Text;				
                    $nilai_sebelumnya=$inputan->nilai_sebelumnya->Value;				
                    if ($nilai_sebelumnya==''&&$n_kual!='none') {//insert					
                        if (!$this->DB->checkRecordIsExist('idkrsmatkul','nilai_matakuliah',$idkrsmatkul)) {
                            $str = "INSERT INTO nilai_matakuliah (idnilai,idkrsmatkul,n_kuan,n_kual,userid_input,tanggal_input,tanggal_modif) VALUES (NULL,$idkrsmatkul,'$n_kuan','$n_kual',$userid,NOW(),NOW())";				
                            $this->DB->insertRecord($str);
                        }
                    }elseif($n_kual!='none'&&$n_kual!=$nilai_sebelumnya){//update										
                        $str = "UPDATE nilai_matakuliah SET n_kuan='$n_kuan',n_kual='$n_kual',userid_modif='$userid',tanggal_modif=NOW(),ket='dari $nilai_sebelumnya menjadi $n_kual' WHERE idkrsmatkul='$idkrsmatkul'";				
                        $this->DB->updateRecord($str);
                    }elseif($nilai_sebelumnya != ''&&$n_kual=='none'){//delete
                        $str = "nilai_matakuliah WHERE idkrsmatkul='$idkrsmatkul'";	
                        $this->DB->deleteRecord($str);
                    }				
                }
            }
            $idpenyelenggaraan=$_SESSION['currentPageNilaiPerMatakuliah']['InfoMatkul']['idpenyelenggaraan'];
            $this->redirect('nilai.NilaiPerMatakuliah', true,array('id'=>$idpenyelenggaraan));
        }		
	}	
}