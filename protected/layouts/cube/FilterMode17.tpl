<div id="config-tool" class="closed">
    <a id="config-tool-cog">
        <i class="fa fa-cog"></i>
    </a>
    <div id="config-tool-options">
        <h4>OPTION</h4>        
        <br/>
        <h4 style="font-size:12px"><strong>Program Studi:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
                <com:TActiveDropDownList ID="tbCmbPs" OnCallback="Page.changeTbPs" CssClass="form-control">				
					<prop:ClientSide.OnPreDispatch>
						$('loading').show();
					</prop:ClientSide.OnPreDispatch>
                    <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbPs->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
					<prop:ClientSide.onComplete>
						$('loading').hide();
                        $('<%=$this->tbCmbPs->ClientId%>').disabled='';
					</prop:ClientSide.OnComplete>	
				</com:TActiveDropDownList>	
            </li>            
        </ul>
        <br/>        
        <h4 style="font-size:12px"><strong>Dari Tahun Akademik:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
               <com:TActiveDropDownList ID="tbCmbTA1" OnCallback="Page.changeTbTA1" CssClass="form-control">
					<prop:ClientSide.OnPreDispatch>
						$('loading').show();
					</prop:ClientSide.OnPreDispatch>
                   <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbTA1->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
					<prop:ClientSide.onComplete>
						$('loading').hide();
                        $('<%=$this->tbCmbTA1->ClientId%>').disabled='';
					</prop:ClientSide.OnComplete>	
				</com:TActiveDropDownList>
            </li>            
        </ul>
        <br/>        
        <h4 style="font-size:12px"><strong>s.d Tahun Akademik:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
               <com:TActiveDropDownList ID="tbCmbTA2" OnCallback="Page.changeTbTA2" CssClass="form-control">
					<prop:ClientSide.OnPreDispatch>
						$('loading').show();
					</prop:ClientSide.OnPreDispatch>
                   <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbTA2->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
					<prop:ClientSide.onComplete>
						$('loading').hide();
                        $('<%=$this->tbCmbTA2->ClientId%>').disabled='';
					</prop:ClientSide.OnComplete>	
				</com:TActiveDropDownList>
            </li>            
        </ul>
        <br/>        
        <h4 style="font-size:12px"><strong>Output Report:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
               <com:TActiveDropDownList ID="tbCmbOutputReport" OnCallback="Page.changeOutputReport" CssClass="form-control">
					<prop:ClientSide.OnPreDispatch>
						$('loading').show();
					</prop:ClientSide.OnPreDispatch>
                   <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbOutputReport->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
					<prop:ClientSide.onComplete>
						$('loading').hide();
                        $('<%=$this->tbCmbOutputReport->ClientId%>').disabled='';
					</prop:ClientSide.OnComplete>	
				</com:TActiveDropDownList>
            </li>            
        </ul>
    </div>
</div>