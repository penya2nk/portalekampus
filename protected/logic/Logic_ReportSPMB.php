<?php
prado::using ('Application.logic.Logic_Report');
class Logic_ReportSPMB extends Logic_Report {	    
	public function __construct ($db) {
		parent::__construct ($db);	        
	}
    /**
     * digunakan untuk memprint formulir pendaftaran
     */
    public function printFormulirPendaftaran () {
        $no_formulir=$this->dataReport['no_formulir'];
        $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.idwarga,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND fp.no_formulir='$no_formulir'";
        $this->db->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','idwarga','idstatus','alamat_kantor','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt'));
        $r=$this->db->getRecord($str);
        
        $datamhs=$r[1];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :                
//                $this->printOut("khs_$nim");
            break;
            case 'pdf' :                
                $rpt=$this->rpt;
                $rpt->setTitle('Formulir Pendaftaran Mahasiswa');
                $rpt->setSubject('Formulir Pendaftaran Mahasiswa');
                $rpt->AddPage();                
                $this->setHeaderPT();

                $row=$this->currentRow;
                $row+=6;
                $rpt->SetFont ('helvetica','B',12);	
                $rpt->setXY(3,$row);			
                $kartu='Formulir Pendaftaran Mahasiswa Baru';
                $rpt->Cell(120,$row,$kartu,0,0,'C');

                $rpt->SetFont ('helvetica','',8);	
                $row+=15;
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'No Formulir',1,0);
                $rpt->Cell(80,5,': '.$datamhs['no_formulir'],1,0);

