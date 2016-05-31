//Namespace do DTable
var DTable = {};

DTable.angular = {};

DTable.angular.module = Yapiys.angular.module;


DTable.angular.setModule = function(moduleInstance){

    DTable.angular.module = moduleInstance;

};


DTable.angular.initialize = function(moduleInstance){

    if(moduleInstance){

        DTable.angular.module = moduleInstance;

    }


    DTable.angular.module.directive('dtable',function($compile,$parse){


        return {

            restrict : "E",

            link : function(scope,element,attrs){

                var childs = element.children();
                var table = false;
                var table_append = false;
                var table_loader = false;
                var limit = 10;
                var plugins = [];
                var row_name = "row";
                var data = [];
                var filterPlaceholder = __t("Pesquisar...","dtable_search");
                var name = "";


                var onget_start = false;
                var onget_end = false;
                var onempty = false;
                var onmatch = false;

                var loader_to_top = false;
                var placeholder_to_top = false;

                var no_status_label = false;

                var footer_custom_class="";

                var no_header = false;


                if(attrs.name){

                    name = attrs.name;
             
                    
                    scope[name] = {name:name};
                    
                   


                }else{

                    console.error('Set name attribute on dtable element');
                    return;

                }

                if(attrs.hasOwnProperty('loaderToTop')){

                    loader_to_top = true;

                }

                if(attrs.hasOwnProperty('placeholderToTop')){

                    placeholder_to_top = true;

                }

                if(attrs.hasOwnProperty("noStatusLabel")){

                        no_status_label = true;

                }


                if(attrs.hasOwnProperty("footerCustomClass")){

                    footer_custom_class = attrs["footerCustomClass"];

                }

                if(attrs.hasOwnProperty("noHeader")){

                    no_header = true;
                }


                if(attrs.onGetStart){


                    onget_start = attrs.onGetStart;

                }


                if(attrs.onGetEnd){


                    onget_end = attrs.onGetEnd;

                }


                if(attrs.onEmpty){


                    onempty = attrs.onEmpty;

                }


                if(attrs.onMatch){


                    onmatch = attrs.onMatch;

                }



                var html = "";

                var extensions_html_updates = false;

                var dataSourceName = false;

                var dataSource = false;

                var params = {};

                var panel = false;

                //dsource : Atributo orbigatorio
                if(attrs.source){

                    dataSourceName = attrs.source;

                }else{

                    console.error('Set data-source attribute on dtable element');
                    return;

                }
				
				
				//Dtable ready
				if(attrs.hasOwnProperty("onReady")){
					
					scope[name].onReady = scope.$eval(attr['onReady']);	
					
					
				}
				
				//Fetch events

				if(attrs.hasOwnProperty("fStart")){
					
					scope[name].onFetchStart = scope.$eval(attrs["fStart"]);		
					
					
				}
				
				if(attrs.hasOwnProperty("fEnd")){
					
					scope[name].onFetchStart = scope.$eval(attrs["fEnd"]);					
					
				}

				/*
				if(attrs.hasOwnProperty('fcallback')){
					
					//fetchCallback
					var cback = $eval(attrs["fcallback"]);
					cback();
					alert(cback);
					
					
					scope[name].fetchCallback = cback; 
				
				}*/
				
				
                var limitProperty = false;

                if(attrs.limit){

                    limitProperty = parseInt(attrs.limit);

                }


                scope[name].limits= [5,15,25,35,50,65,80,95,100,125];

                scope[name].limits_visible = true;

                scope[name].dataSourceName = dataSourceName;

                scope[name].filter = {string:""};
				
				
				//alert("jjj");


                if(!limitProperty){

                    scope[name].limit = scope[name].limits[0];

                }else{

                    scope[name].limit = limitProperty;

                }



                scope[name].filter.visible = true;

                scope[name].pagination_visible = true;

                scope[name].no_match = false;

                scope[name].first_page_label = __t("Primeira","dtable_first_page");
                scope[name].last_page_label = __t("Ultima","dtable_last_page");

                scope[name].limit_per_page_label=__t("registros por pagina","dtable_per_page");

                scope[name].range = {start:0,end:0};

                scope[name].matches = 0;

                scope[name].showing_label = __t("mostrado","dtable_showing");
                scope[name].rows_label = __t("registros","dtable_showing_records");
                scope[name].showing_of_label=__t("de","dtable_showing_of");

                scope[name].gap = 3;

                scope[name].ordering_by = -1;
                scope[name].ordering_asc = false;
                scope[name].ordering_desc = false;


                scope[name].no_status_label = no_status_label;
				
				

                //var state = DTable.readState(attrs.name);


                scope[name].saveState = function(){

                    //Saves the state
                    DTable.saveState(name,scope[name].limit,scope[name].activePage,scope[name].filter.string);

                };




                scope[name].toogleOrder = function(columnIndex) {

                    //Column index should be a number
                    if(typeof columnIndex!='number'){

                        return;

                    }

                    //Column index should be greater or equal to zero
                    if(columnIndex<0){

                        return;

                    }

                    //The same column
                    if (columnIndex == scope[name].ordering_by) {

                        //Order ascending by default
                        if (!scope[name].ordering_asc && !scope[name].ordering_desc) {

                            scope[name].ordering_asc = true;

                        } else if (scope[name].ordering_asc && !scope[name].ordering_desc) {


                            scope[name].ordering_asc = false;
                            scope[name].ordering_desc = true;


                        } else if (scope[name].ordering_desc && !scope[name].ordering_asc) {

                            scope[name].ordering_desc = false;
                            scope[name].ordering_asc = true;

                        } else {

                            scope[name].ordering_asc = true;
                            scope[name].ordering_desc = false;

                        }

                    }else{//Another column

                        scope[name].ordering_asc = true;
                        scope[name].ordering_desc = false;

                    }

                    scope[name].ordering_by = columnIndex;

                    //Refreshes the current page
                    scope[name].refresh(true);

                };

                scope[name].show_limit_label = true;

                if(attrs.placeholder){

                    filterPlaceholder = attrs.placeholder;
                    $(element).removeAttr('placeholder');

                }

                //GAP
                if(attrs.gap){

                    scope[name].gap = parseInt(attrs.gap);

                }

                var goSearch = "pesquisar";

                 if(attrs.golabel){

                    goSearch = attrs.golabel;
 

                }

                goSearch = __t(goSearch,'dtable_search');

                if(attrs.panel){

                    panel = attrs.panel;

                }

             


                if(attrs.row){

                    row_name = attrs.row;
                    scope[name].row_name = row_name;
                    $(element).removeAttr('row');

                }

                if(attrs.extensions){

                    plugins = eval(attrs.extensions);
                    $(element).removeAttr('extensions');

                }else{


                    for(var attribute in attrs){

                        //Nome de um plugin
                        if(attribute[0]=='@'){

                            var pluginName = attribute.substring(1,attribute.length);
                            plugins.push(pluginName);
                            
                        }

                    }

                }

                if(childs.length===0){

                    return;

                }else{

                    //Primeiro child deve ser uma table
                    table = childs[0];

                    if(childs.length>1){

                        table_append = childs[1];

                        if(childs.length>2){

                            table_loader = childs[2];


                        }

                    }

                }


                var gap = scope[name].gap;
                var find_box_height = 12-3-gap;

                //Inicializa os Hooks
                DTable.Hooks.init(name,plugins);


                //Antes da
                var beforeFilter = function(param){


                };



                var initializePlugins = function(html,scope){

                    var final_html = html;

                    for(var pluginIndex in plugins){

                        //Nome da extensao
                        var pluginName = plugins[pluginIndex];


                        //Instacia da extensao
                        var pluginInstance = DTable.Extensions.getInstance(pluginName);


                        //Instancia valida
                        if(pluginInstance){

                            //Inicializavel
                            if(pluginInstance.init){

                                //Inicializando
                                var result = pluginInstance.init(name,final_html,scope);

                                //A extensao alterou o HTML
                                if(result){

                                    final_html = result;

                                }

                            }

                        }


                    }


                    return final_html;

                };


                var fireEvent = function(name,params){

                    for(var pluginIndex in plugins){

                        //Nome da extensao
                        var pluginName = plugins[pluginIndex];

                        //Instacia da extensao
                        var pluginInstance = DTable.Extensions.getInstance(pluginName);


                        //Instancia valida
                        if(pluginInstance){

                            //Trata do evento especificado
                            if(pluginInstance.hasOwnProperty('on'+name)){

                                //Dispara o evento
                                var result = pluginInstance['on'+name](params);



                            }

                        }


                    }

                };


                var limit_combo = "<div class='col-sm-6 pull-left'>"+
                    "<div class='col-sm-1'></div><div class='col-sm-5'>"+
                    "<label><select ng-model='filter.limit' class='form-control dtable-limits'"+
                    " ng-show='limits_visible'>"+
                    "<option ng-repeat='lim in "+name+".limits' value='{{lim}}'>{{lim}}"+
                    "</select></label></div></div>";

                var textInput = "<div class='col-sm-6'>"+
                    "<div class='pull-right row input-group'>"+
                    "<div class='col-sm-11'>"+
                    "<input ng-show='filter.visible' type='text' class='form-control dtable-q'"+
                    "ng-model='filter.string' "+
                    "placeholder = '"+filterPlaceholder+"'>"+
                    "</div>"+
                    "</div></div>";

                var hd = '<div class="row wrapper">'+
                  '<div class="col-sm-3 m-b-xs">'+
                     '<select ng-show="'+name+'.limits_visible" ng-model="'+name+'.limit" class="input-sm form-control w-sm inline v-middle">'+
                      '<option ng-repeat="lim in '+name+'.limits" value="{{lim}}">{{lim}}</option>'+
                    '</select>'+
                    '  <small ng-show="'+name+'.show_limit_label" class="text-muted inline m-t-sm m-b-sm">{{&nbsp;&nbsp;'+name+'.limit_per_page_label}}</small>'+
                  '</div>'+
                  '<div class="col-sm-'+gap+'">'+
                  '</div>'+
                  '<div class="col-sm-'+find_box_height+'">'+
                    '<div class="input-group">'+
                      '<input type="text" class="input-sm form-control" ng-model="'+name+'.filter.string" placeholder="'+filterPlaceholder+'">'+
                      '<span class="input-group-btn">'+
                        '<button class="btn btn-sm btn-default" type="button">'+goSearch+'</button>'+
                      '</span>'+
                    '</div>'+
                  '</div>'+
                '</div>';


                if(no_header){

                    hd= "";

                }


                //var header = limit_combo+textInput+'<br><br>';
                var header = "hhhhhh<br><br>";


                var pagination =

                    "<ul class='pagination pagination-sm m-t-none m-b-none dtable-pagination'"+
                    "ng-show='"+name+".pagination_visible'>"+


                     //Primeira pagina
                    "<li ng-class='{\"disabled\":!"+name+".paging.previousGroupAvailable}'>"+
                    "<a href='#' ng-click='"+name+".paging.activateFirst()' "+
                    "ng-disabled='!"+name+".paging.previousGroupAvailable' class='dtable-page'>{{"+name+".first_page_label}}"+
                    "</a>"+

                    "</li>"+


                    "<li ng-class='{\"disabled\":!"+name+".paging.previousGroupAvailable}'>"+
                    "<a href='#' ng-click='"+name+".paging.activatePrevious()' "+
                    "ng-disabled='!"+name+".paging.previousGroupAvailable'>"+
                    "<i class='dtable-previous fa fa-chevron-left'></i></a>"+
                    "</li>"+

                    "<li ng-class='{\"active\":"+name+".activePage==page}' ng-repeat='page in "+name+".visiblePages'>"+
                    "<a href='#' ng-click='"+name+".activatePage(page)' "+
                    "class='dtable-page page-{{page}}'>"+
                    "{{page}}</a>"+
                    "</li>"+

                    "<li ng-class='{\"disabled\":!"+name+".paging.nextGroupAvailable}'>"+

                    "<a href='#' ng-click='"+name+".paging.activateNext()'>"+
                    "<i class='dtable-next fa fa-chevron-right'></i>"+
                    "</a>"+
                    "</li>"+

                    //Ultima Pagina
                    "<li ng-class='{\"disabled\":!"+name+".paging.nextGroupAvailable}'>"+
                      "<a href='#' ng-click='"+name+".paging.activateLast()' "+
                    "ng-disabled='!"+name+".paging.nextGroupAvailable' class='dtable-page'>{{"+name+".last_page_label}}"+
                    "</a>"+

                    "</li>"+

                    "</ul>";
                    //"</center><br><br>"+
                    //
                    //"</div>";

                var footer = '<div class="panel-footer dtable-footer" ng-show="!'+name+'.no_match"><div class="row">'+

                '<div  class="col-sm-3 text-center pull-left dtable-status-label" ng-hide="'+name+'.no_status_label">'+
                  '<small class="text-muted inline m-t-sm m-b-sm">{{'+name+'.showing_label}} {{'+name+'.range.start}}-{{'+name+'.range.end}} {{'+name+'.showing_of_label}} {{'+name+'.matches}} {{'+name+'.rows_label}}</small>'+
                '</div>'+
                //'<div class="col-sm-2"></div>'+
                '<div class="col-sm-9 text-right text-center-xs">'+                
                  pagination+
                '</div>'+'<div class="col-sm-1"></div></div></div>';


                if(panel){

                    var f = panel+' .panel-footer';

                }


                //Table Headers
                scope[name].headers = {};

                //Setups header to toogle ordering
                var all_heads = $(table).find('th');

                var index = 0;

                all_heads.each(function(index_,item){

                    var skip = $(item).attr('skip-header');

                    if(typeof skip!='undefined'){

                        return;

                    }

                    var headerName = $(item).html();
                    var ngClick = name+'.toogleOrder('+index+')';

                    scope[name].headers[headerName] = index;
                    $(item).attr('ng-click',ngClick);

                    var _asc = name+'.ordering_by=='+index+'&&'+name+'.ordering_asc==true';
                    var _desc = name+'.ordering_by=='+index+'&&'+name+'.ordering_desc==true';
                    var _both = name+'.ordering_by!='+index;

                    var asc = $('<img>');
                    asc.css('align','right');
                    asc.css('float','right');
                    asc.prop('src','webroot/public/dtable/img/sort_asc.png');
                    asc.addClass('sorting_icon');
                    asc.attr('ng-show',_asc);

                    var desc = $('<img>');
                    desc.css('align','right');
                    desc.css('float','right');
                    desc.prop('src','webroot/public/dtable/img/sort_desc.png');
                    desc.addClass('sorting_icon');
                    desc.attr('ng-show',_desc);

                    var both = $('<img>');
                    both.css('align','right');
                    both.css('float','right');
                    both.prop('src','webroot/public/dtable/img/sort_both.png');
                    both.addClass('sorting_icon');
                    both.attr('ng-show',_both);

                    $(item).append(asc);
                    $(item).append(desc);
                    $(item).append(both);

                    index++;


                });




                //Configura o ng-repeat
                var all_rows =  $(table).find('tbody').find('tr');
                var table_row = all_rows[0];
                $(table_row).attr('ng-repeat',row_name+' in '+name+'.data');
                $(table_row).addClass('dtable-row');

                var table_classes = $(table).attr('class');

                var table_id = $(table);

                var append_content = '';

                var prepend_content = ''


                if(table_append){


                    var theHtml = "<div ng-show='"+name+".no_match&&!"+name+".busy'>"+$(table_append).html()+'</div>';

                    if(!placeholder_to_top){

                        append_content = theHtml;

                    }else{


                        prepend_content = theHtml;

                    }




                }

                if(table_loader){

                    var loader_ = "<div ng-show='"+name+".busy&&!"+name+".searching'>"+$(table_loader).html()+'</div>';

                    if(!loader_to_top){


                        append_content = append_content+loader_;

                    }else{

                        prepend_content = prepend_content + loader_;

                    }




                }



                //Html do data-table
                html = hd +
                prepend_content+
                "<table class='dtable-table "+table_classes+
                "' id='"+table_id+"'>"+$(table).html()+
                "</table>"+append_content+"<div class='row dtable-footer "+footer_custom_class+"'>"+footer+"</div><div>";


                //Assistir a alteracao do limit
                scope.$watch(name+'.limit',function(value){


                    //Limite por pagina invalido
                    if(typeof value=='undefined'){


                        return;

                    }



                    scope[name].saveState();

                    //Hook de alteracao de limit
                    var limitHookShooter = new DTable.Hooks.SyncShooter(name,scope);

                    //Dispara o Hook
                    limitHookShooter.fire('limitChange',{value:value},{});

                    if(value!==scope[name].limit){

                        scope[name].limit = value;

                    }


                    //Obter dados
                    //scope[name].fetchData(value);
                    scope[name].fetchPage(1);

                });

				
				scope[name].searching = false;

                //Assistir a alteracao do texto de filtro
                scope.$watch(name+'.filter.string',function(value){

                    //Query string invalida
                    if(typeof value=='undefined'){

                        return;

                    }
					
					//Not empty string
					if(value.trim()!=""){
						
						
						scope[name].searching = true;
						
						
					}else{
						
						//Empty string
						scope[name].searching = false;
						
					}

                    scope[name].saveState();

                    //Hook de alteracao da query de busca
                    var limitHookShooter = new DTable.Hooks.SyncShooter(name,scope);

                    //Dispara o Hook
                    limitHookShooter.fire('qChange',{value:value},{});

                    //Obter dados
                    //scope[name].fetchData(value);
                    scope[name].fetchPage(1);

                });


                scope[name].page =1;

                scope[name].data = data;

                scope[name].busy = false;

                scope[name].pages = [];

                scope[name].visiblePages = [];

                scope[name].pages = 5;

                scope[name].no_match = true;

                scope[name].totalPages = 0;

                scope[name].activePage = 1;

                //Obtem informacao para popular a tabel
                scope[name].fetchData = function(query){

                    //Hook antes da filtragem
                    beforeFilter(query);

                    //DTable ocupado
                    scope[name].busy = true;

                    //alert(dataSourceName);

                    //Busca os dados na fonte
                    DTable.DataSource.retrieve(dataSourceName,name,query,{page:1},scope);


                };


                scope[name].refresh = function(refreshActivePage,customPage,afterDelete){


                    scope[name].busy = true;

                    scope.$apply();


                    //Hook de activacao de pagina
                    var initShooter = new DTable.Hooks.SyncShooter(name,scope);

                    //Dispara o Hook de refresh
                    initShooter.fire('refresh',{name:name},{});

                    var page = 1;

                    if(refreshActivePage){

                        page = scope[name].activePage;
						
						//Fazer refresh da pagina actual depois de uma remocao, sabendo que a pagina actual so possui um item
						if(scope[name].data.length==1&&page>1&&afterDelete){
													
							page = page -1;
							
						}
						
						

                    }else if(customPage){


                        page = customPage;

                    }

                    this.fetchPage(page);

                };


                //Obtem dados de uma pagina
                scope[name].fetchPage = function(p){


                    //DTable ocupado
                    scope[name].busy = true;
					
					if(scope[name].onFetchStart){
						
						scope[name].onFetchStart();
						
					}

                    var query = scope[name].filter.string;

                    //Hook de fetch de dados
                    var fetchHookShooter = new DTable.Hooks.SyncShooter(name,scope);

                    //Parametros do Hook
                    var hookWritables = {query:query,source:dataSourceName};
                    var hookReadables = {limit: scope[name].limit};



                    //Dispara o Hook
                    fetchHookShooter.fire('fetch',hookReadables,hookWritables);

                    //Pega os updates do hook
                    var updates = fetchHookShooter.getUpdates();

                    var updated_query = updates.query;

                    var updated_data_source_name = updates.source;

                    //Busca os dados na fonte
                    DTable.DataSource.retrieve(updated_data_source_name,name,updated_query,
                        {page:p},scope);

                };


                //Activacao de uma pagina
                scope[name].activatePage = function(number){

                    //Hook de activacao de pagina
                    var initShooter = new DTable.Hooks.SyncShooter(name,scope);


                    //Parametros do Hook
                    var hookParams = {number:number};

                    //Dispara o Hook
                    initShooter.fire('activatePage',hookParams,{});


                    //Considera esta a pagina activa
                    scope[name].activePage = number;


                    scope[name].saveState();

                    //Busca os dados da pagina
                    scope[name].fetchPage(number);


                };

                scope[name].loadData = function(page,matches,total,data){

                    var totalPages = matches/scope[name].limit;


                    if((matches%scope[name].limit)>0){

                        totalPages++;

                    }


                    if(totalPages<1){

                        totalPages=1;

                    }


                    if(matches==0){
;
                        scope[name].pagination_visible = false;
                        scope[name].no_match = true;


                    }else{

                        scope[name].pagination_visible = true;
                        scope[name].no_match = false;

                    }

                    var allPages = [];

                    scope[name].data = data;
					
					


                    //Primeira pagina
                    if(page==1){

                        scope[name].activePage = 1;


                        for(var i =1;i<=totalPages;i++){

                            allPages.push(i);

                        }


                        scope[name].setupPages(totalPages,allPages,page);

                    }

                    scope.$apply(function(){
	
						
						
                        //Hook de prontidao de pagina
                        var initShooter = new DTable.Hooks.SyncShooter(name,scope);

                        //Dispara o Hook
                        initShooter.fire('ready',{},{});

                    });
					
					if(scope[name].fetchCallback){
					
							scope[name].fetchCallback();
					
					}
					


                };



                scope.$watch(name+'.no_match',function(value){

                    try {

                        if (value) {


                            if (onempty) {


                                scope.$eval(onempty);

                            }

                        } else {


                            if (onmatch) {


                                scope.$eval(onmatch);

                            }


                        }

                    }catch(err){

                        return false;

                    }


                });

                scope.$watch(name+'.busy',function(value){

                   

                    try {

                        if (value) {


                            if (onget_start) {

                                scope.$eval(onget_start);

                            }


                        } else {

                            if (onget_end) {


                                scope.$eval(onget_end);

                            }

                        }

                    }catch(err){

                        return false;

                    }

                });


                scope[name].paging = {};
                scope[name].paging.groups = [];
                scope[name].paging.activeGroup = 0;
                scope[name].paging.totalGroups = 0;

                scope[name].paging.nextGroupAvailable = true;

                scope[name].paging.previousGroupAvailable = true;

                scope[name].paging.activatePrevious = function(){

                    if(scope[name].paging.activeGroup>0){

                        var previousGroup = scope[name].paging.activeGroup-1;
                        //alert(previousGroup);
                        scope[name].paging.activateGroup(previousGroup);

                        var ag = scope[name].paging.groups[scope[name].paging.activeGroup];
                        scope[name].activatePage(ag[0]);

                    }

                };

                scope[name].paging.activateNext = function(){


                    if(scope[name].paging.activeGroup<scope[name].paging.groups.length-1){


                        var ag = scope[name].paging.groups[scope[name].paging.activeGroup];

                        var lp = ag[ag.length-1];
                        var np = lp+1;

                        scope[name].paging.activateGroup(scope[name].paging.activeGroup+1);
                        scope[name].activatePage(np);

                    }

                };


                scope[name].paging.activateGroup = function(index){

                    scope[name].paging.activeGroup = index;

                    scope[name].visiblePages = scope[name].paging.groups[index];

                    if((index > 0)&&(scope[name].paging.groups.length>1)){


                        scope[name].paging.previousGroupAvailable = true;

                    }else{

                        scope[name].paging.previousGroupAvailable = false;

                    }



                    if((index<scope[name].paging.groups.length-1)){

                        scope[name].paging.nextGroupAvailable = true;

                    }else{

                        scope[name].paging.nextGroupAvailable = false;

                    }



                };



                scope[name].paging.activateFirst = function(){


                    scope[name].paging.activateGroup(0);
                    scope[name].activatePage(1);


                };

                scope[name].paging.activateLast = function(){

                    var p = parseInt(scope[name].totalPages);

                    var lastGroup = scope[name].paging.groups.length-1;

                    //alert(lastGroup);

                    scope[name].paging.activateGroup(lastGroup);
                    scope[name].activatePage(p);
                    


                };

                scope[name].setupPages = function(totalPages,allPages,pageNumber){

                    scope[name].totalPages = totalPages;

                    var groups = [];
                    var groupsMap = {};

                    var counter = 1;
                    var group = [];

                    var nextGroupIndex = 1;
                    var currentGroupIndex = 0;

                    for(var i=0;i<allPages.length;i++){

                        var pageNumber = allPages[i];


                        //Ultima pagina do grupo
                        if(counter===scope[name].pages){

                            //Existem mais paginas depois desta
                            if(i<allPages.length-1){

                                groupsMap[pageNumber] = nextGroupIndex;

                            }



                        }else{

                            //Qualquer pagina do grupo
                            groupsMap[pageNumber] = currentGroupIndex;

                        }


                        //Ainda ha espaco no grupo
                        if(counter<=scope[name].pages){

                            group.push(pageNumber);
                            counter++;


                        }else{

                            //Grupo esta cheio
                            group.push(pageNumber);

                            nextGroupIndex++;
                            currentGroupIndex++;
                            counter =1;
                            groups.push(group);

                            group = [];

                        }


                    }


                    //Ultimo grupo tem defice de paginas
                    if(groups.indexOf(group)==-1){


                        if(currentGroupIndex>0){

                            var diferenca = scope[name].pages - group.length;

                            var sobra = scope[name].pages-diferenca;


                            var limite = sobra+diferenca;

                            var lastGroup = groups[currentGroupIndex-1];

                            var groupRecreation = [];

                            for(var y = sobra;y<=limite;y++){

                                groupRecreation.push(lastGroup[y]);

                            }

                            for(var y=0;y<group.length;y++){

                                groupRecreation.push(group[y]);

                            }


                            groups.push(groupRecreation);


                        }else{


                            groups.push(group);

                        }



                    }


                    scope[name].paging.groups = groups;
                    scope[name].paging.activeGroup = 0;
                    scope[name].paging.totalGroups = groups.length;
                    scope[name].paging.activateGroup(0);


                };



                //Mostra os dados de uma pagina
                scope[name].showPage = function(number){

                    return data;

                };

                scope[name].update = function(){



                };

                //Recebe dados que fazem match do filtro
                scope[name].dataMatch = function(data){

                    scope[name].busy = false;
					scope[name].searching = false;
                    //Yapiys.messages.log(' mached : '+data.length);

                };


                scope[name].pageData = function(n,data,total,universe,range){


                    scope[name].range = range;
                    scope[name].matches = total;

                    //Hook de chegada de dados
                    var initShooter = new DTable.Hooks.SyncShooter(name,scope);

                    //Parametros do Hook
                    var hookParams = {data:data,universe:universe,total:total,page:n};

                    initShooter.fire('data',{},hookParams);

                    var writable = initShooter.getUpdates();

                    //Pega os dados actualizados pelas extensoes
                    data = writable.data;
                    total = writable.total;
                    universe = writable.universe;
                    n = writable.page;

                    scope[name].busy = false;
					scope[name].searching = false;
					
					if(scope[name].onFetchEnd){
						
						scope[name].onFetchEnd();
											
					}
					
                    scope[name].loadData(n,total,universe,data);

                };

/*

                if(state){

                    scope[name].limits = state.limit;
                    scope[name].filter.string = state.query;
                    scope.$apply();


                }
*/



                //Inicializa os plugins usando um Hook
                var initShooter = new DTable.Hooks.SyncShooter(name,scope);
                initShooter.fire('init',{attrs:attrs},{html:html});
                var writable = initShooter.getUpdates();


                //Pega o html modificado pelas extensoes
                html = writable.html;

                //Elemento Angular
                var angularElement = angular.element(html);

                //Compila o HTML
                var compiled = $compile(angularElement);

                element.html(angularElement);

                compiled(scope);


                //Not in scope 
                /*
                if(!Yapiys.$this[name]){

                     Yapiys.$this[name] = scope[name];

                }*/

                Yapiys.$this[name] = scope[name];
               


            }

        };

    });


};


