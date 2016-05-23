<div id="config-tool" class="closed">
    <a id="config-tool-cog">
        <i class="fa fa-cog"></i>
    </a>
    <div id="config-tool-options">
        <h4>OPTION</h4>        
        <br/>
        <h4 style="font-size:12px"><strong>Tahun Akademik:</strong></h4>
        <ul id="skin-colors" class="clearfix">
            <li style="font-size:10px">
                <com:TActiveDropDownList ID="tbCmbTA" OnCallback="Page.changeTbTA" CssClass="form-control">				
					<prop:ClientSide.OnPreDispatch>
						$('loading').show();
					</prop:ClientSide.OnPreDispatch>
                    <prop:ClientSide.OnLoading>
                        $('<%=$this->tbCmbTA->ClientId%>').disabled='disabled';
                    </prop:ClientSide.OnLoading>
					<prop:ClientSide.onComplete>
						$('loading').hide();
                        $('<%=$this->tbCmbTA->ClientId%>').disabled='';
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