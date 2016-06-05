<br/>
<div class="col-md-4">            
    <com:TActiveLabel ID="paginationInfo"/>        
</div>
<div class="col-md-8">        
    <com:TActiveCustomPager ID="pager" OnCallBack="Page.renderCallback" ControlToPaginate="RepeaterS" Mode="Numeric" OnPageIndexChanged="Page.Page_Changed" PrevPageText="&laquo; Previous" NextPageText="Next &raquo;" PageButtonCount="10" FirstPageText="First" LastPageText="Last" CssClass="custompaging text-right" ButtonCssClass="page">	
        <prop:ClientSide.OnPreDispatch>
            $('<%=$this->pager->ClientId%>').disabled='disabled';							            
            Pace.stop();
            Pace.start();                                                                                                             
        </prop:ClientSide.OnPreDispatch>                    
        <prop:ClientSide.OnLoading>
            $('<%=$this->pager->ClientId%>').disabled='disabled';							            
        </prop:ClientSide.OnLoading>
        <prop:ClientSide.onComplete>                                            
            $('<%=$this->pager->ClientId%>').disabled='';									            
        </prop:ClientSide.OnComplete>
    </com:TActiveCustomPager>            
</div>    
<br/><br/>



     