DTable.initialize = function(moduleInstance){

    if(moduleInstance){

        DTable.angular.initialize(moduleInstance);

    }else{

        DTable.angular.initialize();

    }


};

//Eventos do DTable
DTable.Hooks = {};


//Listeners de hooks de datatables
DTable.Hooks.listeners = { all : [] };

DTable.Hooks.init = function(name,plugins){


    if(name.valueOf()!=='all'){

        DTable.Hooks.listeners[name] = plugins;

    }

    DTable.Hooks.parameters[name] = {};


};

//Adiciona um listener de hooks a um target
DTable.Hooks.addListener = function(hooksTarget,extensionName){

    if(DTable.Hooks.listeners.hasOwnProperty(hooksTarget)){

        DTable.Hooks.listeners[hooksTarget].push(extensionName);

    }else{

        DTable.Hooks.listeners[hooksTarget] = [extensionName];

    }

};



//Adiciona um listener de Hooks de todas DTables
DTable.Hooks.addGlobalListener = function(extensionName){


    DTable.Hooks.addListener('all',extensionName);


};


DTable.Hooks.parameters = {};

DTable.Hooks.invoke = function(extensionName,hookName,dtableName){


    //Readable parameter
    var readable = DTable.Hooks.parameters[dtableName][hookName].readable;

    //Writable parameter
    var writable = DTable.Hooks.parameters[dtableName][hookName].writable;

    //DTable Scope
    var scope = DTable.Hooks.parameters[dtableName].scope;

    //Extension Instance
    var extensionInstance = DTable.Extensions.getInstance(extensionName);

    if(extensionInstance){


        //Instance Handles Hooks
        if(extensionInstance.hooks){


            //Hook Handled
            if(extensionInstance.hooks.hasOwnProperty(hookName)){

                //Invoke hook
                extensionInstance.hooks[hookName].call(extensionInstance,readable,writable,scope);

                //Updates the writable
                DTable.Hooks.parameters[dtableName][hookName].writable = writable;

            }

        }

    }

};

