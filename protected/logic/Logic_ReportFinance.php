<?php
prado::using ('Application.logic.Logic_Report');
class Logic_ReportFinance extends Logic_Report {	    
	public function __construct ($db) {
		parent::__construct ($db);	        
    }    
    /**
     * Digunakan untuk mencetak Piutang Jangka Pendek
     * @param type $objFinance object
     * @param type $objDMaster object
     */
    public function printPiutangJangkaPendek($objFinance,$objDMaster)  {
        $kjur=$this->dataReport['kjur'];
        $nama_ps=$this->dataReport['nama_ps'];
        $tahun_masuk=$this->dataReport['tahun_masuk'];
        $nama_tahun_masuk=$this->dataReport['nama_tahun_masuk'];
        $kelas=$this->dataReport['kelas'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :              
                $this->setHeaderPT('L');
                
                $sheet=$this->rpt->getActiveSheet();
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:L7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->setCellValue("A7","LAPORAN PIUTANG JANGKA PENDEK");
                $sheet->mergeCells("A8:M8");
                $sheet->setCellValue("A8","PROGRAM STUDI $nama_ps TAHUN MASUK $nama_tahun_masuk");                                
                $sheet->getRowDimension(8)->setRowHeight(20);
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 16),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:L8")->applyFromArray($styleArray);
                
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('I')->setWidth(14);
                $sheet->getColumnDimension('J')->setWidth(17);
                $sheet->getColumnDimension('K')->setWidth(17);
                $sheet->getColumnDimension('L')->setWidth(17);
                
                $sheet->getRowDimension(10)->setRowHeight(22);                
                $sheet->setCellValue('A10','NO');				
                $sheet->setCellValue('B10','NIM');
                $sheet->setCellValue('C10','NIRM');				                        
                $sheet->setCellValue('D10','NAMA MAHASISWA');				
                $sheet->setCellValue('E10','JK');	
                $sheet->setCellValue('F10','KELAS');	                
                $sheet->setCellValue('G10','T.A');		
                $sheet->setCellValue('H10','SMT');		
                $sheet->setCellValue('I10','STATUS');				
                $sheet->setCellValue('J10','BIAYA');				
                $sheet->setCellValue('K10','SUDAH BAYAR');	
                $sheet->setCellValue('L10','BELUM BAYAR');				
                                
                $styleArray=array(								
                                    'font' => array('bold' => true),
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A10:L10")->applyFromArray($styleArray);
                $sheet->getStyle("A10:L10")->getAlignment()->setWrapText(true);
                
                $komponen_biaya=array();
                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>1,'idkelas'=>'A'));
                $komponen_biaya['A']['baru'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');
                $komponen_biaya['A']['lama'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama');
                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>2,'idkelas'=>'A'));
                $komponen_biaya['A']['baru'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');       
                $komponen_biaya['A']['lama'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama');  

                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>1,'idkelas'=>'B'));
                $komponen_biaya['B']['baru'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');            
                $komponen_biaya['B']['lama'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama');            
                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>2,'idkelas'=>'B'));
                $komponen_biaya['B']['baru'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');            
                $komponen_biaya['B']['lama'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama');

                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>1,'idkelas'=>'C'));
                $komponen_biaya['C']['baru'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');            
                $komponen_biaya['C']['lama'][1]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama'); 
                $objFinance->setDataMHS(array('tahun_masuk'=>$tahun_masuk,'idsmt'=>2,'idkelas'=>'C'));
                $komponen_biaya['C']['baru'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('baru');            
                $komponen_biaya['C']['lama'][2]=$objFinance->getTotalBiayaMhsPeriodePembayaran('lama');  
                                
                $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
                $str = "SELECT no_formulir,nim,nirm,nama_mhs,jk,idkelas,tahun_masuk,semester_masuk FROM v_datamhs WHERE kjur='$kjur'AND tahun_masuk=$tahun_masuk AND k_status!='L' $str_kelas ORDER BY nim ASC,nama_mhs ASC";			                
                $this->db->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tahun_masuk','semester_masuk'));
                $r = $this->db->getRecord($str);	
                $row=11; 
                while (list($k,$v)=each($r)) {
                    $no_formulir=$v['no_formulir'];                                          
                    $nim=$v['nim'];                                                      
                    $sheet->setCellValue("A$row",$v['no']);
                    $sheet->setCellValueExplicit("B$row",$v['nim'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C$row",$v['nirm'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValue("D$row",$v['nama_mhs']);
                    $sheet->setCellValue("E$row",$v['jk']);
                    $str = "SELECT tahun,idsmt,idkelas,k_status FROM dulang WHERE nim='$nim'";
                    $this->db->setFieldTable(array('tahun','idsmt','idkelas','k_status'));
                    $data_dulang = $this->db->getRecord($str);
                    
                    $str="SELECT tahun,idsmt,SUM(dibayarkan) AS dibayarkan FROM v_transaksi WHERE no_formulir='$no_formulir' GROUP BY tahun,idsmt";
                    $this->db->setFieldTable(array('tahun','idsmt','dibayarkan'));
                    $result = $this->db->query($str);                    
                    $daftar_dibayarkan=array();
                    while ($baris=$result->fetch_assoc()) {                       
                       $daftar_dibayarkan[$baris['tahun']][$baris['idsmt']]=$baris['dibayarkan'];              
                    }        
                    $totalpembayaran=0;
                    $totalkewajiban=0;
                    while (list($m,$n)=each($data_dulang)) {
                        $idkelas=$n['idkelas'];
                        $sheet->setCellValue("F$row",$objDMaster->getNamaKelasByID($idkelas));
                        $sheet->setCellValue("G$row",$n['tahun']);
                        $sheet->setCellValue("H$row",$n['idsmt']);
                        $sheet->setCellValue("I$row",$objDMaster->getNamaStatusMHSByID ($n['k_status']));
                        if ($n['tahun']==$v['tahun_masuk'] && $v['semester_masuk'] == $n['idsmt']) {
                            $kewajiban=$komponen_biaya[$idkelas]['baru'][$n['idsmt']];
                        }else{
                            $kewajiban=$komponen_biaya[$idkelas]['lama'][$n['idsmt']];
                        }
                        $totalkewajiban+=$kewajiban;
                        $sheet->setCellValueExplicit("J$row",$objFinance->toRupiah($kewajiban),PHPExcel_Cell_DataType::TYPE_STRING);
                        $pembayaran_semester=$daftar_dibayarkan[$n['tahun']][$n['idsmt']];
                        $totalpembayaran+=$pembayaran_semester;
                        $sheet->setCellValueExplicit("K$row",$objFinance->toRupiah($pembayaran_semester),PHPExcel_Cell_DataType::TYPE_STRING);
                        $belumbayar=$kewajiban-$pembayaran_semester;
                        $sheet->setCellValueExplicit("L$row",$objFinance->toRupiah($belumbayar),PHPExcel_Cell_DataType::TYPE_STRING);                        
                        $row+=1;
                    }    
                    $sheet->mergeCells ("A$row:H$row");
                    $sheet->setCellValue("I$row",'TOTAL');
                    $sheet->setCellValueExplicit("J$row",$objFinance->toRupiah($totalkewajiban),PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("K$row",$objFinance->toRupiah($totalpembayaran),PHPExcel_Cell_DataType::TYPE_STRING);
                    $belumbayar=$totalkewajiban-$totalpembayaran;
                    $sheet->setCellValueExplicit("L$row",$objFinance->toRupiah($belumbayar),PHPExcel_Cell_DataType::TYPE_STRING);                        
                    $styleArray=array(								
                                    'font' => array('bold' => true)                                    
                                );																					 
                    $sheet->getStyle("I$row:L$row")->applyFromArray($styleArray);
                    $totalkewajiban_all+=$totalkewajiban;
                    $totalpembayaran_all+=$totalpembayaran;
                    
                    $row+=1;
                }
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A11:L$row")->applyFromArray($styleArray);
                $sheet->getStyle("A11:L$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );
                $sheet->getStyle("D11:D$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );
                $sheet->getStyle("J11:L$row")->applyFromArray($styleArray);
                $row+=2;
                $sheet->mergeCells ("A$row:I$row");
                $sheet->setCellValue("A$row",'TOTAL KESELURUAN');
                $sheet->setCellValueExplicit("J$row",$objFinance->toRupiah($totalkewajiban_all),PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("K$row",$objFinance->toRupiah($totalpembayaran_all),PHPExcel_Cell_DataType::TYPE_STRING);
                $belumbayar=$totalkewajiban_all-$totalpembayaran_all;
                $sheet->setCellValueExplicit("L$row",$objFinance->toRupiah($belumbayar),PHPExcel_Cell_DataType::TYPE_STRING);                        
                $styleArray=array(								
                                'font' => array('bold' => true),
                                'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                            );																					 
                $sheet->getStyle("A$row:L$row")->applyFromArray($styleArray);
                
                $this->printOut("piutang_jangka_pendek"); 
            break;            
        }
        $this->setLink($this->dataReport['linkoutput'],"Laporan Piutang Jangka Pendek");
    }
    /**
     * Digunakan untuk mencetak rekapitulasi pembayaran semester
     * @param type $objFinance object
     * @param type $objDMaster object
     */
    public function printRekapPembayaranSemester ($objFinance) {
        $kjur=$this->dataReport['kjur'];
        $nama_ps=$this->dataReport['nama_ps'];
        
        $tahun=$this->dataReport['ta'];
        $nama_tahun=$this->dataReport['nama_tahun'];
        
        $tahun_masuk=$this->dataReport['tahun_masuk'];
        $nama_tahun_masuk=$this->dataReport['nama_tahun_masuk'];
        
        $semester=$this->dataReport['semester'];
        $nama_semester=$this->dataReport['nama_semester'];
        $kelas=$this->dataReport['kelas'];
        $str_kelas = $kelas == 'none'?'':" AND idkelas='$kelas'";
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :              
                $this->setHeaderPT('I');
                $sheet=$this->rpt->getActiveSheet();
                
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                
                $sheet->mergeCells("A7:I7");
                $sheet->getRowDimension(7)->setRowHeight(20);
                
                $sheet->setCellValue("A7","LAPORAN REKAPITULASI PEMBAYARAN SEMESTER $nama_semester T.A $nama_tahun");
                $sheet->mergeCells("A8:I8");
                $sheet->setCellValue("A8","MAHASISWA PROGRAM STUDI $nama_ps TAHUN MASUK $nama_tahun_masuk");    
                
                $sheet->getRowDimension(8)->setRowHeight(20);
                $styleArray=array(
								'font' => array('bold' => true,
                                                'size' => 14),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
							);
                $sheet->getStyle("A7:I8")->applyFromArray($styleArray);
                
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(15); 
                $sheet->getColumnDimension('H')->setWidth(15); 
                $sheet->getColumnDimension('I')->setWidth(15);                
                
                $sheet->getRowDimension(10)->setRowHeight(22);                
                $sheet->setCellValue('A10','NO');				
                $sheet->setCellValue('B10','NIM');
                $sheet->setCellValue('C10','NIRM');				                        
                $sheet->setCellValue('D10','NAMA MAHASISWA');				
                $sheet->setCellValue('E10','JK');	
                $sheet->setCellValue('F10','KELAS');	                
                $sheet->setCellValue('G10','KEWAJIBAN');		
                $sheet->setCellValue('H10','PEMBAYARAN');		
                $sheet->setCellValue('I10','SISA');				
                
                
                $styleArray=array(								
                                    'font' => array('bold' => true),
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A10:I10")->applyFromArray($styleArray);
                $sheet->getStyle("A10:I10")->getAlignment()->setWrapText(true);
                
                $str = "SELECT idrekap,no_formulir,nim,nirm,nama_mhs,jk,n_kelas,dibayarkan,kewajiban,sisa FROM rekap_laporan_pembayaran_per_semester WHERE kjur='$kjur' AND tahun=$tahun AND idsmt='$semester'$str_kelas AND tahun_masuk=$tahun_masuk ORDER BY idkelas ASC,nama_mhs ASC";
                $this->db->setFieldTable(array('idrekap','no_formulir','nim','nirm','nama_mhs','jk','n_kelas','dibayarkan','kewajiban','sisa'));
                $r = $this->db->getRecord($str);
                
                $row=11;
                $totalkewajiban_all=0;
                $totalpembayaran_all=0;
                while (list($k,$v)=each($r)) {
                    $kewajiban=$v['kewajiban'];
                    $totalkewajiban_all+=$kewajiban;
                    $dibayarkan=$v['dibayarkan'];
                    $totalpembayaran_all+=$dibayarkan;
                    $sisa=$objFinance->toRupiah($v['sisa']);
                    $sheet->setCellValue("A$row",$v['no']);				
                    $sheet->setCellValueExplicit("B$row",$v['nim'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C$row",$v['nirm'],PHPExcel_Cell_DataType::TYPE_STRING);				                        
                    $sheet->setCellValue("D$row",$v['nama_mhs']);				
                    $sheet->setCellValue("E$row",$v['jk']);	
                    $sheet->setCellValue("F$row",$v['n_kelas']);	                
                    $sheet->setCellValueExplicit("G$row",$objFinance->toRupiah($kewajiban),PHPExcel_Cell_DataType::TYPE_STRING);                    
                    $sheet->setCellValueExplicit("H$row",$objFinance->toRupiah($dibayarkan),PHPExcel_Cell_DataType::TYPE_STRING);		
                    $sheet->setCellValueExplicit("I$row",$sisa,PHPExcel_Cell_DataType::TYPE_STRING);
                    
                    $row+=1;
                }
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $sheet->getStyle("A11:I$row")->applyFromArray($styleArray);
                $sheet->getStyle("A11:I$row")->getAlignment()->setWrapText(true);
                $row+=2;
                $sheet->mergeCells ("A$row:F$row");
                $sheet->setCellValue("A$row",'TOTAL KESELURUAN');
                $sheet->setCellValueExplicit("G$row",$objFinance->toRupiah($totalkewajiban_all),PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$row",$objFinance->toRupiah($totalpembayaran_all),PHPExcel_Cell_DataType::TYPE_STRING);
                $belumbayar=$totalkewajiban_all-$totalpembayaran_all;
                $sheet->setCellValueExplicit("I$row",$objFinance->toRupiah($belumbayar),PHPExcel_Cell_DataType::TYPE_STRING);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );
                $sheet->getStyle("D11:D$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );
                $sheet->getStyle("G11:I$row")->applyFromArray($styleArray);
                $this->printOut("rekap_pembayaran_semester");
            break;
        }
        $this->setLink($this->dataReport['linkoutput'],"Laporan Rekapitiulasi Pembayaran Semester");
    }
}