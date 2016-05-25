<?php
prado::using ('Application.logic.Logic_Report');
class Logic_ReportKuesioner extends Logic_Report {	    
	public function __construct ($db) {
		parent::__construct ($db);	        
	}    
    /**
     * digunakan untuk memprint summary kuesioner dosen
     * @param type $objKuesioner object
     */
    public function printSummaryKuesioner ($objKuesioner) {
        $ta=$this->dataReport['ta'];        
        $idsmt=$this->dataReport['semester'];
        $kjur=$this->dataReport['kjur'];
        $nama_tahun=$this->dataReport['nama_tahun'];
        $nama_semester=$this->dataReport['nama_semester'];
        $nama_ps = $this->dataReport['nama_ps'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :          
                $this->setHeaderPT('J'); 
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:J7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","SUMMARY KUESIONER DOSEN T.A $nama_tahun SEMESTER $nama_semester");                                
                
                $sheet->mergeCells("A8:J8");
                $sheet->setCellValue("A8","PROGRAM STUDI $nama_ps");                                
                $sheet->getRowDimension(8)->setRowHeight(20);
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:J8")->applyFromArray($styleArray);
                
                $sheet->getRowDimension(10)->setRowHeight(20);              
                
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(14);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(17);
                $sheet->getColumnDimension('H')->setWidth(40);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                                
                $sheet->setCellValue('A10','NO');				
                $sheet->setCellValue('B10','ID');
                $sheet->setCellValue('C10','KODE');				                        
                $sheet->setCellValue('D10','NAMA MATAKULIAH');				
                $sheet->setCellValue('E10','SKS');				
                $sheet->setCellValue('F10','SMT');				
                $sheet->setCellValue('G10','NIDN');				
                $sheet->setCellValue('H10','NAMA DOSEN');				
                $sheet->setCellValue('I10','TOTAL NILAI');				
                $sheet->setCellValue('J10','HASIL');				                                    
                
                $styleArray=array(								
                                    'font' => array('bold' => true),
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A10:J10")->applyFromArray($styleArray);
                $sheet->getStyle("A10:J10")->getAlignment()->setWrapText(true);
                
                
                
                $str="SELECT vpp.idpengampu_penyelenggaraan,vpp.idpenyelenggaraan,kmatkul,nmatkul,sks,semester,iddosen,nidn,nama_dosen FROM v_pengampu_penyelenggaraan vpp WHERE EXISTS (SELECT 1 FROM kuesioner_jawaban WHERE idpengampu_penyelenggaraan=vpp.idpengampu_penyelenggaraan) AND vpp.idsmt='$idsmt' AND vpp.tahun='$ta' AND vpp.kjur='$kjur' ORDER BY nmatkul ASC";                
                $this->db->setFieldTable (array('idpengampu_penyelenggaraan','idpenyelenggaraan','kmatkul','nmatkul','sks','semester','iddosen','nidn','nama_dosen','jumlahmhs'));			
                $r=$this->db->getRecord($str);	                
                $row=11;  
                while (list($k,$v)=each($r)) {
                    $sheet->setCellValue("A$row",$v['no']);				           
                    $idpengampu_penyelenggaraan=$v['idpengampu_penyelenggaraan'];                                    
                    $sheet->setCellValue("B$row",$idpengampu_penyelenggaraan);
                    $sheet->setCellValue("C$row",$objKuesioner->getKMatkul($v['kmatkul']));
                    $sheet->setCellValue("D$row",$v['nmatkul']);	                        
                    $sheet->setCellValue("E$row",$v['sks']);				
                    $sheet->setCellValue("F$row",$v['semester']);				
                    $sheet->setCellValue("G$row",$v['nidn']);				
                    $sheet->setCellValue("H$row",$v['nama_dosen']);				
                    
                    $str="SELECT n_kual,total_nilai FROM kuesioner_hasil WHERE idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan";				
                    $this->db->setFieldTable (array('n_kual','total_nilai'));			
                    $r2=$this->db->getRecord($str);	
                    if (isset($r2[1])) {
                        $sheet->setCellValue("I$row",$r2[1]['total_nilai']);
                        $sheet->setCellValue("J$row",$r2[1]['n_kual']);                        
                    }else{
                        $sheet->setCellValue("I$row",'N.A');
                        $sheet->setCellValue("J$row",'N.A');                        
                    }      
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
                
                $sheet->getStyle("D11:D$row")->applyFromArray($styleArray);
                $sheet->getStyle("H11:H$row")->applyFromArray($styleArray);
                
                $this->printOut("summarykuesioner");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Hasil Kuesioner Dosen T.A $nama_tahun Semester $nama_semester");    
    }
    /**
     * digunakan untuk memprint Data kuesioner dosen
     * @param type $objKuesioner object
     */
    public function printKuesionerDosen ($objKuesioner) {
        $ta=$this->dataReport['tahun'];        
        $idsmt=$this->dataReport['idsmt'];        
        $nama_tahun=$this->dataReport['nama_tahun'];
        $nama_semester=$this->dataReport['nama_semester'];
        $nmatkul = $this->dataReport['nmatkul'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :          
                $this->setHeaderPT('I'); 
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:I7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","DATA KUESIONER DOSEN T.A $nama_tahun SEMESTER $nama_semester");                                
                
                $sheet->mergeCells("A8:I8");
                $sheet->setCellValue("A8","MATAKULIAH $nmatkul");                                
                $sheet->getRowDimension(8)->setRowHeight(20);
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:I8")->applyFromArray($styleArray);
                
                
                
                $sheet->setCellValue('A10','NIDN');	
                $sheet->setCellValue('C10',$this->dataReport['nidn']);	
                $sheet->setCellValue('A11','NAMA DOSEN');				
                $sheet->setCellValue('C11',$this->dataReport['nama_dosen']);	
                $sheet->setCellValue('A12','KODE MATKUL');				
                $sheet->setCellValue('C12',$this->dataReport['kmatkul']);	
                $sheet->setCellValue('A13','NAMA MATKUL');				
                $sheet->setCellValue('C13',$this->dataReport['nmatkul']);	
                $sheet->setCellValue('A14','SKS');				
                $sheet->setCellValue('C14',$this->dataReport['sks']);	
                $sheet->setCellValue('A15','SEMESTER');			
                $sheet->setCellValue('C15',$this->dataReport['semester']);	
                $sheet->setCellValue('A16','PROGRAM STUDI');		
                $sheet->setCellValue('C16',$this->dataReport['nama_ps']);	
                
                $sheet->setCellValue('E10','JUMLAH MHS');	
                $sheet->setCellValue('G10',$this->dataReport['jumlah_mhs']);	
                $sheet->setCellValue('E11','JUMLAH SOAL');				
                $sheet->setCellValue('G11',$this->dataReport['jumlah_soal']);	
                $sheet->setCellValue('E12','TOTAL NILAI');				
                $sheet->setCellValue('G12',$this->dataReport['total_nilai']);	
                $sheet->setCellValue('E13','SKOR TERENDAH');			
                $sheet->setCellValue('G13',$this->dataReport['skor_terendah']);	
                $sheet->setCellValue('E14','SKOR TERTINGGI');				
                $sheet->setCellValue('G14',$this->dataReport['skor_tertinggi']);	
                $sheet->setCellValue('E15','INTERVAL');				
                $sheet->setCellValue('G15',$this->dataReport['intervals']);	
                $sheet->setCellValue('E16','KETERANGAN');				
                $sheet->setCellValue('G16',$this->dataReport['n_kual']);	
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );
                
                $sheet->getStyle("G10:G16")->applyFromArray($styleArray); 
                
                $sheet->getRowDimension(18)->setRowHeight(20);                              
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(60);
                $sheet->getColumnDimension('D')->setWidth(13);
                $sheet->getColumnDimension('E')->setWidth(13);
                $sheet->getColumnDimension('F')->setWidth(13);                
                $sheet->getColumnDimension('G')->setWidth(13);
                $sheet->getColumnDimension('H')->setWidth(13);
                $sheet->getColumnDimension('I')->setWidth(10);                
                                
                $sheet->setCellValue('A18','NO');				
                $sheet->setCellValue('B18','URUT');
                $sheet->setCellValue('C18','ASPEK YANG LAIN');				                        
                $sheet->setCellValue('D18','INDIKATOR KE 1');				
                $sheet->setCellValue('E18','INDIKATOR KE 2');				
                $sheet->setCellValue('F18','INDIKATOR KE 3');				
                $sheet->setCellValue('G18','INDIKATOR KE 4');				
                $sheet->setCellValue('H18','INDIKATOR KE 5');				                
                $sheet->setCellValue('I18','TOTAL');				                                    
                
                $styleArray=array(								
                                    'font' => array('bold' => true),
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A18:I18")->applyFromArray($styleArray);
                $sheet->getStyle("A18:I18")->getAlignment()->setWrapText(true);
                
                $idpengampu_penyelenggaraan=$this->dataReport['idpengampu_penyelenggaraan'];                
                $row=19;  
                $kelompok_pertanyaan=$this->dataReport['kelompok_pertanyaan'];
                $TotalIndikator1=0;
                $TotalIndikator2=0;
                $TotalIndikator3=0;
                $TotalIndikator4=0;
                $TotalIndikator5=0;
                while (list($idkelompok_pertanyaan,$nama_kelompok)=each($kelompok_pertanyaan)) {
                    $str = "SELECT idkuesioner,idkelompok_pertanyaan,pertanyaan,`orders`,date_added FROM kuesioner k WHERE tahun='$ta' AND idsmt='$idsmt' AND idkelompok_pertanyaan=$idkelompok_pertanyaan ORDER BY (orders+0) ASC";
                    $this->db->setFieldTable(array('idkuesioner','idkelompok_pertanyaan','pertanyaan','orders','date_added'));
                    $r=$this->db->getRecord($str);
                    $jumlah_r=count($r);
                    if ($jumlah_r > 0) {                        
                        $sheet->mergeCells("A$row:I$row");
                        $styleArray=array(								
                                    'font' => array('bold' => true));
                        $sheet->getStyle("A$row:I$row")->applyFromArray($styleArray);                        
                        $sheet->setCellValue("A$row",$nama_kelompok);
                        $row+=1;   
                        
                        $idkuesioner=$r[1]['idkuesioner'];                
                        $str="SELECT nilai_indikator,SUM(nilai_indikator) AS jumlah FROM kuesioner_jawaban kj,kuesioner_indikator ki WHERE ki.idindikator=kj.idindikator AND kj.idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan AND kj.idkuesioner=$idkuesioner GROUP BY nilai_indikator";
                        $this->db->setFieldTable(array('nilai_indikator','jumlah'));
                        $hasil_indikator=$this->db->getRecord($str);
                        $indikator1=0;
                        $indikator2=0;
                        $indikator3=0;
                        $indikator4=0;
                        $indikator5=0;
                        foreach ($hasil_indikator as $hasil) {
                            switch($hasil['nilai_indikator']) {
                                case 1 :
                                    $indikator1=$hasil['jumlah'];
                                break;
                                case 2 :
                                    $indikator2=$hasil['jumlah'];
                                break;
                                case 3 :
                                    $indikator3=$hasil['jumlah'];
                                break;
                                case 4 :
                                    $indikator4=$hasil['jumlah'];
                                break;
                                case 5 :
                                    $indikator5=$hasil['jumlah'];
                                break;
                            }
                        }
                        $sheet->getRowDimension($row)->setRowHeight(23);  
                        $sheet->setCellValue("A$row",$r[1]['no']);				                                   
                        $sheet->setCellValue("B$row",$r[1]['orders']);
                        $sheet->setCellValue("C$row",$r[1]['pertanyaan']);
                        $sheet->setCellValue("D$row",$indikator1);	                        
                        $sheet->setCellValue("E$row",$indikator2);	                        
                        $sheet->setCellValue("F$row",$indikator3);	                        
                        $sheet->setCellValue("G$row",$indikator4);	                        
                        $sheet->setCellValue("H$row",$indikator5);	                        
                        $total=$indikator1+$indikator2+$indikator3+$indikator4+$indikator5;
                        $sheet->setCellValue("I$row",$total);
                        $TotalIndikator1+=$indikator1;
                        $TotalIndikator2+=$indikator2;
                        $TotalIndikator3+=$indikator3;
                        $TotalIndikator4+=$indikator4;
                        $TotalIndikator5+=$indikator5;
                        next($r);         
                        $row+=1;
                        while (list($k,$v)=each($r)) {
                            $idkuesioner=$v['idkuesioner'];
                            $str="SELECT nilai_indikator,SUM(nilai_indikator) AS jumlah FROM kuesioner_jawaban kj,kuesioner_indikator ki WHERE ki.idindikator=kj.idindikator AND kj.idpengampu_penyelenggaraan=$idpengampu_penyelenggaraan AND kj.idkuesioner=$idkuesioner GROUP BY nilai_indikator";
                            $this->db->setFieldTable(array('nilai_indikator','jumlah'));
                            $hasil_indikator=$this->db->getRecord($str);
                            $indikator1=0;
                            $indikator2=0;
                            $indikator3=0;
                            $indikator4=0;
                            $indikator5=0;
                            foreach ($hasil_indikator as $hasil) {
                                switch($hasil['nilai_indikator']) {
                                    case 1 :
                                        $indikator1=$hasil['jumlah'];
                                    break;
                                    case 2 :
                                        $indikator2=$hasil['jumlah'];
                                    break;
                                    case 3 :
                                        $indikator3=$hasil['jumlah'];
                                    break;
                                    case 4 :
                                        $indikator4=$hasil['jumlah'];
                                    break;
                                    case 5 :
                                        $indikator5=$hasil['jumlah'];
                                    break;
                                }
                            }
                            $sheet->getRowDimension($row)->setRowHeight(23);  
                            $sheet->setCellValue("A$row",$v['no']);				                                   
                            $sheet->setCellValue("B$row",$v['orders']);
                            $sheet->setCellValue("C$row",$v['pertanyaan']);
                            $sheet->setCellValue("D$row",$indikator1);	                        
                            $sheet->setCellValue("E$row",$indikator2);	                        
                            $sheet->setCellValue("F$row",$indikator3);	                        
                            $sheet->setCellValue("G$row",$indikator4);	                        
                            $sheet->setCellValue("H$row",$indikator5);	                        
                            $total=$indikator1+$indikator2+$indikator3+$indikator4+$indikator5;
                            $TotalIndikator1+=$indikator1;
                            $TotalIndikator2+=$indikator2;
                            $TotalIndikator3+=$indikator3;
                            $TotalIndikator4+=$indikator4;
                            $TotalIndikator5+=$indikator5;
                            $sheet->setCellValue("I$row",$total);	  
                            $row+=1;
                        }                
                    }            
                }
                $sheet->getRowDimension($row)->setRowHeight(23);  
                $sheet->mergeCells("A$row:C$row");
                $sheet->setCellValue("A$row",'TOTAL');				                                                   
                $sheet->setCellValue("D$row",$TotalIndikator1);	                        
                $sheet->setCellValue("E$row",$TotalIndikator2);	                        
                $sheet->setCellValue("F$row",$TotalIndikator3);	                        
                $sheet->setCellValue("G$row",$TotalIndikator4);	                        
                $sheet->setCellValue("H$row",$TotalIndikator5);	                        
                $total=$TotalIndikator1+$TotalIndikator2+$TotalIndikator3+$TotalIndikator4+$TotalIndikator5;                
                $sheet->setCellValue("I$row",$total);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A19:I$row")->applyFromArray($styleArray);
                $sheet->getStyle("A19:I$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );
                
                $sheet->getStyle("C10:C$row")->applyFromArray($styleArray);                
                $row+=2;
                $skor_terendah=$this->dataReport['skor_terendah'];
                $interval=$this->dataReport['intervals'];
                $maks_sangatburuk=$skor_terendah+$interval;
                $maks_buruk=$maks_sangatburuk+$interval;
                $maks_sedang=$maks_buruk+$interval;
                $maks_baik=$maks_sedang+$interval;
                $maks_sangatbaik=$maks_baik+$maks_baik;
                
                $sheet->setCellValue("C$row","Interval yang terbentuk :");
                $row+=1;
                $sheet->setCellValue("C$row","SANGAT BURUK : $skor_terendah - $maks_sangatburuk");
                $row+=1;
                $sheet->setCellValue("C$row","BURUK : $maks_sangatburuk - $maks_buruk");
                $row+=1;
                $sheet->setCellValue("C$row","SEDANG: $maks_buruk - $maks_sedang");
                $row+=1;
                $sheet->setCellValue("C$row","BAIK: $maks_sedang - $maks_baik");
                $row+=1;
                $sheet->setCellValue("C$row","SANGAT BAIK: $maks_baik - $maks_sangatbaik");
                
                $this->printOut("datakuesionerdosen_$idpengampu_penyelenggaraan");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Hasil Kuesioner Dosen T.A $nama_tahun Semester $nama_semester");    
    }
}
?>