//Dispara um Hook de um datatable
DTable.Hooks.fire = function(name,hookName,readable,writable,scope){


    //Nome da datatable
    readable.name = name;

    DTable.Hooks.parameters[name][hookName] = {};

    DTable.Hooks.parameters[name][hookName].writable = writable;

    DTable.Hooks.parameters[name][hookName].readable = readable;

    DTable.Hooks.parameters[name].scope = scope;


    //Table Hook Listeners
    var tableListeners = DTable.Hooks.listeners[name];


    //Global Hook Listeners
    var globalListeners = DTable.Hooks.listeners['all'];



    //Invoke Global Hooks Listeners
    for(var listenerIndex in globalListeners ){


        var extensionName = globalListeners[listenerIndex];


        //Invoke the hook in the extension
        DTable.Hooks.invoke(extensionName,hookName,name);


    }

    //Invoke DTable specific listeners
    for(var listenerIndex in tableListeners ){

        var extensionName = tableListeners[listenerIndex];

        //Invoke the hook in the extension
        DTable.Hooks.invoke(extensionName,hookName,name);


    }

};


//Gets the most updated writable parameter
DTable.Hooks.getUpdates = function(name,hookName){

    if(DTable.Hooks.parameters.hasOwnProperty(name)){


        if(DTable.Hooks.parameters[name][hookName]){

            return DTable.Hooks.parameters[name][hookName].writable;

        }

    }

    return false;

}

