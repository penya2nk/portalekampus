<div class="row">
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2 class="pull-left"><i class="fa fa-bars"></i> Informasi Matakuliah</h2> 
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
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['kmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>NAMA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['nmatkul']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SKS: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['sks']%></p>
                                </div>                            
                            </div>                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>T.A: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['ta']%></p>
                                </div>                            
                            </div>  
                        </div>
                    </div>                    
                    <div class="col-sm-6">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SEMESTER: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['semester']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>DOSEN PENGAMPU UTAMA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['nama_dosen']%> [<%= $this->Demik->InfoMatkul['nidn']%>]</p>
                                </div>                            
                            </div>                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>JUMLAH PESERTA: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['jumlah_peserta']%></p>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><strong>SEMESTER: </strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><%= $this->Demik->InfoMatkul['nama_semester']%></p>
                                </div>                            
                            </div>  
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>