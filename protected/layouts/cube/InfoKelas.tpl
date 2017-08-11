<div class="row">
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><i class="fa fa-bars"></i> Informasi Kelas</h2> 
                <div class="filter-block pull-right">                                           
                    
                </div>
            </header>
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>KODE: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['kmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NAMA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['nmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SKS: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['sks']%></p>
                                </div>                            
                            </div>       
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SEMESTER: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['semester']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>DOSEN PENGAJAR: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['nama_dosen']%> [<%= $this->Demik->InfoKelas['nidn']%>]</p>
                                </div>                            
                            </div> 
                        </div>
                    </div>                    
                    <div class="col-sm-6">
                        <div class="form-horizontal">                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>KELAS: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['namakelas']%> [<%= $this->Demik->InfoKelas['nidn']%>]</p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>HARI: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['hari']%></p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JAM: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['jam_masuk'].'-'.$this->Demik->InfoKelas['jam_keluar']%></p>
                                </div>                            
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>RUANG: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['namaruang']%> [<%= $this->Demik->InfoKelas['kapasitas']%>]</p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JUMLAH PESERTA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoKelas['jumlah_peserta']%></p>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>