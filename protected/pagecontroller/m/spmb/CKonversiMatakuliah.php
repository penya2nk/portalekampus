<?php
prado::using ('Application.MainPageM');
class CKonversiMatakuliah extends MainPageM {	
	public function onLoad($param) {
		parent::onLoad($param);					
        $this->showKonversiMatakuliah=true;
        $this->createObj('Nilai');
		$this->Pengguna->moduleForbiden('spmb','ks');			
		if (!$this->IsPostBack && !$this->IsCallBack) {
            if (!isset($_SESSION['currentPageKonversiMatakuliah'])||$_SESSION['currentPageKonversiMatakuliah']['page_name']!='m.spmb.KonversiMatakuliah') {
				$_SESSION['currentPageKonversiMatakuliah']=array('page_name'=>'m.spmb.KonversiMatakuliah','page_num'=>0,'offset'=>0,'limit'=>0,'search'=>false,'daftarmatkul'=>array(),'kjur'=>$_SESSION['kjur']);												
			}
            $_SESSION['currentPageKonversiMatakuliah']['search']=false;
            
            $this->RepeaterS->PageSize=$this->setup->getSettingValue('default_pagesize');

            $daftar_prodi=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');                                    
			$this->tbCmbPs->DataSource=$daftar_prodi;
			$this->tbCmbPs->Text=$_SESSION['currentPageKonversiMatakuliah']['kjur'];			
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

			$this->lblModulHeader->Text=$this->getInfoToolbar();
			$this->populateData();
		}					
	}
	public function getInfoToolbar() {        
        $kjur=$_SESSION['currentPageKonversiMatakuliah']['kjur'];        		
        $ps=$_SESSION['daftar_jurusan'][$kjur];
		$tahunmasuk=$this->DMaster->getNamaTA($_SESSION['tahun_masuk']);		
		$text="$ps Tahun Masuk $tahunmasuk";
		return $text;
	}
	public function changeTbTahunMasuk($sender,$param) {					
		$_SESSION['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
        $_SESSION['currentPageKonversiMatakuliah']['kjur']=$this->tbCmbPs->Text;
        $this->lblModulHeader->Text=$this->getInfoToolbar();
        $this->populateData();
	}
    public function searchRecord ($sender,$param) {
		$_SESSION['currentPageKonversiMatakuliah']['search']=true;
		$this->populateData($_SESSION['currentPageKonversiMatakuliah']['search']);
	}	
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKonversiMatakuliah']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageKonversiMatakuliah']['search']);
	}		
	public function populateData ($search=false) {			
		$kjur=$_SESSION['currentPageKonversiMatakuliah']['kjur'];
		$tahun_masuk=$_SESSION['tahun_masuk'];		
        if ($search) {
            $txtsearch=$this->txtKriteria->Text;
            $str = "SELECT iddata_konversi,nama,alamat,no_telp FROM data_konversi2 WHERE perpanjangan=0";
            switch ($this->cmbKriteria->Text) {                                
                case 'nama' :
                    $cluasa="AND nama LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("data_konversi2 WHERE perpanjangan=0 $cluasa",'iddata_konversi');
                    $str = "$str $cluasa";
                break;
            }            			
        }else{
            $jumlah_baris=$this->DB->getCountRowsOfTable("data_konversi2 WHERE kjur='$kjur' AND tahun='$tahun_masuk' AND perpanjangan=0",'iddata_konversi');
			$str = "SELECT iddata_konversi,nama,alamat,no_telp FROM data_konversi2 WHERE kjur='$kjur' AND tahun='$tahun_masuk' AND perpanjangan=0";
        }			
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPage']['page_num'];
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPage']['page_num']=0;}
		$str = $str . " ORDER BY nama ASC LIMIT $offset,$limit";		
		$this->DB->setFieldTable(array('iddata_konversi','nama','alamat','no_telp'));
		$r = $this->DB->getRecord($str,$offset+1);
		$result=array();        
        while (list($k,$v)=each($r)) {             
            $iddata_konversi=$v['iddata_konversi'];
            $v['jumlahmatkul']=$this->DB->getCountRowsOfTable("nilai_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $v['jumlahsks']=$this->DB->getSumRowsOfTable('sks',"v_konversi2 WHERE iddata_konversi=$iddata_konversi");
            $result[$k]=$v;
        }
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS); 
	}	
	public function setDataBound ($sender,$param) {
		$item=$param->Item;
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {						
			if ($this->DB->checkRecordIsExist('iddata_konversi','data_konversi',$item->DataItem['iddata_konversi'])) {				
				$item->btnDelete->Enabled=false;				
			}
		}
	}		
	public function addProcess ($sender,$param) {			
		$this->idProcess = 'add';
        $this->lblAddKurikulum->Text=$this->Nilai->getKurikulumName($_SESSION['kjur']);
        $jenjang=$this->DMaster->getListJenjang();
        $this->cmbAddJenjang->DataSource=$jenjang;
        $this->cmbAddJenjang->Text='E';
        $this->cmbAddJenjang->dataBind();

        $idkur=$this->Nilai->getIDKurikulum($_SESSION['kjur']);
        $str = "SELECT kmatkul,nmatkul,sks,semester FROM matakuliah WHERE idkur=$idkur ORDER BY (semester+0),kmatkul ASC";
		$this->DB->setFieldTable (array('kmatkul','nmatkul','sks','semester'));
		$listMatkul = $this->DB->getRecord($str);				
		
		$_SESSION['currentPageKonversiMatakuliah']['daftarmatkul']=$listMatkul;
		$this->RepeaterAddS->dataSource=$listMatkul;
		$this->RepeaterAddS->dataBind();
	}	
	public function saveData ($sender,$param) {
		if ($this->page->isValid) {		
            $this->idProcess='add';
            $nim_asal=strtoupper($this->txtAddNimAsal->Text);					
            $nama=addslashes(strtoupper($this->txtAddNama->Text));
            $alamat=strtoupper($this->txtAddAlamat->Text);
            $notelp=addslashes($this->txtAddNoTelp->Text);	
            $kode_pt_asal=addslashes($this->txtAddKodePtAsal->Text);
            $pt_asal=addslashes(strtoupper($this->txtAddNamaPtAsal->Text));
            $kode_ps_asal=addslashes($this->txtAddKodePsAsal->Text);
            $ps_asal=addslashes(strtoupper($this->txtAddNamaPsAsal->Text));	
            $kjenjang=$this->cmbAddJenjang->Text;            
			$kjur=$_SESSION['kjur'];
            $tahun_masuk=$_SESSION['tahun_masuk'];
			$idkur=$this->Nilai->getIDKurikulum($kjur);
            $i=1;
            try {
                $str="INSERT INTO data_konversi2 (iddata_konversi,nama,alamat,no_telp,nim_asal,kode_pt_asal,nama_pt_asal,kjenjang,kode_ps_asal,nama_ps_asal,tahun,kjur,idkur,date_added,date_modified) VALUES ";
                $str=$str . " (NULL,'$nama','$alamat','$notelp','$nim_asal','$kode_pt_asal','$pt_asal','$kjenjang','$kode_ps_asal','$ps_asal','$tahun_masuk',$kjur,$idkur,NOW(),NOW())";
                $this->DB->query ('BEGIN');
                if ($this->DB->insertRecord($str)) {						
                    $iddata_konversi=$this->DB->getLastInsertID();                
                    foreach ($this->RepeaterAddS->Items As $inputan) {
                        if ($inputan->txtMatkulAsal->Text !== ''&& $inputan->txtSksAsal->Text !=='' && $inputan->cmbNilaiAsal->Text !=='') {
                            $str = 'INSERT INTO nilai_konversi2 (idnilai_konversi,iddata_konversi,kmatkul,kmatkul_asal,matkul_asal,sks_asal,n_kual) VALUES ';					
                            $kmatkul=$_SESSION['currentPageKonversiMatakuliah']['daftarmatkul'][$i]['kmatkul'];
                            $kmatkul_asal=strtoupper(trim($inputan->txtKMatkulAsal->Text));
                            $matkul_asal=addslashes(strtoupper(trim($inputan->txtMatkulAsal->Text)));
                            $nilai_asal=$inputan->cmbNilaiAsal->Text;
                            $sks_asal=addslashes($inputan->txtSksAsal->Text);  
                            $str = $str . " (NULL,'$iddata_konversi','$kmatkul','$kmatkul_asal','$matkul_asal','$sks_asal','$nilai_asal')";
                            $this->DB->insertRecord($str);					
                        }
                        $i++;
                    }
                    $this->DB->query('COMMIT');
                    $this->redirect('spmb.KonversiMatakuliah',true);
                }else {
                    $this->DB->query('ROLLBACK');                
                }       
            } catch (Exception $ex) {
                $nmatkul=$_SESSION['currentPageKonversiMatakuliah']['daftarmatkul'][$i]['nmatkul'];
                $this->lblContentMessageError->Text="Matakuliah $nmatkul dengan kode $kmatkul_before belum terdaftar di Kurikulum saat ini. Mohon untuk ditambahkan di Data Master -> Matakuliah";
                $this->modalMessageError->show(); 
            }			
		}
	}	
	
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';        
		$iddata_konversi=$sender->getId()=='btnDelete' ?$this->hiddenid->Value :$this->getDataKeyField($sender,$this->RepeaterS);		
		$this->hiddenid->Value=$iddata_konversi;		
		//load view
        $str = "SELECT dk.iddata_konversi,dk.nama,dk.alamat,dk.no_telp,dk.nim_asal,dk.kode_pt_asal,dk.nama_pt_asal,dk.kjenjang,dk.kode_ps_asal,dk.nama_ps_asal,dk.tahun,dk.kjur,dk.idkur FROM data_konversi2 dk WHERE dk.iddata_konversi=$iddata_konversi";
        $this->DB->setFieldTable(array('iddata_konversi','nama','alamat','no_telp','nim_asal','kode_pt_asal','nama_pt_asal','kjenjang','kode_ps_asal','nama_ps_asal','tahun','kjur','idkur'));
        $r = $this->DB->getRecord($str);			
        $dataView=$r[1];		
		$this->txtEditNimAsal->Text = $dataView['nim_asal'];
		$this->txtEditNama->Text = $dataView['nama'];
		$this->txtEditAlamat->Text = $dataView['alamat'];
		$this->txtEditNoTelp->Text = $dataView['no_telp'];
		$this->txtEditKodePtAsal->Text = $dataView['kode_pt_asal'];
		$this->txtEditNamaPtAsal->Text = $dataView['nama_pt_asal'];
        $jenjang=$this->DMaster->getListJenjang();	
        $this->cmbEditJenjang->DataSource=$jenjang;			
        $this->cmbEditJenjang->dataBind();
		$this->cmbEditJenjang->Text=$dataView['kjenjang'];
		$this->txtEditKodePsAsal->Text = $dataView['kode_ps_asal'];
		$this->txtEditNamaPsAsal->Text = $dataView['nama_ps_asal'];
		
		$this->cmbEditTahunAkademik->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA(),'none');			
		$this->cmbEditTahunAkademik->Text=$dataView['tahun'];;			
		$this->cmbEditTahunAkademik->dataBind();
		
		$this->cmbEditNamaPsTujuan->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
		$this->cmbEditNamaPsTujuan->Text=$dataView['kjur'];
        $this->cmbEditNamaPsTujuan->Enabled=false;
		$this->cmbEditNamaPsTujuan->dataBind();
        
		$this->lblEditKurikulum->Text=$this->Nilai->getKurikulumName($dataView['kjur']);
		$this->txtEditIdkur->Value=$this->Nilai->getIDKurikulum($dataView['kjur']);
        
		$nilai=$this->Nilai->getNilaiKonversi($iddata_konversi,$dataView['idkur']);		
		$_SESSION['currentPageKonversiMatakuliah']['daftarmatkul']=$nilai;
		$this->RepeaterEditS->dataSource=$nilai;
		$this->RepeaterEditS->dataBind();
	}
    public function updateData ($sender,$param) {
		if ($this->page->isValid) {						
            $this->idProcess='edit';
            $i=1;         
            try {
                $idkur=$this->txtEditIdkur->Value;
                $iddata_konversi=$this->hiddenid->Value;
                $nim_asal=strtoupper($this->txtEditNimAsal->Text);					
                $nama=addslashes(strtoupper($this->txtEditNama->Text));
                $alamat=strtoupper($this->txtEditAlamat->Text);
                $notelp=addslashes($this->txtEditNoTelp->Text);	
                $kode_pt_asal=addslashes($this->txtEditKodePtAsal->Text);
                $pt_asal=addslashes(strtoupper($this->txtEditNamaPtAsal->Text));
                $kode_ps_asal=addslashes($this->txtEditKodePsAsal->Text);
                $ps_asal=addslashes(strtoupper($this->txtEditNamaPsAsal->Text));	
                $kjenjang=$this->cmbEditJenjang->Text;
                $ta=$this->cmbEditTahunAkademik->Text;            
                $this->DB->query('BEGIN');
                $str = "UPDATE data_konversi2 SET nim_asal='$nim_asal',nama='$nama',alamat='$alamat',no_telp='$notelp',kode_pt_asal='$kode_pt_asal',nama_pt_asal='$pt_asal',kjenjang='$kjenjang',kode_ps_asal='$kode_ps_asal',nama_ps_asal='$ps_asal',tahun='$ta',date_modified=NOW() WHERE iddata_konversi='$iddata_konversi'";                
                if ($this->DB->updateRecord($str)) {                           
                    foreach ($this->RepeaterEditS->Items As $inputan) {
                        if ($inputan->txtMatkulAsal->Text !== ''&& $inputan->txtSksAsal->Text !=='' && $inputan->cmbNilaiAsal->Text !=='') {					
                            $idnilaikonversi=$inputan->hiddenidnilaikonversi->Value;
                            $kmatkul_before=$this->Nilai->getKMatkul($_SESSION['currentPageKonversiMatakuliah']['daftarmatkul'][$i]['kmatkul']);
                            $kmatkul=$idkur.'_'.$kmatkul_before;
                            $kmatkul_asal=strtoupper(trim($inputan->txtKMatkulAsal->Text));						
                            $matkul_asal=strtoupper(trim($inputan->txtMatkulAsal->Text));						
                            $nilai_asal=$inputan->cmbNilaiAsal->Text;
                            $sks_asal=addslashes($inputan->txtSksAsal->Text);                        
                            if ($idnilaikonversi == '') {
                                $str = 'INSERT INTO nilai_konversi2 (idnilai_konversi,iddata_konversi,kmatkul_asal,kmatkul,matkul_asal,sks_asal,n_kual) VALUES ';											
                                $str = $str . " (NULL,'$iddata_konversi','$kmatkul_asal','$kmatkul','$matkul_asal','$sks_asal','$nilai_asal')";														
                                $this->DB->insertRecord($str);
                            }else {                                                        
                                $str = "UPDATE nilai_konversi2 SET kmatkul='$kmatkul',kmatkul_asal='$kmatkul_asal',matkul_asal='$matkul_asal',sks_asal='$sks_asal',n_kual='$nilai_asal' WHERE idnilai_konversi=$idnilaikonversi";							
                                $this->DB->updateRecord($str);                                    
                            }
                        }
                        $i++;
                    }
                    $this->DB->query('COMMIT');
                }else {
                    $this->DB->query('ROLLBACK');
                }
    			$this->redirect('spmb.KonversiMatakuliah',true);
            } catch (Exception $ex) {
                $nmatkul=$_SESSION['currentPageKonversiMatakuliah']['daftarmatkul'][$i]['nmatkul'];
                $this->lblContentMessageError->Text="Matakuliah $nmatkul dengan kode $kmatkul_before belum terdaftar di Kurikulum saat ini. Mohon untuk ditambahkan di Data Master -> Matakuliah";
                $this->modalMessageError->show();                
            }				
		}
	}
    public function deleteNilai ($sender,$param) {			
		$this->idProcess='edit';
		$idnilai_konversi=$sender->CommandParameter;		
		$this->DB->deleteRecord("nilai_konversi2 WHERE idnilai_konversi='$idnilai_konversi'");			
		$this->editRecord($sender, $param);
	}
	public function deleteRecord ($sender,$param) {			
		$iddata_konversi=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("data_konversi2 WHERE iddata_konversi='$iddata_konversi'");
		$this->redirect('spmb.KonversiMatakuliah',true);
	}	
    public function printOut ($sender,$param) {	
        $this->createObj('reportnilai');             
        $this->linkOutput->Text='';
        $this->linkOutput->NavigateUrl='#';        
        
    }
}
