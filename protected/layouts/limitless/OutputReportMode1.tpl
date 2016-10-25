<div class="sidebar-category">
    <div class="category-title">
        <span>Output Report</span>
        <ul class="icons-list">
            <li><a href="#" data-action="collapse"></a></li>
        </ul>
    </div>
    <div class="category-content"> 
        <div class="form-group">
            <label><strong>Tipe Laporan :</strong></label>
            <com:TActiveDropDownList ID="tbCmbOutputReport" OnCallback="Page.changeOutputReport" CssClass="form-control">
                <prop:ClientSide.OnPreDispatch>
                    $('<%=$this->tbCmbOutputReport->ClientId%>').disabled='disabled';
                    Pace.stop();
                    Pace.start();                    
                </prop:ClientSide.OnPreDispatch>
               <prop:ClientSide.OnLoading>
                    $('<%=$this->tbCmbOutputReport->ClientId%>').disabled='disabled';
                </prop:ClientSide.OnLoading>
                <prop:ClientSide.onComplete>                    
                    $('<%=$this->tbCmbOutputReport->ClientId%>').disabled='';
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </div>        
    </div>
</div>