DTable.Hooks.SyncShooter = function(name,scope){

    this.tableName = name;

    this.scope = scope;

    this.lastInvoked = false;

    this.fire = function(hook,read,write){

        this.lastInvoked = hook;

        DTable.Hooks.fire(this.tableName,hook,read,write,this.scope);

    };


    this.getUpdates = function(){

        return DTable.Hooks.getUpdates(this.tableName,this.lastInvoked);

    };


};

//Responsavel por buscar dados no servidor
DTable.DataSource = {};


DTable.readState = function(name){

    try {

        var token = DTable.getStateName(name);

        if (localStorage.hasOwnProperty(token)) {

            var state = JSON.parse(localStorage[token]);

            return state;

        } else {

            return false;

        }

    }catch(err){

        return false;

    }



};

DTable.getStateName = function(name){

    var url = Yapiys.Internal.getURL(Yapiys.Internal.nextViewPath);
    var token = 'dtable::'+name+'::'+url;

    return token;



};

DTable.saveState = function(name,limit,page,query){

    try {

        var token = DTable.getStateName(name);
        var state = {limit: limit, page: page, query: query};

        localStorage.setItem(token, JSON.stringify(state));


    }catch(err){

        return false;

    }


};


//Configuracoes
DTable.DataSource.configs = {

    example: {

        url : "data.php",

        method : 'GET'

    }
};