                $row+=5;						
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Nama Mahasiswa',1,0);
                $rpt->Cell(80,5,': '.$datamhs['nama_mhs'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Tempat Lahir',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['tempat_lahir'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Tanggal Lahir',1,0);				
                $tgl=$datamhs['tanggal_lahir']==''?': -':$this->tgl->tanggal('l, j F Y',$datamhs['tanggal_lahir'],1,0);
                $rpt->Cell(80,5,": $tgl",1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Jenis Kelamin',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['jk'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Agama',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['nama_agama'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Kewarganegaraan',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['idwarga'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Program Studi Pilihan Ke I',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['njur1'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Program Studi Pilihan Ke II',1,0);
                $rpt->Cell(80,5,': '.$datamhs['njur2'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Alamat Rumah',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['alamat_rumah'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'No. Telepon Rumah',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['telp_rumah'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Telepon HP',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['telp_hp'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Status Kepegawaian',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['idstatus'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'No Telepon Kantor',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['telp_kantor'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Pekerjaan Orang Tua',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['Nama Pekerjaan'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Pendidikan Terakhir',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['pendidikan_terakhir'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Jurusan',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['jurusan'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Kab/Kodya/Kota',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['Kota'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Provinsi',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['provinsi'],1,0);
                $row+=5;								
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Tahun',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['tahun_pa'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Jenis SLTA',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['jenis_slta'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Asal SLTA',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['asal_slta'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Status SLTA',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['status_slta'],1,0);
                $row+=5;				
                $rpt->setXY(3,$row);			
                $rpt->Cell(40,5,'Nomor Ijazah',1,0);				
                $rpt->Cell(80,5,': '.$datamhs['nomor_ijazah'],1,0);	                
                $this->printOut("formulirpendaftaran_$no_formulir");            
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Formulir Pendaftaran $no_formulir");
    }
    /**
     * digunakan untuk memprint Formulir Pendaftaran     
     * @param type $outputcompress none,zip,tar,etc
     * @param type $level 1 s.d 9
     */
    public function printFormulirPendaftaranAll ($outputcompress,$level=0) {  
        $kjur=$this->dataReport['kjur'];        
        $tahun_masuk=$this->dataReport['tahun_masuk'];
        $semester=$this->dataReport['semester'];
        $nama_tahun=$this->dataReport['nama_tahun'];
        $nama_semester=$this->dataReport['nama_semester'];
        
        $daftar_via=$this->dataReport['daftar_via'];                
        $offset=$this->dataReport['offset'];
        $limit=$this->dataReport['limit'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :                

            break;
            case 'pdf' :                               
                $str = "SELECT fp.no_formulir,fp.nama_mhs,fp.tempat_lahir,fp.tanggal_lahir,fp.jk,fp.idagama,a.nama_agama,fp.idwarga,fp.idstatus,fp.alamat_kantor,fp.alamat_rumah,fp.telp_rumah,fp.telp_kantor,fp.telp_hp,pm.email,fp.idjp,fp.pendidikan_terakhir,fp.jurusan,fp.kota,fp.provinsi,fp.tahun_pa,jp.nama_pekerjaan,fp.jenis_slta,fp.asal_slta,fp.status_slta,fp.nomor_ijazah,fp.kjur1,fp.kjur2,fp.idkelas,fp.waktu_mendaftar,fp.ta,fp.idsmt FROM formulir_pendaftaran fp,agama a,jenis_pekerjaan jp,profiles_mahasiswa pm WHERE fp.idagama=a.idagama AND fp.idjp=jp.idjp AND pm.no_formulir=fp.no_formulir AND ta='$tahun_masuk' AND idsmt='$semester' AND daftar_via='$daftar_via' AND (kjur1='$kjur' OR kjur2='$kjur') LIMIT $offset,$limit";
                $this->db->setFieldTable(array('no_formulir','nama_mhs','tempat_lahir','tanggal_lahir','jk','idagama','nama_agama','idwarga','idstatus','alamat_kantor','alamat_rumah','telp_rumah','telp_kantor','telp_hp','email','idjp','pendidikan_terakhir','jurusan','kota','provinsi','tahun_pa','nama_pekerjaan','jenis_slta','asal_slta','status_slta','nomor_ijazah','kjur1','kjur2','idkelas','waktu_mendaftar','ta','idsmt'));
                $r=$this->db->getRecord($str);
                
                while (list($k,$v)=each($r)) {
                    $datamhs=$v;								
                    if ($datamhs['waktu_mendaftar']=='0000-00-00 00:00:00') {							
                        $datamhs['tanggal_lahir']='-';
                        $datamhs['jk']='-';
                        $datamhs['nama_agama']='-';
                        $datamhs['idwarga']='-';														
                        $datamhs['idstatus']='-';		
                        $datamhs['nama_pekerjaan']='-';
                        $datamhs['tahun_pa']='-';
                        $datamhs['jenis_slta']='-';
                        $datamhs['status_slta']='-';
                    }
                    $this->setMode('pdf');
                    $rpt=$this->rpt;
                    $rpt->setTitle('Formulir Pendaftaran Mahasiswa');
                    $rpt->setSubject('Formulir Pendaftaran Mahasiswa');
                    $rpt->AddPage();                
                    $this->setHeaderPT();
                
                    $row=$this->currentRow;
                    $row+=6;
                    $rpt->SetFont ('helvetica','B',12);	
                    $rpt->setXY(3,$row);			
                    $kartu='Formulir Pendaftaran Mahasiswa Baru';
                    $rpt->Cell(120,$row,$kartu,0,0,'C');

                    $rpt->SetFont ('helvetica','',8);	
                    $row+=15;
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'No Formulir',1,0);
                    $rpt->Cell(80,5,': '.$datamhs['no_formulir'],1,0);

                    $row+=5;						
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Nama Mahasiswa',1,0);
                    $rpt->Cell(80,5,': '.$datamhs['nama_mhs'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Tempat Lahir',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['tempat_lahir'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Tanggal Lahir',1,0);				
                    $tgl=$datamhs['tanggal_lahir']==''?': -':$this->tgl->tanggal('l, j F Y',$datamhs['tanggal_lahir'],1,0);
                    $rpt->Cell(80,5,": $tgl",1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Jenis Kelamin',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['jk'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Agama',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['nama_agama'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Kewarganegaraan',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['idwarga'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Program Studi Pilihan Ke I',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['njur1'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Program Studi Pilihan Ke II',1,0);
                    $rpt->Cell(80,5,': '.$datamhs['njur2'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Alamat Rumah',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['alamat_rumah'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'No. Telepon Rumah',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['telp_rumah'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Telepon HP',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['telp_hp'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Status Kepegawaian',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['idstatus'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'No Telepon Kantor',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['telp_kantor'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Pekerjaan Orang Tua',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['Nama Pekerjaan'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Pendidikan Terakhir',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['pendidikan_terakhir'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Jurusan',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['jurusan'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Kab/Kodya/Kota',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['Kota'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Provinsi',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['provinsi'],1,0);
                    $row+=5;								
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Tahun',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['tahun_pa'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Jenis SLTA',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['jenis_slta'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Asal SLTA',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['asal_slta'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Status SLTA',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['status_slta'],1,0);
                    $row+=5;				
                    $rpt->setXY(3,$row);			
                    $rpt->Cell(40,5,'Nomor Ijazah',1,0);				
                    $rpt->Cell(80,5,': '.$datamhs['nomor_ijazah'],1,0);	
                    $no_formulir=$datamhs['no_formulir'];                    
                    $this->printOut("formulirpendaftaran_$no_formulir",true);                    
                    $filespdf["formulirpendaftaran_$no_formulir.pdf"]=$this->exportedDir['full_path']."formulirpendaftaran_$no_formulir.pdf";
                }              
                
                $this->setMode("pdf$outputcompress");                  
                $this->printOutArchive($filespdf, 'formulirpendaftaran', 'zip');
            break;
        }        
        $this->setLink($this->dataReport['linkoutput'],"Formulir Pendaftaran T.A $nama_tahun Semester $nama_semester");
    }    
    /**
     * digunakan untuk memprint nilai ujian
     * @param type $passinggrade
     * @param type $daftar_jurusan
     */
    public function printNilaiUjian ($passinggrade,$daftar_jurusan) {
        $kjur=$this->dataReport['kjur'];        
        $tahun_masuk=$this->dataReport['tahun_masuk'];
        
        $str_kjur=$kjur=='none'?' AND (num.kjur=0 OR num.kjur IS NULL)':" AND num.kjur=$kjur";	                
        $str = "SELECT fp.no_formulir,fp.nama_mhs,ku.tgl_ujian,ts.nama_tempat,num.kjur,num.jumlah_soal,num.jawaban_benar,num.jawaban_salah,num.nilai,fp.kjur1,fp.kjur2,num.passinggrade,num.kjur AS diterima_di_prodi FROM kartu_ujian ku JOIN formulir_pendaftaran fp ON (fp.no_formulir=ku.no_formulir) JOIN tempat_spmb ts ON (ku.idtempat_spmb=ts.idtempat_spmb) JOIN nilai_ujian_masuk num ON (ku.no_formulir=num.no_formulir) WHERE fp.ta='$tahun_masuk'$str_kjur ORDER BY nilai DESC,nama_mhs ASC";
        $this->db->setFieldTable(array('no_formulir','nama_mhs','tgl_ujian','jumlah_soal','jawaban_benar','jawaban_salah','nilai','kjur1','kjur2','passinggrade','diterima_di_prodi'));				
        $r = $this->db->getRecord($str);        
        
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :    
                $this->setHeaderPT('J');
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');   
                
                $sheet->mergeCells("A7:J7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","HASIL UJIAN PMB TAHUN MASUK $tahun_masuk");                                
                if ($kjur!= 'none'){
                    $sheet->mergeCells("A8:J8");
                    $nama_ps_label='PROGRAM STUDI ' .$daftar_jurusan[$kjur];
                    $sheet->setCellValue("A8",$nama_ps_label);                                
                    $sheet->getRowDimension(8)->setRowHeight(20);
                }                
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:J8")->applyFromArray($styleArray);
                $sheet->getRowDimension(10)->setRowHeight(25);
                
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);                
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(40);
                $sheet->getColumnDimension('J')->setWidth(40);
                                
                $sheet->setCellValue('A10','NO');				
                $sheet->setCellValue('B10','NO. FORMULIR');
                $sheet->setCellValue('C10','NAMA');				                        
                $sheet->setCellValue('D10','TANGGAL UJIAN');				
                $sheet->setCellValue('E10','JUMLAH SOAL');				
                $sheet->setCellValue('F10','JAWABAN BENAR');				
                $sheet->setCellValue('G10','JAWABAN SALAH');				
                $sheet->setCellValue('H10','NILAI');				
                $sheet->setCellValue('I10','PIL. 1');				
                $sheet->setCellValue('J10','PIL. 2');				                                    
                
                $styleArray=array(								
                                    'font' => array('bold' => true),
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A10:J10")->applyFromArray($styleArray);
                $sheet->getStyle("A10:J10")->getAlignment()->setWrapText(true);
                $row=11;                
                while (list($k,$v)=each($r)) { 
                    $sheet->setCellValue("A$row",$v['no']);		
                    $sheet->setCellValue("B$row",$v['no_formulir']);		
                    $sheet->setCellValue("C$row",$v['nama_mhs']);		
                    $sheet->setCellValue("D$row",$this->tgl->Tanggal('d F Y',$v['tgl_ujian']));		
                    $sheet->setCellValue("E$row",$v['jumlah_soal']);		
                    $sheet->setCellValue("F$row",$v['jawaban_benar']);		
                    $sheet->setCellValue("G$row",$v['jawaban_salah']);		
                    $sheet->setCellValue("H$row",$v['nilai']);		
                    if ($kjur=='none') {
                        $pil1='N.A';
                        $bool1=false;
                        if ($v['kjur1'] > 0) {                  
                            $nama_ps=$daftar_jurusan[$v['kjur1']];      
                            $bool1=($v['nilai'] >= $passinggrade[$v['kjur1']]);
                            $ket=$bool1 == true ? 'LULUS' : 'GAGAL';                            
                            $pil1="$ket ($nama_ps)";

                        }                       
                        $pil2='N.A';
                        $bool2=false;
                        if ($v['kjur2'] > 0) {
                            $nama_ps=$daftar_jurusan[$v['kjur2']];      
                            $bool2=($v['nilai'] >= $passinggrade[$v['kjur2']]);
                            $ket=$bool2 == true ? 'LULUS' : 'GAGAL';                            
                            $pil2="$ket ($nama_ps)";
                        }                   
                    }else{
                        $pil1='N.A';
                        if ($v['kjur1'] == $v['diterima_di_prodi']) {
                            $nama_ps=$daftar_jurusan[$v['diterima_di_prodi']];     
                            $ket='DI TERIMA';
                            $pil1="$ket ($nama_ps)";
                        }
                        $v['pil1']=$pil1;                
                        $pil2='N.A';
                        if ($v['kjur2'] == $v['diterima_di_prodi']) {
                            $nama_ps=$daftar_jurusan[$v['diterima_di_prodi']];     
                            $ket='DI TERIMA';
                            $pil2="$ket ($nama_ps)";
                        }                        
                    }
                    $sheet->setCellValue("I$row",$pil1);		
                    $sheet->setCellValue("J$row",$pil2);		
                    $row+=1;
                }
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A11:J$row")->applyFromArray($styleArray);
                $sheet->getStyle("A11:J$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );
                $sheet->getStyle("C11:C$row")->applyFromArray($styleArray);
                $sheet->getStyle("I11:J$row")->applyFromArray($styleArray);
                $this->printOut("hasilujianpmb_$tahun_masuk");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Hasil Ujian PMB Tahun Masuk $tahun_masuk $nama_ps_label");
    }
    /**
     * digunakan untuk mencetak penggunaan PIN
     */
    public function printPIN() {
        $pilihan=$this->dataReport['pilihan'];
        $tahun_masuk=$this->dataReport['tahun_masuk'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :    
                $this->setHeaderPT('G');                
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:G7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","DAFTAR PIN T.A $tahun_masuk");
                
                $styleArray=array(
								'font' => array('bold' => true,'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:A7")->applyFromArray($styleArray);
                
                $sheet->getRowDimension(9)->setRowHeight(25); 
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->setCellValue('B9','NO');
                $sheet->setCellValue('C9','NO. FORMULIR');
                $sheet->setCellValue('D9','PIN');
                $sheet->setCellValue('E9','NAMA');
                $sheet->setCellValue('F9','KETERANGAN');
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $sheet->getStyle("B9:F9")->applyFromArray($styleArray);
                $sheet->getStyle("B9:F9")->getAlignment()->setWrapText(true);
                    
                $str_display='';
                if ($pilihan=='terdaftar'){
                    $str_display='AND fp.no_formulir IS NOT NULL';
                }elseif ($pilihan=='belum_terdaftar'){
                    $str_display='AND fp.no_formulir IS NULL';
                }
                $str = "SELECT pin.no_pin,pin.no_formulir,fp.nama_mhs,fp.no_formulir AS ket FROM pin LEFT JOIN formulir_pendaftaran fp ON (fp.no_formulir=pin.no_formulir) WHERE pin.tahun_masuk=$tahun_masuk $str_display";
                $str = "$str  $str_display ORDER BY pin.no_formulir ASC";
                $this->db->setFieldTable(array('no_pin','no_formulir','nama_mhs','ket'));
                $r = $this->db->getRecord($str);
                $row=10;
                while (list($k,$v)=each ($r)) {            
                    $sheet->setCellValue("B$row",$v['no']);
                    $sheet->setCellValue("C$row",$v['no_formulir']);
                    $sheet->setCellValueExplicit("D$row",$v['no_pin'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $nama_mhs=$v['nama_mhs'] == '' ? 'N.A' : $v['nama_mhs'];
                    $sheet->setCellValue("E$row",$nama_mhs);
                    $ket = $v['ket'] == '' ? 'N.A' : 'TELAH DAFTAR';
                    $sheet->setCellValue("F$row",$ket);
                    $row+=1;
                } 
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("B10:F$row")->applyFromArray($styleArray);
                $sheet->getStyle("B10:F$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $sheet->getStyle("E10:E$row")->applyFromArray($styleArray);
                $this->printOut("daftarpin$tahun_masuk");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Daftar PIN Mahasiswa");
    }
    /**
     * digunakan untuk memprint kartu ujian PMB
     */
    public function printKartuUjianPMB() {
        switch ($this->getDriver()) {
            case 'pdf' :
                $rpt=$this->rpt;
                $rpt->setTitle('Kartu Ujian PMB');
				$rpt->setSubject('Kartu Ujian PMB');
                $rpt->AddPage();
				$this->setHeaderPT();
                
                $row=$this->currentRow;
				$row+=6;
				$rpt->SetFont ('helvetica','B',12);	
				$rpt->setXY(3,$row);			
				$rpt->Cell(0,$row,'KARTU UJIAN PMB',0,0,'C');
                
                $row+=20;
				$rpt->SetFont ('helvetica','B',8);	
				$rpt->setXY(3,$row);			
				$rpt->Cell(0,7,'No. Formulir','LT');
				$rpt->SetFont ('helvetica','',8);
                $rpt->setXY(38,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['no_formulir']);
                
                $rpt->SetFont ('helvetica','B',8);	
				$rpt->setXY(105,$row);			
				$rpt->Cell(0,7,'Tanggal');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(130,$row);			
				$rpt->Cell(0,7,': '.$this->tgl->Tanggal('l, d F Y',$this->dataReport['tanggal_ujian']),'TR');
                
                $row+=7;
				$rpt->setXY(3,$row);			
				$rpt->SetFont ('helvetica','B',8);	
				$rpt->Cell(0,7,'Nama Peserta','L');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(38,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['nama_mhs']);
                
                $rpt->SetFont ('helvetica','B',8);	
				$rpt->setXY(105,$row);			
				$rpt->Cell(0,7,'Jam');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(130,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['jam_mulai']. ' - '.$this->dataReport['jam_akhir'],'R');
                
                $row+=7;
				$rpt->setXY(3,$row);			
				$rpt->SetFont ('helvetica','B',8);	
				$rpt->Cell(0,7,'Prodi. Pilihan ke - 1','L');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(38,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['nama_ps1']);
                
                $rpt->SetFont ('helvetica','B',8);	
				$rpt->setXY(105,$row);			
				$rpt->Cell(0,7,'Ruangan');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(130,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['namaruang'],'R');
                
                $row+=7;
				$rpt->setXY(3,$row);			
				$rpt->SetFont ('helvetica','B',8);	
				$rpt->Cell(0,7,'Prodi. Pilihan ke - 2','BL');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(38,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['nama_ps2'],'B');
                
                $rpt->SetFont ('helvetica','B',8);	
				$rpt->setXY(105,$row);			
				$rpt->Cell(0,7,'Periode','B');
				$rpt->SetFont ('helvetica','',8);
				$rpt->setXY(130,$row);			
				$rpt->Cell(0,7,': '.$this->dataReport['nama_kegiatan'],'BR');
                
                $row+=20;
                $rpt->Image($this->dataReport['photo_profile'],15,$row,35,35,'','','',false,300,'',false,false,1);
                
                $rpt->SetFont ('helvetica','B',8);
                $rpt->setXY(105,$row);               				
                $rpt->Cell(105, 5, "Tanjungpinang, ................................................",0,0,'L');		

                $row+=5;
                $rpt->setXY(105,$row);			
                $rpt->Cell(100, 5, "Pengawas Ujian PMB",0,0,'L');
                
                $row+=17;
                $rpt->setXY(105,$row);			
                $rpt->Cell(100, 5, "(....................................) ",0,0,'L');
                
                $row+=20;
                $rpt->setXY(3,$row);
                $rpt->SetFont ('helvetica','B',6);
                $rpt->Cell(28, 5, "TATA TERTIB UJIAN PMB :",'B',0,'L');
                $row+=5;
                $rpt->SetFont ('helvetica','',6);
                $rpt->setXY(3,$row);
                $rpt->Cell(14, 5, '1. Pakaian ');
                $rpt->Cell(3, 5, ':');
                $rpt->Cell(40, 5, 'a. Baju kemeja putih, celana bahan hitam dan sepatu (laki-laki).');
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(14, 5, '');
                $rpt->Cell(3, 5, ':');
                $rpt->Cell(40, 5, 'b. Baju kemeja putih, rok / celana bahan hitam, dan sepatu (perempuan).');
                
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(50, 5, '2. Hadir  30 menit sebelum jadwal ujian berlangsung.');
                
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(50, 5, '3. Peserta diwajibkan membawa kartu ujian PMB.');
                
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(50, 5, '4. Jika tidak datang pada waktu yang ditetapkan, peserta wajib melaporkan kepada pengawas ujian untuk mendapatkan jadwal ujian yang baru.');
                
                $this->printOut("kartu_ujian_pmb");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Kartu Ujian PMB");
    }
    /**
     * digunakan untuk memprint KHS
     * @param type $objNilai object
     */
    public function printBeritaAcaraUjianSPMB ($objDMaster) {
        switch ($this->getDriver()) {
            case 'pdf' :
                $rpt=$this->rpt;
                $rpt->setTitle('Berita Acara Ujian SPMB');
				$rpt->setSubject('Berita Acara Ujian SPMB');
                $rpt->AddPage('P','LETTER');
				$this->setHeaderPT();
                
                $row=$this->currentRow;
				$row+=12;
				$rpt->SetFont ('helvetica','B',12);	
				$rpt->setXY(3,$row);			
				$rpt->Cell(0,6,'BERITA ACARA UJIAN SELEKSI PENERIMAAN MAHASISWA BARU',0,0,'C');
                $row+=6;
				$rpt->setXY(3,$row);			
				$rpt->Cell(0,6,'TAHUN AKADEMIK '.$this->dataReport['nama_tahun'],0,0,'C');
                
                $row+=8;
                $rpt->setXY(3,$row);
                $rpt->SetFont ('helvetica','',8);
                $hari_ujian=$this->tgl->tanggal ('l',$this->dataReport['tanggal_ujian']);
                $terbilang_tanggal=  ucwords($this->setup->toTerbilang($this->tgl->tanggal ('d',$this->dataReport['tanggal_ujian'])));
                $bulan=$this->tgl->tanggal ('F',$this->dataReport['tanggal_ujian']);
                $terbilang_tahun=  ucwords($this->setup->toTerbilang($this->tgl->tanggal ('Y',$this->dataReport['tanggal_ujian'])));
                $nama_pt=  ucwords(strtolower($this->setup->getSettingValue('nama_pt')));
                $txt="Pada hari ini <b>$hari_ujian</b> tanggal <b>$terbilang_tanggal</b> bulan <b>$bulan</b> tahun <b>$terbilang_tahun</b> telah dilaksanakan ujian seleksi penerimaan mahasiswa baru {$nama_pt}.";
                $rpt->MultiCell(0, 10, $txt, 0, 'J', 0, 1, '', '', true, 0, true, true, 0);
                
                $row+=10;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'1. Jenis Ujian');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,'Tertulis / Wawancara *)','B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'2. Jumlah Peserta');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,$this->dataReport['jumlah_peserta'],'B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'3. Nama / Nomor Ruangan');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,$this->dataReport['namaruang'],'B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'4. Jumlah yang Tidak Hadir');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,'','B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'5. Nomor Peserta yang Tidak Hadir');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,'1. ','B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'');
                $rpt->Cell(5,6,'');
                $rpt->Cell(0,6,'2. ','B');
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'');
                $rpt->Cell(5,6,'');
                $rpt->Cell(0,6,'3. ','B');
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'');
                $rpt->Cell(5,6,'');
                $rpt->Cell(0,6,'4. ','B');
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'');
                $rpt->Cell(5,6,'');
                $rpt->Cell(0,6,'5. ','B');
                
                $row+=6;
                $rpt->setXY(30,$row);
                $rpt->Cell(50,6,'6. Keterangan');
                $rpt->Cell(5,6,':');
                $rpt->Cell(0,6,'','B');
                
                $row+=10;
                $rpt->setXY(3,$row);
                $rpt->Cell(0,5,'Pengawas Ujian / Pewawancara *)');
                
                $row+=6;
                $rpt->setXY(3,$row);
                $rpt->Cell(20,6,'1. Nama');
                $rpt->Cell(5,6,':');
                $rpt->Cell(50,6,'','B');
                $rpt->Cell(35,6,'(');
                $rpt->Cell(0,6,')');
                $row+=6;
                $rpt->setXY(3,$row);
                $rpt->Cell(20,6,'2. Nama');
                $rpt->Cell(5,6,':');
                $rpt->Cell(50,6,'','B');
                $rpt->Cell(35,6,'(');
                $rpt->Cell(0,6,')');
                
                $row+=10;
                $rpt->setXY(3,$row);
                $rpt->Cell(0,5,'Daftar Peserta :');
                
                $row+=10;
                $rpt->SetFont ('helvetica','B',8);
                $rpt->setXY(3,$row);
                $rpt->Cell(30,12,'NO. FORMULIR',1,0,'C');
                $rpt->Cell(50,12,"NAMA PESERTA",1,0,'C');
                $rpt->Cell(24,12,'TANDA TANGAN',1,0,'C');
                $rpt->Cell(23,12,'NO. HP',1,0,'C');
                $rpt->Cell(25,12,'PILIHAN KELAS',1,0,'C');
                $rpt->Cell(46,6,'PILIHAN PRODI',1,0,'C');
                $row+=6;
                $rpt->setXY(155,$row);
                $rpt->Cell(23,6,'I',1,0,'C');
                $rpt->Cell(23,6,'II',1,0,'C');
                
                $idjadwal_ujian=$this->dataReport['idjadwal_ujian'];        
                $str = "SELECT pum.no_formulir,fp.nama_mhs,fp.idkelas,fp.kjur1,fp.kjur2,fp.telp_hp FROM peserta_ujian_pmb pum,formulir_pendaftaran fp,pin WHERE fp.no_formulir=pum.no_formulir AND pin.no_formulir=pum.no_formulir AND pum.idjadwal_ujian=$idjadwal_ujian ORDER BY fp.no_formulir ASC";
                $this->db->setFieldTable(array('no_formulir','telp_hp','nama_mhs','idkelas','kjur1','kjur2'));	
                $r=$this->db->getRecord($str);
                
                $rpt->SetFont ('helvetica','',8);
                $row+=6;
                while (list($k,$v)=each($r)) {                    
                    $rpt->setXY(3,$row);
                    $rpt->Cell(30,6,$v['no_formulir'],1,0,'C');
                    $rpt->Cell(50,6,$v['nama_mhs'],1);
                    $rpt->Cell(24,6,'',1);
                    $rpt->Cell(23,6,$v['telp_hp'],1,0,'C');
                    $rpt->Cell(25,6,$objDMaster->getNamaKelasByID($v['idkelas']),1,0,'C');
                    $rpt->Cell(23,6,$objDMaster->getNamaAliasProgramStudiByID($v['kjur1']),1,0,'C');
                    $rpt->Cell(23,6,$objDMaster->getNamaAliasProgramStudiByID($v['kjur2']),1,0,'C');
                    $row+=6;
                }
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->SetFont ('helvetica','B',8);
                $rpt->Cell(0,5,'Mengetahui',0,0,'C');
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(0,5,'Ketua '.$this->setup->getSettingValue('nama_pt_alias'),0,0,'C');
                $row+=15;
                $rpt->setXY(3,$row);
                $rpt->Cell(0,5,'ENDRI SANOPAKA, S.Sos., MPM',0,0,'C');
                $row+=5;
                $rpt->setXY(3,$row);
                $rpt->Cell(0,5,'Lektor NIDN : 1005118101',0,0,'C');
                
                $this->printOut("berita_acara_pmb");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Berita Acara Ujian SPMB");
    }
    
}
?>

