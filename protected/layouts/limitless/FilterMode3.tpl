<div class="sidebar-category">
    <div class="category-title">
        <span>Filter</span>
        <ul class="icons-list">
            <li><a href="#" data-action="collapse"></a></li>
        </ul>
    </div>
    <div class="category-content"> 
        <div class="form-group">
            <label><strong>Program Studi :</strong></label>
            <com:TActiveDropDownList ID="tbCmbPs" OnCallback="Page.changeTbPs" CssClass="form-control">				
                <prop:ClientSide.OnPreDispatch>
                    $('<%=$this->tbCmbPs->ClientId%>').disabled='disabled';
                    Pace.stop();
                    Pace.start();                    
                </prop:ClientSide.OnPreDispatch>
                <prop:ClientSide.OnLoading>
                    $('<%=$this->tbCmbPs->ClientId%>').disabled='disabled';
                </prop:ClientSide.OnLoading>
                <prop:ClientSide.onComplete>                    
                    $('<%=$this->tbCmbPs->ClientId%>').disabled='';
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>	
        </div>
        <div class="form-group">
            <label><strong>Tahun Masuk :</strong></label>
            <com:TActiveDropDownList ID="tbCmbTahunMasuk" OnCallback="Page.changeTbTahunMasuk" CssClass="form-control" Width="120px">
                <prop:ClientSide.OnPreDispatch>
                    $('<%=$this->tbCmbTahunMasuk->ClientId%>').disabled='disabled';
                    Pace.stop();
                    Pace.start();                    
                </prop:ClientSide.OnPreDispatch>
               <prop:ClientSide.OnLoading>
                    $('<%=$this->tbCmbTahunMasuk->ClientId%>').disabled='disabled';
                </prop:ClientSide.OnLoading>
                <prop:ClientSide.onComplete>                    
                    $('<%=$this->tbCmbTahunMasuk->ClientId%>').disabled='';
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </div>    
        <div class="form-group">
            <label><strong>Kelas :</strong></label>
            <com:TActiveDropDownList ID="tbCmbKelas" OnCallback="Page.changeTbKelas" CssClass="form-control">
                <prop:ClientSide.OnPreDispatch>
                    $('<%=$this->tbCmbKelas->ClientId%>').disabled='disabled';
                    Pace.stop();
                    Pace.start();                    
                </prop:ClientSide.OnPreDispatch>
               <prop:ClientSide.OnLoading>
                    $('<%=$this->tbCmbKelas->ClientId%>').disabled='disabled';
                </prop:ClientSide.OnLoading>
                <prop:ClientSide.onComplete>                    
                    $('<%=$this->tbCmbKelas->ClientId%>').disabled='';
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </div>
    </div>
</div>