DTable.DataSource.interceptors ={};


//Implementacaos de fonte de dados
DTable.DataSource.implementations = {};


//Fonte de dados abstracta
DTable.DataSource.Abstract = {

    callback : false,

    page : false,

    order : false,

    onFetch_ : function(q,page,limit,abortable){



    },

    //Vasculha os dados
    fetch : function(q,page,limit,order,callback,abortable){


        this.callback = callback;

        this.page = page;

        this.order = order;

        this.onFetch(q,page,limit,abortable);



    },

    //Passa os dados para o callback
    dataMatch : function(total,matches,range,data){

        this.callback(this.page,total,matches,range,data);


    }


};



//Adiciona uma fonte de dados
DTable.setDataSource = function(name,obj,interceptor){

    DTable.DataSource.configs[name]=obj;

    if(interceptor){

        DTable.DataSource.interceptors[name] = interceptor;


    }

};


//Implementa uma fonte de dados
DTable.implementDataSource = function(name,obj){

    DTable.DataSource.configs[name]=obj;
    $.extend(obj,DTable.DataSource.Abstract);

    DTable.DataSource.implementations[name] = obj;

};

DTable.DataSource.get = function(name){

    //Ajax request
    if(DTable.DataSource.configs.hasOwnProperty(name)){


        return DTable.DataSource.configs[name];

        //Implementacao de um DataSource
    }else if(DTable.DataSource.implementations.hasOwnProperty(name)){

        return DTable.DTable.implementations[name];

    }else{

        return false;

    }
};


