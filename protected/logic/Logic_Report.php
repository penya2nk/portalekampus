<?php
prado::using ('Application.logic.Logic_Global');
class Logic_Report extends Logic_Global {	
    /**
	* mode dari driver
	*
	*/
	protected $driver;
	/**
	* object dari driver2 report misalnya PHPExcel, TCPDF, dll.
	*
	*/
	public $rpt;	
    /**
	* object setup;	
	*/
	public $setup;	
    /**
	* object tanggal;	
	*/
    public $tgl;
	/**
	* Exported Dir
	*
	*/
	protected $exportedDir;	
	/**
	* posisi row sekarang
	*
	*/
	public $currentRow=1;		
    /**
     * 
     * data report	
	*/
	public $dataReport;	
	public function __construct ($db) {
		parent::__construct ($db);	
        $this->setup = $this->getLogic ('Setup');
		$this->tgl = $this->getLogic ('Penanggalan');
	}		
    /**
     * digunakan untuk mengeset data report
     * @param type $dataReport
     */
    public function setDataReport ($dataReport) {
        $this->dataReport=$dataReport;
    }
    /**
	*
	* set mode driver
	*/
	public function setMode ($driver) {
		$this->driver = $driver;
		$path = dirname($this->getPath()).'/';								
		$host=$this->setup->getAddress().'/';				
		switch ($driver) {
            case 'excel2003' :								
                $phpexcel=BASEPATH.'protected/lib/excel/';
                define ('PHPEXCEL_ROOT',$phpexcel);
                set_include_path(get_include_path() . PATH_SEPARATOR . $phpexcel);
                
                require_once ('PHPExcel.php');                
				$this->rpt=new PHPExcel();                
                $this->exportedDir['excel_path']=$host.'exported/excel/';
				$this->exportedDir['full_path']=$path.'exported/excel/';
			break;
			case 'excel2007' :							
                //phpexcel
                $phpexcel=BASEPATH.'protected/lib/excel/';
                define ('PHPEXCEL_ROOT',$phpexcel);
                set_include_path(get_include_path() . PATH_SEPARATOR . $phpexcel);
                
                require_once ('PHPExcel.php');
				$this->rpt=new PHPExcel();                
                $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
                $cacheSettings = array( 
                    'cacheTime' => 600
                );
                PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
				$this->exportedDir['excel_path']=$host.'exported/excel/';
				$this->exportedDir['full_path']=$path.'exported/excel/';
			break;					
            case 'pdf' :				
                require_once (BASEPATH.'protected/lib/tcpdf/tcpdf.php');
				$this->rpt=new TCPDF();			
				$this->rpt->setCreator ($this->Application->getID());
				$this->rpt->setAuthor ($this->setup->getSettingValue('config_name'));
				$this->rpt->setPrintHeader(false);
				$this->rpt->setPrintFooter(false);				
				$this->exportedDir['pdf_path']=$host.'exported/pdf/';	
				$this->exportedDir['full_path']=$path.'exported/pdf/';
			break;	
            case 'pdfzip' :
                $this->exportedDir['pdf_path']=$host.'exported/pdf/';	
				$this->exportedDir['full_path']=$path.'exported/pdf/';
            break;
		}
	}
    /**
     * digunakan untuk mendapatkan driver saat ini
     */
	public function getDriver () {
        return $this->driver;
    }   
    /**
     * digunakan untuk mencetak header
     * @param type $endColumn
     * @param type $alignment
     * @param type $columnHeader
     */
	public function setHeaderPT ($endColumn=null,$alignment=null,$columnHeader='C') {			
        $headerLogo=BASEPATH.$this->setup->getSettingValue('config_logo');
		switch ($this->getDriver()) {
            case 'pdf' :
                $rpt=$this->rpt;
                $rpt->Image($headerLogo,3,6,17,17,'JPG');
                
                $rpt->SetFont ('helvetica','B',12);
				$rpt->setXY(20,5);
				$rpt->Cell (0,5,'SEKOLAH TINGGI ILMU SOSIAL DAN ILMU POLITIK');				
				$rpt->setXY(20,8.5);
				$rpt->Cell (0,8.5,'RAJA HAJI TANJUNGPINANG');
				
				$rpt->SetFont ('helvetica','B',8);
				$rpt->setXY(20,11.5);
				$rpt->Cell (0,11.5,'JL. RAJA HAJI FISABILILLAH NO. 48 TANJUNGPINANG - KEPULAUAN RIAU');
				$rpt->setXY(20,14.5);
				$rpt->Cell (0,14.5,'TELP. (0771) 7000652 Website : http://www.stisipolrajahaji.ac.id Email : info@stisipolrajahaji.ac.id');
				$this->currentRow=14.5;
            break;
			case 'excel2003' :
			case 'excel2007' :	
                //cetak logo                
				$drawing = new PHPExcel_Worksheet_Drawing();		
				$drawing->setName('Logo');
				$drawing->setDescription('Logo');			
				
				$drawing->setPath($headerLogo);
				$drawing->setHeight(90);
				$drawing->setCoordinates('A'.$this->currentRow);
				$drawing->setOffsetX(20);
				$drawing->setRotation(0);
				$drawing->getShadow()->setVisible(false);
				$drawing->getShadow()->setDirection(0);
				$drawing->setWorksheet($this->rpt->getActiveSheet());
                
				$row=1;
                $sheet=$this->rpt->getActiveSheet();
				$sheet->getRowDimension($row)->setRowHeight(18);
				$sheet->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$sheet->setCellValue($columnHeader.$row,'SEKOLAH TINGGI ILMU SOSIAL DAN ILMU POLITIK');
				
				$row+=1;
				$sheet->getRowDimension($row)->setRowHeight(18);
				$sheet->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$sheet->setCellValue($columnHeader.$row,'RAJA HAJI TANJUNGPINANG');
				
				$row+=1;
				$sheet->getRowDimension($row)->setRowHeight(18);
				$sheet->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$sheet->setCellValue($columnHeader.$row,'JL. RAJA HAJI FISABILILLAH NO. 48 TANJUNGPINANG - KEPULAUAN RIAU');
                $row+=1;
				$sheet->getRowDimension($row)->setRowHeight(18);
				$sheet->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$sheet->setCellValue($columnHeader.$row,'TELP. (0771) 7000652 Website : http://www.stisipolrajahaji.ac.id Email : info@stisipolrajahaji.ac.id');
				
				$row+=1;
				$sheet->getRowDimension($row)->setRowHeight(18);
				$sheet->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$sheet->setCellValue($columnHeader.$row,'');
								
				$sheet->getStyle($columnHeader.($row-3))->getFont()->setSize('10');
				$sheet->getStyle($columnHeader.($row-2))->getFont()->setSize('12');	
				$sheet->getStyle($columnHeader.($row-1))->getFont()->setSize('10');				
				$sheet->getStyle($columnHeader.$row)->getFont()->setSize('10');
				
				
				$sheet->duplicateStyleArray(array(
												'font' => array('bold' => true),
												'alignment' => array('horizontal'=>$alignment,
														'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)					   	
												),
												$columnHeader.$this->currentRow.':'.$columnHeader.$row
											);				
				$this->currentRow=$row;
			break;			
		}		
    }    
    /**
     * digunakan untuk mencetak laporan
     * @param type $filename
     * @param type $debug
     */
	public function printOut ($filename,$debug=false) {	
		$filename_to_write = $debug == true ? $filename  : $filename.'_'.date('Y_m_d_H_m_s');	
		switch ($this->driver) {
			case 'excel2003' :
                //$writer=new PHPExcel_Writer_Excel5($this->rpt);								
                $writer=PHPExcel_IOFactory::createWriter($this->rpt, 'Excel5');
                $writer->setPreCalculateFormulas(false);
				$filename_to_write = "$filename_to_write.xls";
				$writer->save ($this->exportedDir['full_path'].$filename_to_write);		
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['excel_path'].=$filename_to_write;		
            break;
			case 'excel2007' :
				$writer=PHPExcel_IOFactory::createWriter($this->rpt, 'Excel2007');
                $writer->setPreCalculateFormulas(false);
				$filename_to_write = "$filename_to_write.xlsx";
				$writer->save ($this->exportedDir['full_path'].$filename_to_write);		
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['excel_path'].=$filename_to_write;		
			break;	
            case 'pdf' :
				$filename_to_write="$filename_to_write.pdf";
				$this->rpt->output ($this->exportedDir['full_path'].$filename_to_write,'F');
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['pdf_path'].=$filename_to_write;		
			break;            
		}
	}    
    /**
     * digunakan untuk printout ke dalam bentuk archive
     * @param type $DataFile
     * @param type $FileName
     * @param type $FormatArchive
     */
    public function printOutArchive ($DataFile,$FileName,$FormatArchive) {	
        switch ($FormatArchive) {
            case 'zip' :                        
                $namafile=$FileName.'_'.date('Y_m_d_H_m_s').'.zip';
                $destinationfile=$this->exportedDir['full_path'].$namafile;
                $this->setup->createZIP($DataFile,$destinationfile);
                $this->exportedDir['pdf_path'].=$namafile;		
            break;
        }
    }
    /**
	* digunakan untuk mendapatkan link ke sebuah file hasil dari export	
	* @param obj_out object 
	* @param text in override text result
	*/
	public function setLink ($obj_out,$text='') {
		$filename=$text==''?$this->exportedDir['filename']:$text;		        
		switch ($this->driver) {
			case 'excel2003' :
                $obj_out->Text = "$filename.xls";
				$obj_out->NavigateUrl=$this->exportedDir['excel_path'];				
            break;
			case 'excel2007' :                
				$obj_out->Text = "$filename.xlsx";
				$obj_out->NavigateUrl=$this->exportedDir['excel_path'];				
			break;	
            case 'pdf' :
				$obj_out->Text = "$filename.pdf";
				$obj_out->NavigateUrl=$this->exportedDir['pdf_path'];	
			break;
            case 'pdfzip' :
				$obj_out->Text = "$filename.zip";
				$obj_out->NavigateUrl=$this->exportedDir['pdf_path'];	
			break;
		}
	}    
}
?>

