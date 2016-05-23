<?php
prado::using ('Application.Logic.Logic_Global');
prado::using ('Application.lib.ParseDown.Parsedown');
class Logic_Forum extends Logic_Global {		
	/**
	* object parser
	*/
	private $parser;	
	public function __construct ($db) {
		parent::__construct ($db);					
        $this->parser= new Parsedown();
	}	
    /**
     * digunakan untuk mendapatkan kategori forum
     */
	public function getListForumKategori () {
        if ($this->Application->Cache) {            
            $dataitem=$this->Application->Cache->get('listforumkategori');            
            if (!isset($dataitem['none'])) {                
                $dataitem=$this->getList ('forumkategori',array('idkategori','nama_kategori'),'idkategori',null,1);			
                $dataitem['none']='Daftar Kategori';    
                $this->Application->Cache->set('listkategori',$dataitem);
            }
        }else {                        
            $dataitem=$this->getList ('kelas',array('idkategori','nama_kategori'),'idkategori',null,1);			
            $dataitem['none']='Daftar Kategori';
        }
        return $dataitem;     		
	}
    /**
     * memparsing text markdown
     * @param type $text
     */
    public function parsingMarkdown($text) {
        $str = $this->parser->text($text);
        return $str; 
    }
    
}