DTable.DataSource.getInterceptor = function(name){

    //Interceptador do request
    if(DTable.DataSource.interceptors.hasOwnProperty(name)){


        return DTable.DataSource.interceptors[name];
    }

    return false;

};



DTable.DataSource.retrieving = {};

DTable.DataSource.preventMultipleRequests = function(name){


    //Ja existe uma requisicao para esta instancia DTable
    if(DTable.DataSource.retrieving.hasOwnProperty(name)){

        Yapiys.messages.warn('aborting request to '+name);
        //Aborta a requisicao
        DTable.DataSource.retrieving[name].abort();

    }

};


DTable.DataSource.workingOn = function(dsource,scope,connection){

    Yapiys.messages.warn('starting request to '+dsource);
    DTable.DataSource.retrieving[dsource] = connection;

}


DTable.DataSource.workDone = function(dsource,scope){

    Yapiys.messages.warn('completed request to '+dsource);
    delete DTable.DataSource.retrieving[dsource];

}


//Faz a requisicao ajax e obtem os dados
DTable.DataSource.retrieve = function(name,dtable,query,params,scope){

    var datasource = DTable.DataSource.get(name);

    //DataSource desconhecido
    if(!datasource){

        return;

    }        


    DTable.DataSource.preventMultipleRequests(name);

    if(!datasource.fetch){

            //Obtem as configuracoes da fonte de dados
            var configs = datasource;


            if(!(configs.url)){

                return;

            }

            params.q = query;

            params.limit = scope[dtable].limit;


            var ajaxParams = {

                url : configs.url,
                type: 'GET',
                data : params,

                beforeSend : function(){


                },

                success : function(response){

                    //DTable.DataSource.workDone(name,scope);

                    try{


                        var server = JSON.parse(response);

                        if(params.page){

                            scope[dtable].pageData(params.page,server.data,
                                server.matches,server.universe,server.range);

                        }else{

                            scope[dtable].dataMatch(server.data);

                        }


                    }catch(err){

                        if(params.page){

                            scope[dtable].pageData(params.page,[],0)

                        }else{

                            scope[dtable].dataMatch([]);


                        }

                    }


                },

                complete : function(){

                    DTable.DataSource.workDone(name,scope);

                }

            };



            if(configs.jsonp){

                ajaxParams.jsonp = configs.jsonp;

            }


            if(configs.cache){

                ajaxParams.cache = configs.cache;

            }

            //Ordering by something
            if(scope[dtable].ordering_by!=-1&&(scope[dtable].ordering_asc||scope[dtable].ordering_desc)){


                ajaxParams.data.ordering_by = scope[dtable].ordering_by;

                //Order ascending
                if(scope[dtable].ordering_asc){

                    ajaxParams.data.ordering = 1;

                }else{

                    //Order descending
                    ajaxParams.data.ordering = 0;

                }

                //alert(JSON.stringify(ajaxParams));

            }


            var interceptor = DTable.DataSource.getInterceptor(name);


            var completed_the_job = function(){

                    //Interceptador
                    if(interceptor){

                        //Executar intercecao
                        interceptor.call({},ajaxParams,dtable);

                    }

                    var con = $.ajax(ajaxParams);
					
                    DTable.DataSource.workingOn(name,scope,con);
					
					


            };


            //View is not compiled and was specified an interceptor
            if(Yapiys.preparing&&interceptor){


                //completed_the_job();
                //When finish compiling 
                Yapiys.on_ready(completed_the_job);


            }else{


                //Yapiys view was already compiled
                completed_the_job();


            }

       



    }else{

        //Quando o fetch terminar
        var callback = function(page,total,matches,range,data){

            DTable.DataSource.workDone(name,scope);

            if(params.page){
                
                scope[dtable].pageData(page,data,matches,0,range);

            }else{

                scope[dtable].dataMatch(data);
            }
			

        };  

        //Para abortar operacao
        var abortable = {};

        var page = params.page;
        var limit = scope[dtable].limit;

        var order = {};

        //Ordering by something
        if(scope[dtable].ordering_by&&(scope[dtable].ordering_asc||scope[dtable].ordering_desc)){

            order.ordering_by = scope[dtable].ordering_by;

            //Order ascending
            if(scope[dtable.ordering_asc]){

                order.ordering = 1;

            }else{

                //Order descending
                order.ordering = 0;

            }

        }

        DTable.DataSource.workingOn(name,scope,abortable);

        //Implementacao de datasource
        datasource.fetch(query,page,limit,order,callback,abortable);


    }

};


