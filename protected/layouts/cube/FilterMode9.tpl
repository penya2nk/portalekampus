<div id="config-tool" class="closed">
    <a id="config-tool-cog">
        <i class="fa fa-cog"></i>
    </a>
    <div id="config-tool-options">
        <h4>OPTION</h4>        
        <br/>
        <h4 style="font-size:12px"><strong>Tahun Masuk:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
                <com:TActiveDropDownList ID="tbCmbTahunMasuk" OnCallback="Page.changeTbTahunMasuk" CssClass="form-control" Width="120px">
                    <prop:ClientSide.OnPreDispatch>
                        $('loading').show();
                    </prop:ClientSide.OnPreDispatch>
                   <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbTahunMasuk->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
                    <prop:ClientSide.onComplete>
                        $('loading').hide();
                        $('<%=$this->tbCmbTahunMasuk->ClientId%>').disabled='';
                    </prop:ClientSide.OnComplete>	
                </com:TActiveDropDownList>          
            </li>            
        </ul>        
    </div>
</div>