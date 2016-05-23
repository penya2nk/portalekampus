<?php
prado::using ('Application.logic.Logic_Report');
class Logic_ReportAkademik extends Logic_Report {	    
	public function __construct ($db) {
		parent::__construct ($db);	        
	}
    /**
     * digunakan untuk mencetak data master matakuliah
     */
    public function printMatakuliah ($objDemik) {        
        $kjur=$this->dataReport['kjur'];
        $idkur=$this->dataReport['idkur'];        
        $nama_ps=$this->dataReport['nama_ps'];        
        $str = "SELECT ta FROM kurikulum WHERE idkur=$idkur";
        $this->db->setFieldTable(array('ta'));
        $data = $this->db->getRecord($str);
        $tahun_kurikulum=$data[1]['ta'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :                
                $this->setHeaderPT('Q'); 
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:P7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","KURIKULUM TAHUN $tahun_kurikulum");                                
                
                $sheet->mergeCells("A8:Q8");
                $sheet->setCellValue("A8","PROGRAM STUDI $nama_ps");                                
                $sheet->getRowDimension(8)->setRowHeight(20);
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:Q8")->applyFromArray($styleArray);
                
                $sheet->getRowDimension(10)->setRowHeight(20);                                                
                
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(23);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(3);
                $sheet->getColumnDimension('L')->setWidth(12);
                $sheet->getColumnDimension('M')->setWidth(23);
                $sheet->getColumnDimension('Q')->setWidth(20);
                
                //field of column ganjil				
				$sheet->setCellValue('A10','SMT');				
				$sheet->setCellValue('B10','NO');
				$sheet->setCellValue('C10','KODE MK');				
				$sheet->mergeCells('D10:F10');
				$sheet->setCellValue('D10','MATA KULIAH');				
				$sheet->setCellValue('G10','SKS');				
				$sheet->setCellValue('H10','KETERANGAN');				
				
				//field of column genap				
				$sheet->setCellValue('J10','SMT');				
				$sheet->setCellValue('K10','NO');
				$sheet->setCellValue('L10','KODE MK');				
				$sheet->mergeCells('M10:O10');
				$sheet->setCellValue('M10','MATA KULIAH');				
				$sheet->setCellValue('P10','SKS');				
				$sheet->setCellValue('Q10','KETERANGAN');				
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $sheet->getStyle("A10:Q10")->applyFromArray($styleArray);
                $sheet->getStyle("A10:Q10")->getAlignment()->setWrapText(true);
                
                $row_ganjil=11;
				$row_genap=11;															
												
				$tambah_ganjil_row=false;		
				$tambah_genap_row=false;		
                $str = "SELECT m.kmatkul,m.nmatkul,m.sks,m.semester,m.idkonsentrasi,k.nama_konsentrasi,m.ispilihan,m.islintas_prodi FROM matakuliah m LEFT JOIN konsentrasi k ON (k.idkonsentrasi=m.idkonsentrasi) WHERE idkur=$idkur ORDER BY semester,kmatkul ASC";			                
                $this->db->setFieldTable(array('kmatkul','nmatkul','sks','semester','idkonsentrasi','nama_konsentrasi','ispilihan','islintas_prodi','aktif'));
                $data = $this->db->getRecord($str);
                
                $smt = $objDemik->getSemesterMatakuliahRomawi();
				for ($i=1; $i <= 8; $i+=1) {					
					if ($i%2==0) {//genap
						$tambah_genap_row=true;
						$no_semester=1;
						$row_smt_awal=$row_genap;												
						$ada_matkul=true;						
						$genap_total_sks=0;								
						foreach ($data as $k=>$v) {														
							if ($v['semester']==$i) {								
								if ($v['kmatkul'] == '') {
									$ada_matkul=false;
								}else {							                                    
									$sks=$v['sks'];									
									$sheet->setCellValue("K$row_genap",$no_semester);
									$sheet->setCellValue("L$row_genap",$objDemik->getKMatkul($v['kmatkul']));
									$sheet->mergeCells("M$row_genap:O$row_genap");
									$sheet->setCellValue("M$row_genap",$v['nmatkul']);
									$sheet->setCellValue("P$row_genap",$sks);
                                    $keterangan='-';
                                    if ($v['idkonsentrasi'] == 0) {
                                        if($v['islintas_prodi'] == 1){
                                            $keterangan='Matkul Lintas Prodi';                                            
                                        }elseif($v['ispilihan'] == 1) {
                                            $keterangan='Matkul Pilihan';                                            
                                        }
                                    }else{
                                        $keterangan='Matkul Konsentrasi';                                        
                                    }
									$sheet->setCellValue("Q$row_genap",$keterangan);
									$genap_total_sks += $sks;																	
								}
								$no_semester+=1;								
								$row_genap+=1;												
							}						
						}			
						if ($ada_matkul) {
							if ($row_genap <= $row_ganjil) {
								$row_genap=($row_genap+($row_ganjil-$row_genap))-1;
							}							
							$sheet->mergeCells("M$row_genap:O$row_genap");
							$sheet->setCellValue("M$row_genap",'Jumlah SKS');							
							$sheet->setCellValue("P$row_genap",$genap_total_sks);							
						
							$row_genap+=1;
							$row_smt_akhir=$row_genap-1;							
							
                            $sheet->mergeCells("J$row_smt_awal:J$row_smt_akhir");
							$sheet->setCellValue("J$row_smt_awal",$smt[$i]);
						}						
					}else {//ganjil				
						$tambah_ganjil_row=true;						
						$no_semester=1;
						$row_smt_awal=$row_ganjil;
						$ada_matkul=true;										
						$ganjil_total_sks=0;								
						foreach ($data as $r=>$s) {												
							if ($s['semester']==$i) {	
								if ($s['kmatkul'] == '') {
									$ada_matkul=false;
								}else {												
									$sks=$s['sks'];									
									$sheet->setCellValue("B$row_ganjil",$no_semester);
									$sheet->setCellValue("C$row_ganjil",$objDemik->getKMatkul($s['kmatkul']));
									$sheet->mergeCells("D$row_ganjil:F$row_ganjil");
									$sheet->setCellValue("D$row_ganjil",$s['nmatkul']);
									$sheet->setCellValue("G$row_ganjil",$sks);
                                    $keterangan='-';
                                    if ($s['idkonsentrasi'] == 0) {
                                        if($s['islintas_prodi'] == 1){
                                            $keterangan='Matkul Lintas Prodi';                                            
                                        }elseif($s['ispilihan'] == 1) {
                                            $keterangan='Matkul Pilihan';                                            
                                        }
                                    }else{
                                        $keterangan='Matkul Konsentrasi';                                        
                                    }
									$sheet->setCellValue("H$row_ganjil",$keterangan);									
									$ganjil_total_sks += $sks;																	
								}
                                $sheet->getRowDimension($row_ganjil)->setRowHeight(22);
								$no_semester+=1;								
								$row_ganjil+=1;
							}						
						}
						if ($ada_matkul) {							
                            $sheet->getRowDimension($row_ganjil)->setRowHeight(22);
							$sheet->mergeCells("D$row_ganjil:F$row_ganjil");
							$sheet->setCellValue("D$row_ganjil",'Jumlah SKS');							
							$sheet->setCellValue("G$row_ganjil",$ganjil_total_sks);							
							
							$row_ganjil+=1;							
							$row_smt_akhir=$row_ganjil-1;
                            $sheet->mergeCells("A$row_smt_awal:A$row_smt_akhir");
							$sheet->setCellValue("A$row_smt_awal",$smt[$i]);						
						}																	
					}
					if ($tambah_ganjil_row && $tambah_genap_row) {						
						$sheet->getRowDimension($row_ganjil)->setRowHeight(3);
						$sheet->mergeCells("A$row_ganjil:Q$row_ganjil");						
						$row_ganjil+=1;
						$row_genap+=1;
						$tambah_ganjil_row=false;
						$tambah_genap_row=false;
					}
				}	
				$row_akhir = (($row_ganjil <= $row_genap)?$row_genap:$row_ganjil)-1;
                $sheet->mergeCells("I9:I$row_akhir");
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A11:Q$row_akhir")->applyFromArray($styleArray);
                $sheet->getStyle("A11:Q$row_akhir")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                                );																					 
                $sheet->getStyle("A11:C$row_akhir")->applyFromArray($styleArray);
                $sheet->getStyle("G11:H$row_akhir")->applyFromArray($styleArray);
                $sheet->getStyle("J11:L$row_akhir")->applyFromArray($styleArray);
                $sheet->getStyle("P11:Q$row_akhir")->applyFromArray($styleArray);
                
                $this->printOut('daftarmatakuliah');
            break;
            case 'pdf' :
                                                
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Daftar Matakuliah $nama_ps");
    }
}
?>