DTable.Extensions = {};

DTable.registeredExtensions = {};

DTable.Extensions.register = function(controller){

    if(controller.hasOwnProperty('name')){

        var name = controller.name;

        DTable.registeredExtensions[name] = DTable.extend(controller);

    }

};


DTable.Extensions.getInstance = function(name){

    if(DTable.registeredExtensions.hasOwnProperty(name)){

        return DTable.registeredExtensions[name];

    }

    return false;

};


DTable.Extension  = {

    getSettings : function(attrs){

        if(attrs.hasOwnProperty('@'+this.name)){

            var settings = attrs['@'+this.name];


            try{

                var settings_obj = JSON.parse(settings);

                return settings_obj;

            }catch(err){

                return false;
            }

        }

        return false;

    },

    setHtml : function(writable,readable){


        var keyBody = '@'+this.name+'Body';
        var keyHead = '@'+this.name+'Head';

        var newHtml = writable.html;

        newHtml = newHtml.replace(new RegExp(keyBody,'g'),readable.html);

        //Substituir o header
        if(readable.head){

            newHtml = newHtml.replace(new RegExp(keyHead,'g'),readable.head);

        }

        writable.html = newHtml;

    }

};

DTable.extend = function(ext){

    var extend = $.extend(ext,DTable.Extension);

    return ext;

};


var ext = {

    init : function(name,html,scope){

        return false;

    },


    hooks : {


        fetch : function(readable,writable,scope){

            //Fazendo fetch de dados

        },


        init : function(readable, writable,scope){

            //Inicializando dataTable

        },

        data : function(readable,writable,scope){

            //Chegada de dados do servidor

        }


    }


};

/*
DTable.Extensions.register('columns',ext);
DTable.Hooks.addGlobalListener('columns');
*/
DTable.angular.initialize();




/**
 Extensao do DTable para fazer checks em uma DTable
 **/



var checker = {



    html: '<div class="checkbox dtable-check" style="margin: 0px" ><label><input ng-click="@name.checker.click(@rowName)" type="checkbox" ng-checked="@name.checker.isChecked(@rowName)" checked="checked"><span class="text"></span></label></div>',
    //html : '<label class="i-checks m-b-none"><input ng-checked="@name.checker.isChecked(@rowName)" ng-click="@name.checker.click(@rowName)" type="checkbox"><i></i></label>',
    //head : '<label class="i-checks m-b-none"><input ng-disabled="@name.data.length==0" ng-model="@name.checker.all" ng-checked="@name.checker.areAllChecked&&@name.data.length>0" ng-click="@name.allCheck()" type="checkbox"><i></i></label>',
    head : '<div class="checkbox dtable-check" style="margin: 0px" ng-click="@name.allCheck()"><label><input ng-model="@name.checker.all" ng-disabled="@name.data.length==0" type="checkbox" ng-checked="@name.checker.areAllChecked&&@name.data.length>0"  checked="checked"><span class="text"></span></label></div>',

    name : 'checker',

    hooks : {


        //Desmarca todas rows no refresh
        refresh : function(readable,writable,scope){

            var name = readable.name;

            scope[name].checker.checked=[];
            scope[name].allChecked = scope[name].checker.checked;
            scope[name].checkedCount = scope[name].checker.checked.length;

            scope.$apply(function(){


            });

        },


        fetch : function(readable,writable,scope){

            //Fazendo fetch de dados

        },


        init : function(readable, writable,scope){


            var name  = readable.name;

            scope[name].checkedItems = function(){

                return this.checker.checkedItems;

            };
            scope[name].checker = {

                areAllChecked : false,

                checked:[],
                checkedItems : [],

                click : function(item){


                    if(typeof item !='undefined'){

                        var id = this.idRow(item);

                        if(!id){

                            return false;

                        }

                        if(this.isChecked(item)){

                            var indexOf = this.checked.indexOf(id);

                            this.checked.splice(indexOf,1);
                            this.checkedItems.splice(indexOf,1);

                        }else{

                            this.checked.push(id);
                            this.checkedItems.push(item);

                        }

                    }


                },

                isChecked : function(item){

                    if(typeof item !='undefined'){

                        var id = this.idRow(item);

                        if(!id){

                            return false;

                        }

                        return this.checked.indexOf(id)!=-1;

                    }

                },

                idProperty : false,

                idRow : function(item){

                    if(item && typeof item != 'undefined'){

                        try{

                            var val = eval('item.'+[this.idProperty]);

                            return val;

                        }catch(err){

                            return false;

                        }


                    }


                    return false;

                }


            };



            //Fazer check em um item
            scope[name].check = scope[name].checker.click;

            //Todos items com check
            scope[name].allChecked = scope[name].checker.checked;

            //Desmarcar todos items
            scope[name].uncheckAll = function(){

                scope[name].checker.checked = [];
                scope[name].checker.checkedItems = [];
                scope[name].allChecked = scope[name].checker.checked;
                scope.$apply();

            };

            scope[name].allCheck = function(){

                //Marcar ou desmarcar todos
                var checkAll = scope[name].checker.all;

                scope[name].data.forEach(function(item,index){


                    var itemChecked = scope[name].checker.isChecked.call(scope[name].checker,item);

                    //Todos items devem ser marcados [exte nao esta marcado]
                    if(checkAll&&!itemChecked){

                        scope[name].check.call(scope[name].checker,item);


                        //Todos items devem ser desmarcados [Este esta marcado]
                    }else if(!checkAll&&itemChecked){

                        scope[name].check.call(scope[name].checker,item);

                    }


                });


                scope.$apply(function(){

                    scope[name].allChecked = scope[name].checker.checked;
                    scope[name].checkedCount = scope[name].allChecked.length;

                });


            };


            var allCheckedFunction = function(){
				
				if(!scope[name]){
					
					return;
					
				}

                var value = scope[name].data;

                if(typeof value!='undefined'){

                    if(Array.isArray(value)){

                        var checkedCount = 0;

                        value.forEach(function(item,index){

                            if(scope[name].checker.isChecked.call(scope[name].checker,item)){

                                checkedCount++;

                            }


                        });


                        //Todas rows estao checkeds
                        if(checkedCount==value.length){

                            Yapiys.messages.log('Yeas. Are all checked');
                            scope[name].checker.areAllChecked = true;

                        }else{

                            scope[name].checker.areAllChecked = false;

                        }

                    }


                }


            };

            //Verifica se esta tudo checked ou nao
            scope.$watch(name+'.checker.checked.length', function(chk){

                allCheckedFunction();

            });

            scope.$watch(name+'.data.length', function(chk){

                allCheckedFunction();

            });


            //Actualiza o total de itens com check
            scope.$watch(name+'.checker.checked.length',function(val){

                if(typeof val=='undefined'){

                    return false;

                }

                scope[name].checkedCount = val;

            });

            //Total de itens com check
            scope[name].checkedCount = 0;


            //Obtem as configuracoes do Checker
            var settings = this.getSettings(readable.attrs);

            var rowName = scope[name]['row_name'];

            var readable = {};

            //Configuracoes fornecidas
            if(settings){

                //Foi passado um HTML
                if(settings.html){

                    this.html = settings.html;

                }


                //fUNCAO para identificar cada registro
                if(settings.trackBy){

                    scope[name].checker.idProperty = settings.trackBy;

                }





                //Substitui o nome da row
                readable.html=this.html.replace(new RegExp('@rowName','g'),rowName);




            }


            //Substitui o nome da dtable
            readable.html= readable.html.replace(/@name/g,name);

            if(this.head){

                readable.head=this.head.replace(/@name/g,name);

            }


            //Foi passado o HTML do DTable
            if(writable.html){

                //Modifica o Html
                this.setHtml(writable,readable);

            }


        },

        data : function(readable,writable,scope){

            //Chegada de dados do servidor

        }


    }


};


//Regista a extensao do DTable
DTable.Extensions.register(checker);
