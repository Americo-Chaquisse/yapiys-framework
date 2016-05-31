/**
 * @author: Mario Junior
 * @author: Americo Chaquisse
 * @author: Romildo Cumbe
 *
 * WELCOME TO YAPIYS FRAMEWORK
 *
 * by the way. DO NOT CHANGE ANYTHING IN THIS FILE
 *
 * All Client Side Stuff is handled here
 *
 */

var contextApplied = false;


//Biblioteca Yapiys
var Yapiys = {};

Yapiys.controllerReady=false;


//Configurar o yapiys
Yapiys.config = function(params){

    if(params.enableAjaxLoader){

        Yapiys.UI.loader = params.enableAjaxLoader;

    }

};


//Instancias de plugins
Yapiys.pluginsInstances = [];
Yapiys.pluginsMaterials = [];

Yapiys.plugins = {};

Yapiys.getPlugin = function(name){


    if(Yapiys.plugins.hasOwnProperty(name)){


        return Yapiys.plugins[name];

    }

    return false;


};

Yapiys.getPlugins = function(){

    var setters = {};

    for(var pluginName in Yapiys.plugins){

        setters['$'+pluginName] = Yapiys.plugins[pluginName].$function();

    }

    return setters;


};


Yapiys.pluginsFireEvent = function(name){

    for(var p in Yapiys.plugins){

        var plugin = Yapiys.plugins[p];

        plugin.handleEvent(name);    

    }

};


Yapiys.Plugin = function(name,constructor,core,pluginEventsHandlers,singleInstance){

    Yapiys.messages.log('creating plugin');

    //Name - nome do plugin
    //conctructor - contrutor que recebe a declaracao feita no controlador do template
    //core - funcao ou objecto core do plugin
    //events - handler de eventos da view
    //singleInstance - indica se a mesma instancia vai sobreviver a todas as views ou se cada uma tera sua propria


    this.name = name;
    this.constructor = constructor;
    this.core = core;
    this.events = pluginEventsHandlers;
    this.singleInstance = singleInstance;

    

    //Obtem o core do plugin
    this.$function = function(){

        if(singleInstance){

            if(Yapiys.pluginsInstances.hasOwnProperty(this.name)){

                if(this.core.hasOwnProperty('instance')){

                    //alert('same old core');

                    return this.core;

                }


                //alert('new core');
                this.core['instance'] = Yapiys.pluginsInstances[this.name];

                return this.core;

            }

        }else{

            //alert('not singleInstance');

            //Controi uma nova instancia
            this.construct();

            this.core['instance'] = Yapiys.pluginsInstances[this.name];

            return this.core;


        }

    };


    this.construct = function(params){

        if(params){

            //Material para contruir a instancia
            this.material = params;

        }else{


            if(!this.material){

                return;

            }

        }

        //Controi a instancia do plugin
         var instance = this.constructor.call(this,this.material);

         //Guarda a instancia do plugin
         Yapiys.pluginsInstances[this.name] = instance;

    };


    //Todos eventos sao tratatos com o contexto do core
    this.handleEvent = function(name){
        
        //Evento tratado
        if(this.events.hasOwnProperty(name)){

            this.events[name].call(this.core);

        }


    };


    if(Yapiys.plugins.name){

        Yapiys.messages.warn('Yapiys plugin duplication : '+name);

        return;


    }

    //Adiciona o plugin a lista de plugins
    Yapiys.plugins[name] = this;

    //Os plugins ainda nao foram carregados
    if(!Yapiys.UI.pluginsLoaded){

        Yapiys.UI.pluginsOnce();

    }


};






//Funcoes Internas ao Yapiys
Yapiys.Internal = {};

//Funcoes o angular
Yapiys.angular ={};

//Define directivas e filtros
Yapiys.angular.js = function(definitions){

     for(var definition_name in definitions){
        
        var definition_value = definitions[definition_name];
        
        
        //Definicoes de directivas
        if(definition_name.valueOf()==='directives'&&typeof definition_value ==='object'){
            
            
            //Lista de directivas
            
            var directives_list = definition_value;
            
            
            //Para cada directiva
            for(var directive_name in directives_list){


                
                //Funcao da directiva
                var directive_function = directives_list[directive_name];
                
                //Trata-se de uma funcao
                if(typeof directive_function === 'function'){

                    //Cria a directiva
                    Yapiys.angular.module.directive(directive_name,directive_function);
                    
                    
                }
                
                
                
            }
            
            
        //Definicoes de filtros
        }else if(definition_name.valueOf()==='filters'&&typeof definition_value==='object'){
            
                //Lista de filtros
                var filters_list = definition_value;
                
                
                for(var filter_name in filters_list){
                   
                   
                   //Funcao do filtro
                   var filter_function = filters_list[filter_name];
                    
                   
                   //Cria o filtro
                   Yapiys.angular.module.filter(filter_name,filter_function);
                   
                   
                }
            
            
            
        //Funcao que executa configuracoes pessoalmente    
        }else if(definition_name.valueOf()==='execute'&&typeof definition_value==='function'){
            
            //Executa a funcao de configuracao e define como this o angularJS e passa como
            //Parametro o modulo principal da aplicacao
            definition_value.apply(angular,Yapiys.angular.module);
            
            
        }
                
     }  
       
    
};


Yapiys.UI={};


Yapiys.UI.root={};


//Cache de interface grafica
Yapiys.UI.cache = {};

Yapiys.globals = {};




//Inicializa a cache
Yapiys.UI.cache.initialize = function(){
    try {

        if (sessionStorage) {

            if (!sessionStorage['yapiys_cache_regs']) {
                sessionStorage['yapiys_cache_regs'] = JSON.stringify(new Array());
            }

        }

    }catch(err){

        return false;

    }
    
};

//Carrega uma view antes de ela ser necessaria
Yapiys.UI.prepareView = function(route_name_or_object){
   
    //Objecto da rota
    var route = false;
    
    //Parametro eh uma string
    if(typeof route_name_or_object ==='string'){
        
        //Pega uma rota com nome igual
        route = Yapiys.routes.get(route_name_or_object);
        
    }else{
        
        //Parametro eh um objecto
        route = route_name_or_object;
        
    }
    
    
    //Rota valida
    if(route){
        
        
        
        //Gera o caminho da rota
        var path = Yapiys.Internal.generateViewPath(route.module,route.controller, route.action);
        
                
        var url = Yapiys.Internal.getURL(route);
        
        //Esta rota ja foi cacheada
        if(Yapiys.UI.cache.stores(url)){
        
            
            return;
            
        }

      
        
        //Faz a requisicao GET
        $.get(url,function(reponse){
            
            
            try{
                
                //Faz parse da response para JSON
                var JSONResponse = JSON.parse(reponse);
                
                //Pega o HTMK
                var html = JSONResponse.markup;
                
                //Coloca na cache
                Yapiys.UI.cache.storeView(url,html);
            
                
            }catch(err){
                
                
            }
            
        
        });
        
    
        
    }    

};


//Cache ligada
Yapiys.UI.cache.on = true;


//Limpa a cache
Yapiys.UI.cache.destroy = function(){
    
    if(sessionStorage){
        sessionStorage.clear(); 
    }
    
};


//Coloca uma view na cache
Yapiys.UI.cache.storeView = function(path,markup){    

    try {

        //Session storage disponivel
        if (sessionStorage && Yapiys.UI.cache.on) {
			
			
			var toStore = $("<div>");
			toStore.html(markup);
			
			$(toStore).find(".yapiys").remove();
			
			var html = $(toStore).html();
			
            sessionStorage.setItem(path, html);

            Yapiys.UI.cache.log(path);


        }

    }catch(err){

        return false;

    }
        
   	
};

Yapiys.UI.cache.log = function(path){

    try {

        if (sessionStorage) {

            var regs = JSON.parse(sessionStorage['yapiys_cache_regs']);
            if (!Array.isArray(regs)) {

                regs = new Array();

            }

            if (regs.indexOf(path) == -1) {
                regs.push(path);
            }

            sessionStorage['yapiys_cache_regs'] = JSON.stringify(regs);

        }

    }catch(err){


        return false;

    }
    
};


Yapiys.UI.cache.cleanup = function(){

    try {

        if (sessionStorage) {

            var regs = sessionStorage['yapiys_cache_regs'];

            if (regs) {

                regs = JSON.parse(regs);

                for (var index in regs) {

                    if (index !== 'removeVal') {

                        var path = regs[index];


                        if (sessionStorage[path]) {

                            delete sessionStorage[path];

                        }


                    }


                }


                sessionStorage['yapiys_cache_regs'] = JSON.stringify(new Array());

            }


        }

    }catch(err){

        return false;

    }
    
};


//Verifica se a cache contem uma determinada view
Yapiys.UI.cache.stores = function(path){
    
     //Session storage disponivel    
    if(sessionStorage&&Yapiys.UI.cache.on){
        
        if(sessionStorage.getItem(path)){
                        
            Yapiys.messages.log('View '+path+' found in the cache');
            return true;
            
        }else{
            
            
            Yapiys.messages.log('View '+path+' NOT found in the cache');
            
            return false;            
            
        }
        
    }else{
        
        Yapiys.messages.log('Cache is disabled');
        
    }    
    
    
};

//Obtem uma view da cahe
Yapiys.UI.cache.fetch = function(path){
    
    //Session storage disponivel    
    if(sessionStorage&&Yapiys.UI.cache.on){
        
        return sessionStorage.getItem(path);
        
    }
    
    
    
};


//Scope actual
Yapiys.$this = false;
Yapiys.Internal.next$this = {};

Yapiys.Internal.$thisInclude = function(name,value){

    Yapiys.Internal.next$this[name] = value;


};

Yapiys.Internal.$thisIncludesDelete = function(){

    Yapiys.Internal.next$this = {};

};

Yapiys.Internal.$thisApply = function(){

    for(var key in Yapiys.Internal.next$this){

        var value = Yapiys.Internal.next$this[key];

        Yapiys.$this[key] = value;

    }

    Yapiys.Internal.$thisIncludesDelete();

};


//Aplicar o scope
Yapiys.flush = function(callback){
    
    Yapiys.$this.$apply(function(){

        if(callback){

            callback.call();

        }

        Yapiys.UI.resetLocation();

    });
    
};

Yapiys.strip = function(obj){

    var json =angular.toJson(obj);

    return JSON.parse(json);

};

Yapiys.empty = function(){

    return angular.copy({});

};



Yapiys.UI.title = false;

//Recarregar sempre o tituo original
Yapiys.UI.reload_title = true;

//Usando o titulo de uma view
Yapiys.UI.view_title=false;

//Definir o titulo da pagina
Yapiys.UI.setTitle = function(title){
    
    $("title").html(title);
    
};

Yapiys.UI.partials_loader = false;

Yapiys.UI.setPartialsLoader = function(loader){

    Yapiys.UI.partials_loader = loader;

};


Yapiys.goto = function(element_id,name){
    var route_command = "Yapiys.route('"+name+"');";
    $("#"+element_id).attr('onClick',route_command);    
};

//Faz bind do clique ou de outro evento de um elemento a uma rota
Yapiys.bind = function(element_id,name,eventName){
    var route_command = "Yapiys.route('"+name+"');";
    if(eventName){
       
       
        if(typeof element_id === 'string'){
            $("#"+element_id).bind(eventName,function(e){

                e.preventDefault();

                eval(route_command);


            });
            
        }else{
            
            $(element_id).bind(eventName,function(e){

                e.preventDefault();

                eval(route_command);

            });
            
        }
    }else{
        $("#"+element_id).attr('onClick',route_command); 
    }
};

//Controlador do template
Yapiys.UI.uimaster=false;

//Closures javascript de views
Yapiys.UI.viewsClosures={};

//Closures que estao a espera que seus parentes sejam declarados
Yapiys.UI.viewsClosuresChildsWaiting = {};

Yapiys.UI.viewsClosuresChildsDeclared = new Array();

Yapiys.UI.viewsClosuresDeclared = new Array();

//Handlers para eventos de contactos
Yapiys.UI.viewsEventsHandlers={};

Yapiys.UI.nextViewContext=false;
Yapiys.UI.nextViewPath=false;
Yapiys.UI.nextDialogContext=false;

Yapiys.UI.master_template = {};

//Regista o controlador do template raiz
Yapiys.UI.master = function(master){        
    Yapiys.UI.master_template=master;
};


Yapiys.UI.master = function(master){    

    Yapiys.UI.master_template = master;

    Yapiys.UI.fireMasterLoadEvent();

    
};

Yapiys.UI.pluginsLoaded = false;
Yapiys.UI.pluginsOnce = function(){

      if(Yapiys.UI.uimaster.$plugins){

        //Constroi instancias para cada um
        for(var pluginName in Yapiys.UI.uimaster.$plugins){


            var material = Yapiys.UI.uimaster.$plugins[pluginName];


            var plugin = Yapiys.getPlugin(pluginName);

            if(plugin){

                Yapiys.messages.log('plugin found '+pluginName);

                //Constroi a instancia do plugin
                plugin.construct(material);

                //var core = plugin.$function();



            }else{

                Yapiys.messages.log('Plugin missing : '+pluginName);

            }


        }



    }else{

        //alert('no plugins');

    }
    

    Yapiys.pluginsLoaded = true;


};

Yapiys.ang = {modules:[],run:false};



//Regista o controlador do template
Yapiys.UI.template = function(master){        
    
    //OLD VERSION
    //Yapiys.UI.uimaster=master;
    
    jQuery.extend(master,Yapiys.UI.master_template);
    Yapiys.UI.uimaster=master;

    //Plugins foram especificados
  
    
};

//Contexto do template
Yapiys.UI.templateContext = false;

//Quando o template for carregado
Yapiys.UI.fireMasterLoadEvent = function(){

    
    //Pega o titulo da aplicacao
    Yapiys.UI.title = $("title").html();


    $(function(){        

        if(Yapiys.UI.uimaster){

            if(Yapiys.UI.uimaster.hasOwnProperty('init')){

                Yapiys.UI.uimaster.init();

            }
			
			
			
        }else{
			
			
		}
              
                
    });

};



//Quando o template for carregado
Yapiys.UI.fireMasterConfigEvent = function(params){
    
    
    if(Yapiys.UI.uimaster){
        
        
        if(Yapiys.UI.uimaster.hasOwnProperty('init')){
            
            Yapiys.UI.uimaster.init();
            
        }
        
        if(Yapiys.UI.uimaster.hasOwnProperty('config')){
            
            Yapiys.UI.uimaster.config(params);
            
        }
        
        
         if(Yapiys.UI.uimaster.hasOwnProperty('preLoad')){



                Yapiys.UI.uimaster.preLoad();


         }
        
        
    }
    
};


Yapiys.Internal.setNextViewPath = function(module_name,model,action,params){

    if(!Array.isArray(params)){

        params = [params];

    }

    var nvp = {module_name:module_name,model:model,controller:model,action:action};
    Yapiys.Internal.nextViewPath= nvp;

    var new_path = {module_name:module_name,model:model,controller:model,action:action,params:params};
    var path_with_params = Yapiys.Internal.getURL(new_path);

    Yapiys.logRoute(path_with_params,new_path);
};

Yapiys.Internal.generateViewPath = function(module_name,model,action){  
    var view_path=false;
    
    if(module_name){
        
        view_path = module_name+"_"+model+"_"+action;  
        
    }else{
        
        view_path = model+"_"+action;  
        
    }
    
    
    return view_path;
    
};

Yapiys.Internal.getClosures = function(module_name,model,action){
      
    var view_path = Yapiys.Internal.generateViewPath(module_name,model,action);
        
    if(Yapiys.UI.viewsClosures.hasOwnProperty(view_path)){     
        
        //Obtem os closures da view
        var closures_array = Yapiys.UI.viewsClosures[view_path]; 
        
        return closures_array;
        
    }else{
        
        //Nenhum closure encontrado
        return false;
        
    }
    
};

//Regista um closure de uma view
Yapiys.Internal.pushViewClosure= function(module_name,model,action,closure){
    var view_path = Yapiys.Internal.generateViewPath(module_name,model,action);
       
    if(Yapiys.UI.viewsClosures.hasOwnProperty(view_path)){     

        var closures_array = Yapiys.UI.viewsClosures[view_path];   
  
               
        var cls = closures_array[0];
      
        
        for(var closure_index in closure){
            
            var closure_value = closure[closure_index];

            cls[closure_index] = closure_value;            
            
        }
        
        
        //closures_array.push(closure);
        Yapiys.UI.viewsClosures[view_path]=closures_array;   
       
        
        
    }else{
        
        
         var closures_array = new Array();        
         closures_array.push(closure);
         Yapiys.UI.viewsClosures[view_path]=closures_array;   
        
        
    }
    
    
};


//Um closure fica a espera de um outro closure ser declarado
Yapiys.Internal.closureWaiting = function(view_route,closure_declaration){
    
 
    var path = Yapiys.Internal.generateViewPath(view_route.module_name,view_route.model,view_route.action);

    var closures_waiting_array = false;
    
    
    //Ja existem closures a espera da declaracao do mesmo closure
    if(Yapiys.UI.viewsClosuresChildsWaiting.hasOwnProperty(path)){
        
        closures_waiting_array = Yapiys.UI.viewsClosuresChildsWaiting[path];
        
        
    }else{
        
        //Primeiro closure a espera da declaracao deste closures
         closures_waiting_array = new Array();
        Yapiys.UI.viewsClosuresChildsWaiting[path] = closures_waiting_array;
        
        
    }
    
    //Coloca este closure na lista de closures em espera
    closures_waiting_array.push(closure_declaration);
    
};


//Declara todos os closures que nao foram declarados
Yapiys.Internal.declareAllUndeclared = function(){
                    
                for(var path in Yapiys.UI.viewsClosuresChildsWaiting){


                    var closures_waiting_array = Yapiys.UI.viewsClosuresChildsWaiting[path];    
                    
                    if(Yapiys.UI.viewsClosuresChildsDeclared.indexOf(path)==-1){
                    

                        for(var closures_waiting in closures_waiting_array){

                            var closure_declaration = closures_waiting_array[closures_waiting]();

                        } 
                        
                    }
                    
                }
    
            
        Yapiys.UI.viewsClosuresChildsDeclared.push(path);

    
};

//Um closure que estava sendo esperado, chama os closures em espera
Yapiys.Internal.declareClosureChilds = function(view_route){
    
    
    var path = Yapiys.Internal.generateViewPath(view_route.module_name,view_route.model,view_route.action);
    
    //alert('declaring closure childs : '+path);
    
        if(Yapiys.UI.viewsClosuresChildsWaiting.hasOwnProperty(path)){

            

                var closures_waiting_array = Yapiys.UI.viewsClosuresChildsWaiting[path];    

     
                 //Considera os filhos deste closure declarados
                Yapiys.UI.viewsClosuresChildsDeclared.push(path);
   

                for(var closures_waiting in closures_waiting_array){
                 
                      var closure_declaration = closures_waiting_array[closures_waiting]();

                } 
                
            
               

        }else{
            
            //alert('stop declaring closures');
            
        }

            
    
};

Yapiys.Internal.viewController=false;

Yapiys.Internal.startedup=false;

Yapiys.Internal.prepareNextViewContext = function(dialog,context_variables,markup,startup){


    //Inicio de Prevencao de inicializacao dupla
    if(startup){


        if(Yapiys.Internal.startedup){

            return;

        }else{

            Yapiys.Internal.startedup = true;

        }

    }

    //Fim de prevencao



    //Carregar os plugins para o controller da view
    var plugins = Yapiys.getPlugins();

    for(var pluginName in plugins){

        context_variables[pluginName] = plugins[pluginName];

    }

    //----------------------
        
    if(dialog){
        
        var context=false;

        
        Yapiys.UI.ngApp.controller('dialog_controller',function($scope){
            

            for(var context_variable_name in context_variables){
          

                    var context_variable_value = context_variables[context_variable_name];
                    $scope[context_variable_name]=context_variable_value;   
                    
                
            }
                
                
            
            context=$scope;
        });
        
            
        Yapiys.UI.nextDialogContext=context;
        
    }else{
        
         
        //Ainda nao existe controllador
         if(!Yapiys.Internal.viewController){
  
                     
             //Cria o controlador
             Yapiys.Internal.createController(dialog,context_variables,markup);

             
          }else{


              Yapiys.Internal.callMeBack(dialog,context_variables,markup);
              
              
          }
        
        
          
  
       
        }
        

    
};


Yapiys.Internal.callMeBack = function(dialog,context_variables,markup){



        //Comandos

        if(context_variables.$commands){

            var commands = context_variables.$commands;

            for(var commandName in commands){

                if(commandName!='removeVal'){

                    var commandParams = commands[commandName];

                    if(Yapiys.commands.run(commandName,commandParams)){

                        return;

                    }

                }


            }

        }
    

        var html=false;
            
          if(markup){
                
              html=markup;

               
          }else{              
              
                                          
              if(viewToLoad.extend){
                  
                  var child_route = viewToLoad.route;          
                  var parent_name = viewToLoad.parent_view_name;
                  
                  var parent_route = JSON.parse(JSON.stringify(child_route));                  
                  parent_route.action = parent_name;

                  
                  var child_path = Yapiys.Internal.getURL(child_route); 
                 
                  
                  if(Yapiys.UI.locker.isLocked(child_path)){
                                  
                   //Child path is locked
                    Yapiys.UI.locker.waitFor(child_path,'ntr',function(){
                         
               
                         Yapiys.Internal.callMeBack(dialog,context_variables);
                                      
                         
                     });                 
                      
                  }
                      
              
                                  
                //View is cached
                if(Yapiys.UI.cache.stores(child_path)){
                    
                    //Fetches the view from cache
                    html = Yapiys.UI.cache.fetch(child_path);   
                    
                }else{
                    
                                    
                    return;
                    
                }
          
                                                      
              }else{
                
              
                 html=viewToLoad.html;  
                  
              }
                                 
              
          }
        
                
          //Pega o controlador
           var context=Yapiys.Internal.viewController;   

           Yapiys.UI.closeView(); 
           
           //Limpa o contexto
           Yapiys.Internal.cleanContext(context);  


        
           //Faz bind dos parametros
           var load = Yapiys.Internal.bindParamsAndClosures(context,context_variables);
            


           //Preloads
           if(load.preLoad){

               html = Yapiys.Internal.preLoad(load.preLoad,html,context);
                     
                   
           }


            //Dispara o preLoad para os plugins
            Yapiys.pluginsFireEvent('preLoad');


           //PostLoads
           if(load.postLoad){
               
               html = Yapiys.Internal.postLoad(load.postLoad,html);
                   
           }
            

            //Dispara o postLoad para os plugins
            Yapiys.pluginsFireEvent('postLoad');


           //Se tiver sido definido um titulo
           if(context.hasOwnProperty('_title')){

               //Define o titulo passado pela view
               Yapiys.UI.setTitle(context._title);

               
           }else{
            
               //Nao foi definido um titulo
               
               if(Yapiys.UI.reload_title===true){
            
                   
                   //define o titulo default
                   Yapiys.UI.setTitle(Yapiys.UI.title);
                   
               }
               
           }
        
                       
           if(html){


               context.reload(html);

		       Yapiys.$this = context;

               Yapiys.Internal.$thisApply();


                Yapiys.$this.solve = Yapiys.solve;


                if(load.postLoad){
                
                    Yapiys.Internal.runPostLoad(load.postLoad,context);
                    
                   
                }



                //alert('triggering');
                Yapiys.trigger_ready();
               
            
               
           } 
                
                                 
           Yapiys.UI.nextViewContext=context;
            //Yapiys.UI.setLocation('demo/home',route_object);
           
          
    
};

Yapiys.solve = function(func,toInject){

        var $injector = angular.injector(['ng','yapiys']);

        if(toInject) {

            func.$inject = toInject;

        }

        $injector.invoke(func);

};

Yapiys.Internal.createController = function(dialog,context_variables,markup){
        Yapiys.UI.ngApp.controller('view_controller',function($scope,$compile,$rootScope){
           
           
                //Contexto do template
                Yapiys.UI.root=$rootScope;
                
                if(Yapiys.UI.uimaster){
                    
                    var template_private_names = ['preLoad','init','config'];
                    
                    for(template_variable_name in Yapiys.UI.uimaster){
                            
                    var template_variable_value = Yapiys.UI.uimaster[template_variable_name];    
                    var private_index = template_private_names.indexOf(template_variable_name);
                        
                        if(private_index!=-1){
                            
                           //Variavel privada
                            
                        }else{
                            
                            //Variavel publica
                            Yapiys.UI.root[template_variable_name] = template_variable_value;   
                        }
                        
                        var template_variable_value =Yapiys.UI.uimaster[template_variable_name];
                        Yapiys.UI.root[template_variable_name] = template_variable_value;
                        
                    }
                    
                    
              
                    
                    
                    
                }
           
           
                $scope.self=$scope;                
                $scope.self.compileHTML = function(markup){
                    
                    var consider = markup;
                    var compile_function=$compile(consider);             
                    var compiled_element = compile_function($scope.self);                   
                    return compiled_element;
                    
                };
                
              
                Yapiys.Internal.configureContext($scope,true);
                Yapiys.Internal.viewController=$scope;
            
                if(!Yapiys.controllerReady){


                    Yapiys.controllerReady=true;

                    Yapiys.Internal.callMeBack(dialog,context_variables,markup);
                    
            
                }   
                
                
           
        });
};

Yapiys.Internal.started_up = false;

Yapiys.Internal.configureContext = function(context,create_controller){
    
    context['reload'] = function(html){        
   
            var compiled = this.compileHTML(html); 

            
            var after_apply = false;
            
            
            //Controller acaba de ser criado
            if(create_controller){
                
                //alert('creating controller');

             
                after_apply = function(){


                   //Template possui um hook para quando uma visao for carregada
                   if(Yapiys.UI.root.afterViewLoad){


                              if(typeof Yapiys.UI.root.afterViewLoad ==='function' ){

                                  var resolved_route = Yapiys.routes.resolve(Yapiys.Internal.nextViewPath);                          
                                  Yapiys.UI.root.afterViewLoad.apply(Yapiys.$this,[resolved_route]);

                              }


                     }

                     //Yapiys.trigger_ready();

                
                };
                    
                    
                //Startup
                if(!Yapiys.Internal.started_up){
                    
                    
                    if(Yapiys.UI.root.startup){

                              Yapiys.messages.log('startup event');

                              if(typeof Yapiys.UI.root.startup ==='function' ){

                                  var resolved_route = Yapiys.routes.resolve(Yapiys.Internal.nextViewPath);                          
                                  Yapiys.UI.root.startup.apply(Yapiys.UI.root,[resolved_route]);

                              }


                     }                 


                    Yapiys.Internal.started_up = true;
                    
                }

                context.$safeApply = function(fn) {
                    var phase = this.$root.$$phase;
                    if(phase == '$apply' || phase == '$digest') {
                        if(fn && (typeof(fn) === 'function')) {
                            fn();
                        }
                    } else {
                        this.$apply(fn);
                    }
                };

                //console.log(context);
                if(Yapiys.$this.$apply){

                    Yapiys.$this.$apply();

                }
                
            }

            Yapiys.UI.putView(compiled);
        
    };    
       
    
};

  
//Limpa todas as variaveis do contexto
Yapiys.Internal.cleanContext = function(context){
    
    for(var context_object_name in context){
        
        if(context_object_name.charAt(0)!=='$'&&context_object_name!='reload'&&context_object_name!==
           'compileHTML'&&context_object_name!=='this'&&context_object_name!=='constructor'&&context_object_name!==
           'self'&&context_object_name!=='refresh'){

            if(!Yapiys.UI.uimaster.hasOwnProperty(context_object_name)){

                delete context[context_object_name];

            }
            
        }
           
    }
    
};



Yapiys.commands = {};

//Todos comandos Yapiys
Yapiys.commands.all={};

//Define um comando Yapiys
Yapiys.commands.set = function(key,callback){

    Yapiys.commands.all[key]=callback;

};


//Comando para recarregar a pagina
Yapiys.commands.set('$reload',function(url){

    var path = false;

    if(App.context){


        path=App.base_url+url;

    }else{

        path = document.location.href=App.base_url+url;

    }

    //document.location.href = path;
    //$.ajax({})

    return true;

});



//Update the url shown in browser
Yapiys.commands.set('$url',function(url){

    Yapiys.UI.setLocation(url,{});

});

//Comando para mudar o contexto
Yapiys.commands.set('$context',function(context){


        App.context=context;

        return false;


});


//Executa um commando Yapiys
Yapiys.commands.run = function(key,params){


    if(Yapiys.commands.all.hasOwnProperty(key)){

        var command = Yapiys.commands.all[key];
        return command(params);


    }

    return false;

};

Yapiys.command = Yapiys.commands.run;

Yapiys.Internal.bindParamsAndClosures = function(context,context_variables){
        
    
       var UIRoot = {};
    
    
      
      if(context_variables.$root){
          
          UIRoot = context_variables.$root;
                  
       }


     
       for(var root_variable_name in UIRoot){
           var root_variable_value = UIRoot[root_variable_name];
           Yapiys.UI.root[root_variable_name]=root_variable_value;
                
       }


      Yapiys.UI.root['__t']=__t;
    
    
      for(var context_variable_name in context_variables){
                
                var context_variable_value = context_variables[context_variable_name];
                context[context_variable_name]=context_variable_value;             
      }
    
  
    
       if(Yapiys.Internal.nextViewPath){
                   
                var module_name= Yapiys.Internal.nextViewPath.module_name;
                var model = Yapiys.Internal.nextViewPath.model;
                var action = Yapiys.Internal.nextViewPath.action;
                var postLoad = false;
                var preLoad = false;
                 
           
                //Obtem o grupo de callbacks do servidor
                var server_group =Yapiys.getServerGroup({"module":module_name,"model":model,"action":action});
           
                      
                //Grupo de callbacks valido
                if(server_group){
                           
                    context.php = server_group;
                    
                    
                }
           
                //Obtem os closures da view
                
                var closures=Yapiys.Internal.getClosures(module_name,model,action);
                
                if(closures){
                    
                    
                    for(var closure_index in closures){
                        
                        var closures = closures[closure_index]; 
                        
                        for(var closure_name in closures){
                                                                                                                                
                            var closure_function = closures[closure_name];
                                                                                      
                            context[closure_name] = closure_function;
                            
                            if(closure_name==='postLoad'){
                               
                                postLoad=closures[closure_name];                          
                            }
                            
                            if(closure_name==='preLoad'){
                                
                                preLoad=closures[closure_name];                          
                            }
                            
                            if(closure_name==='window'){
                       
                                Yapiys.Internal.setWindowEvents(closures[closure_name]);
                                
                            }
                            
                            if(closure_name==='document'){
                       
                                Yapiys.Internal.setDocumentEvents(closures[closure_name]);
                                
                            }
                            
                        }
                        
                     
                        
                    }
                   
                   
                   return {
                        
                       postLoad: postLoad,
                       preLoad: preLoad
                   
                   };
                    
                }else{
                    
                    Yapiys.messages.log("Closures not found");
                    
                      return {

                            postLoad: false,
                            preLoad: false

                        };
                    
                    
                }
           
           
    }else{
        

        Yapiys.messages.log("View path not found");
        return {
            
            postLoad: false,
            
            preLoad: false
            
        };
        
    }
    
};


Yapiys.Internal.setWindowEvents = function(eventsHandlers){
        
    for(var event_name in eventsHandlers){
        
        var event_handler = eventsHandlers[event_name];
        
        $(window).bind(event_name,event_handler);
        
    }
        
};


Yapiys.Internal.setDocumentEvents = function(eventsHandlers){
    
    for(var event_name in eventsHandlers){
        
        var event_handler = eventsHandlers[event_name];
        
        $(document).bind(event_name,event_handler);
        
    }    
        
};


Yapiys.UI.loaderCompleted = function(){
    
    
    if(Yapiys.UI.ajaxLoader.current){
        
          Yapiys.UI.ajaxLoader.current.completed();
        
     }
    
};

Yapiys.php = {};
Yapiys.php.getInterface = function(route){
    
    var route_ = Yapiys.routes.resolve(route);
    
    return Yapiys.getServerGroup(route_);
    
};

Yapiys.UI.Interface = function(route){
    
       var route_ = Yapiys.routes.resolve(route);

       var closure = {};
       
       var closures = Yapiys.Internal.getClosures(route_.module,route_.controller,route_.action);
              
       if(closures[0]){
            
            closure = closures[0];
        
       }
       
       
       for(var name in closure){
           
           if(name!=='removeVal'){
              

                var value = closure[name];
                this[name] = value;
           
            }
           
           
       }
       
       this.php = Yapiys.getServerGroup(route_);
    
};

Yapiys.UI.loaderLoading = function(){
    
    
    if(Yapiys.UI.ajaxLoader.current){
        
          Yapiys.UI.ajaxLoader.current.loading();
        
    }
    
};

Yapiys.UI.closeView = function(){
    
    //Esta sendo visualizada uma view
    if(Yapiys.$this){
        
        
        //A view declarou um evento onClose
        if(Yapiys.$this.hasOwnProperty('onClose')){
            
            //Executa o onClose
            var onClose = Yapiys.$this['onClose'];
            
            
            if(typeof onClose ==='function'){
                              
                onClose();   
                
            }else if(typeof onClose ==='object'){
                
                
                for(var itemName in onClose){
                  
                    var item = onClose[itemName];
                    
                    if(typeof item ==='function'){
                        
                        item();
                        
                    }
                                    
                }
                
            }
            
            
        }



        //Dispara o onClose para os plugins
        Yapiys.pluginsFireEvent('onClose');   
        
    }
        
    
};  

Yapiys.UI.displayView = function(markup){
    

    $("#view_content").html(markup);
    $('#view_content').animate({scrollTop:0}, 'slow');
    Yapiys.UI.loaderCompleted();
    //Yapiys.trigger_ready();
};

Yapiys.UI.putView = function(markup){
    
    $("#view_content").html("");
    $("#view_content").append(markup);
    Yapiys.UI.loaderCompleted();
    //Yapiys.trigger_ready();
};

Yapiys.UI.displayDialog = function(markup){
    $("#view_content").html(markup);
};

//Obtem o contexto da visao actual
Yapiys.UI.getContext = function(){
        
};


Yapiys.dialog = function(route_name){
        
        if(route_name){
            
            var route = Yapiys.routes.get(route_name);
            route['dialog']=true;
            
            Yapiys.route(route);
            
        }
};


//Roteamento
Yapiys.routes={};

Yapiys.routes.last =  false;

Yapiys.history = {};

Yapiys.logRoute = function(url,route){
    Yapiys.history[url]=route;
};

Yapiys.getLog = function(url){
    
    if(Yapiys.history.hasOwnProperty(url)){
        
        return Yapiys.history[url];
        
    }else{
        
        return false;
        
    }
    
};



//Registar uma nova rota
Yapiys.routes.new = function(name,object){
    
    if(!Yapiys.routes.hasOwnProperty(name)){
        Yapiys.routes.all[name]=object;
        return object;
    }
    
    return false;
    
};


Yapiys.startFrom = function(route){
    
    Yapiys.routes['entrance_route']=route;
    
};


//Todos rotas
Yapiys.routes.all={};


//Carrega a view novamente
Yapiys.reloadView = function(){

    
    //Ultima rota encontrada
    if(Yapiys.routes.last){
       
        if(Yapiys.routes.last.name){
          
            var name = Yapiys.routes.last.name;
            
            if(Yapiys.routes.last.params){
                
                var params = Yapiys.routes.last.params;                
                Yapiys.route(name,params);
                
            }else{
                
                Yapiys.route(name);
            }
            
        
            
        }
        
    }
    
};


//Obtem a rota
Yapiys.routes.get = function(name){
    
    //O parametro eh uma string 
    if(typeof name==='string'){
        
         //Eexiste uma rota com o nome especificado
         if(Yapiys.routes.all.hasOwnProperty(name)){
             
             
             //Pega o objecto da rota
             return Yapiys.routes.all[name];
             
         }
        
        return false;
    }
    
};


Yapiys.UI.loader = false;



Yapiys.preparing = false;
Yapiys.on_ready_callbacks = [];
Yapiys.on_ready = function(func){

    Yapiys.on_ready_callbacks.push(func);


};


Yapiys.trigger_ready = function(){

    Yapiys.on_ready_callbacks.forEach(function(item,index){

        if(typeof item=='function'){

            item();

        }

    });

    Yapiys.on_ready_callbacks = [];
    Yapiys.preparing = false;

};


//Direcciona o usuario para uma rota
Yapiys.route = function(route_name_or_object,params,getParams){


    //Yapiys is preparing the this context object
    Yapiys.preparing = true;

    Yapiys.routes.last = {name:route_name_or_object,params:params};
    
    var route_object = Yapiys.routes.resolve(route_name_or_object);

    route_object = JSON.parse(JSON.stringify(route_object));



    //Objecto de rota invalido
    if(!route_object){
        
        return false;
        
    }


    if(route_object.params){

        params = route_object.params;
        delete route_object['params'];

    }
    
    //Path da view

    var clean_path = Yapiys.Internal.getURL(route_object);


    if(Yapiys.UI.loaderSettings.hasOwnProperty(clean_path)){
        
        Yapiys.messages.warn('loader settings found for: '+clean_path);
        
        var loader_options = Yapiys.UI.loaderSettings[clean_path];       
        
        if(Yapiys.UI.ajaxLoader.current){
            
            Yapiys.UI.ajaxLoader.current.loading(loader_options);
               
        }        
                
    }else{
        
        
        Yapiys.messages.info('loader settings not found for path: '+clean_path);
        
        //Mostrar o loader por default...
        if(!Yapiys.UI.disableLoader){
            
            
            if(Yapiys.UI.ajaxLoader.current){
                
                Yapiys.messages.log('showing loader by default');                
                var loader_options = Yapiys.UI.ajaxLoader.current.defaults;                
                Yapiys.UI.ajaxLoader.current.loading(loader_options);

            }
            
            
                           
        }
        
    }
	
	    
    
    if(Yapiys.UI.locker.isLocked(clean_path)){
        
        Yapiys.UI.locker.waitFor(clean_path,'getRequest',function(){
                
                
        
        });
        
    }
     
    //Parametros get do request
    if(route_object.get){
        
        getParams = route_object.get;       
        
    }
    
    //Se tiverem sido especificados parametros
    if(params){
        
        //Se parametros forem um array        
        if(Array.isArray(params)){
            route_object['params']=params; 
            
        }else{
            //Se for um simples valor, coloca o valor em um array
            route_object['params']=[params];
            
        }       
    
                
    }
    
    if(getParams){
        
        route_object['get']=getParams;
        
    }
    
    //Gera o path novamente
    var path = Yapiys.Internal.getURL(route_object);

    
    if(!route_object.dialog){

           
        var cached_view = false;
        
        var server_directives = {};
            
            //A view esta na cache
        if(Yapiys.UI.cache.stores(path)){  

            cached_view = Yapiys.UI.cache.fetch(path);
            
            server_directives = {'server_block_view_output':'1'};
            
         }
        
        
        Yapiys.Internal.getRoute(route_object,function(server_response){ 

		
            var context_variables = server_response.context;

            var markup = server_response.markup; 
                 
            //A view nao esta na cache
            if(!Yapiys.UI.cache.stores(clean_path)){  
                
                Yapiys.UI.cache.storeView(clean_path,markup);
            
            }

            //A view esta na cache
            if(Yapiys.UI.cache.stores(clean_path)){  

                markup = Yapiys.UI.cache.fetch(clean_path);
            
            }

            var module_name = false;
            
            if(route_object.module){
                module_name=route_object.module;  
                
            }else if(route_object.module_name){
                module_name=route_object.module_name;                
            }
            
        
            var controller = route_object.controller;
            var action = route_object.action;

            if(params){

                route_object.params = params;

            }


            Yapiys.Internal.setNextViewPath(module_name,controller,action,params);
            Yapiys.UI.setLocation(path,route_object);
            Yapiys.Internal.prepareNextViewContext(route_object.dialog,context_variables,markup);




        },server_directives);
        
    }
    
  
};


//Redirecciona o usuario para determinada rota
Yapiys.redirect = Yapiys.route;

Yapiys.ajaxify = Yapiys.route;


Yapiys.Internal.encodeGetParams = function(params){
    
    var params_uri = "";
    
    var index = 0;
    for(var param_name in params){
        
        var param_value = params[param_name];
        
        if(index!==0){
            
            params_uri = params_uri+'&';
            
        }
        
        params_uri = params_uri+param_name+'='+param_value;
        
        index++;
        
    }
    
    return encodeURI(params_uri);
    
    
};

//Separa um array de parametros por virgula
Yapiys.Internal.encodeParams= function(params){
    var params_uri="";
    var params_total=params.length;
    var last_param_index = params_total-1;
    
   
    for(var param_index in params){
        var param_value =  params[param_index];
        
        if(param_index==='removeVal'){
            continue;   
        }
        
        params_uri=params_uri+param_value;
        
        if(param_index!=last_param_index){
            params_uri=params_uri+"/";
        }
        
    }
    
    return params_uri;   
    
};



Yapiys.Internal.eventURL = function(route,name){
     
    var url = false;
    
    //Aplicacao tem contextos
    if(App.context){
        
        url = Yapiys.Internal.getURL(route,App.context+"/$event/"+name);
        
    }else{
        
        url = Yapiys.Internal.getURL(route,"false/$event/"+name);
        
    }
   
    
    return url;
    
};

//Gera a URL de uma rota
Yapiys.Internal.getURL= function(route,covw) {

    var app_context = App.context;
    var route_url = App.base_url;

    if (app_context) {

        if (!covw) {

            route_url = route_url + app_context + "/";

        }
    }

    //Context overwrite
    if (covw) {

        route_url = route_url + covw + "/";

    }


    if (route.module_name) {

        route_url = route_url + route.module_name + "/";
    }


    if (route.module) {

        route_url = route_url + route.module + "/";
    }


    if (route.controller && !route.model) {

        route_url = route_url + route.controller + "/";

    } else if (route.model && !route.controller) {

        route_url = route_url + route.model + "/";

    } else if (route.controller && route.model) {

        route_url = route_url + route.controller + "/";
    }

    if (route.action) {

        route_url = route_url + route.action;
    }


        if (route.params) {
            route_url = route_url + "/" + Yapiys.Internal.encodeParams(route.params);
        }


    
    //Parametros get
    if(route.get){
        var get_params = Yapiys.Internal.encodeGetParams(route.get);    
        route_url = route_url+"?"+get_params;
        
        Yapiys.messages.log('URL with getParams : '+route_url);
    }


    return route_url;
    
};


Yapiys.UI.currentPage=false;
Yapiys.UI.currentRoute = false;

/**
 * @description: Set new browser url location
 * @param location
 * @param route
 */
Yapiys.UI.setLocation = function (location,route){

    Yapiys.UI.currentPage=location;
    Yapiys.UI.currentRoute = route;

    window.history.pushState(route,Yapiys.UI.title+Math.random(),location);

};

Yapiys.UI.resetLocation = function(){

    if(Yapiys.UI.currentPage){


         window.history.replaceState(Yapiys.UI.currentRoute,Yapiys.UI.currentPage+Math.random(),Yapiys.UI.currentPage);

    }


};


Yapiys.UI.isSameRoute = function(url){
    
    return Yapiys.UI.currentPage===url;
    
};


//Gera URL para API
function api(path){
    
    return App.base_url+"API/"+path;
    
}

//Gera URL para API de um WebService
function partialApi(module,partial,path){

    if(module){

        return App.base_url+"$partials/ws/"+module+'/'+partial+'/'+path;

    }else{

        return App.base_url+"$partials/ws/"+partial+'/'+path;

    }

}




//Efectua uma requisicao get de uma rota
Yapiys.Internal.getRoute = function(route,callback,server_directives){
        
    if(route){

        //Obtem a url da rota
        var route_url = Yapiys.Internal.getURL(route);

        var server_response=false;
    
        var request_params = {};

        if(server_directives){
            
            request_params = server_directives;
            
        }
    
                
        //Efectua a requisicao GET
        $.get(route_url,request_params,function(server){
           
                      
            try{

                            
            //Faz parse da response para JSON
             server_response=JSON.parse(server);

                //console.log(server_response);

                //areturn false;
            
            }catch(err){
                server_response=false;
            }  
            
            
           
            callback(server_response);
            
                        
            
        }).fail(function(){
   
		   if(Yapiys.UI.ajaxLoader.current){
			 
			 if(Yapiys.UI.ajaxLoader.current.hasOwnProperty("onError")){
			  
			  Yapiys.UI.ajaxLoader.current.onError();
			  
			 }
			 
			}
		   
		});
        
    }
    
    
};


Yapiys.UI.setApp = function(app){
    Yapiys.UI.ngApp=app;
    Yapiys.angular.module=app;
    
};




Yapiys.UI.controllers = {};
Yapiys.UI.Controller = {};
Yapiys.UI.cpstyles = {};
Yapiys.UI.readyControllersHandlers={};


Yapiys.UI.partialReady = function(name,callback){



    if(Yapiys.UI.partialsInstances.hasOwnProperty(name)){

        callback(Yapiys.UI.partialsInstances[name]);

        return;

    }

    Yapiys.UI.readyControllersHandlers[name]=callback;
};

Yapiys.UI.componentReady = Yapiys.UI.partialReady;


Yapiys.Internal.fireControllerReady = function(name,controller){
    if(Yapiys.UI.readyControllersHandlers.hasOwnProperty(name)){
        Yapiys.UI.readyControllersHandlers[name](controller);   
    }
};

Yapiys.Internal.nextClosureInformation = {};
Yapiys.Internal.setNextClosureInformation = function(module_name,model,action){
    Yapiys.Internal.nextClosureInformation = {module_name:module_name,model:model,action:action};
};

//Handlers para eventos da view
Yapiys.UI.onLoad = function(view_name,callback){
    
    var model_name=Yapiys.Internal.nextClosureInformation.model;
    var handlers_path = Yapiys.Internal.nextClosureInformation.module_name+'_'+model_name+'_'+view_name;
    
  
    if(Yapiys.UI.viewsEventsHandlers.hasOwnProperty(handlers_path)){
               
        var handlers = Yapiys.UI.viewsEventsHandlers[handlers_path];
        
        handlers.push(callback);
        
    }else{
        
        var handlers = new Array();
        
        handlers.push(callback);
        
        Yapiys.UI.viewsEventsHandlers[handlers_path] = handlers;        
        
    }
};

Yapiys.Internal.nextPartialModule = false;
Yapiys.Internal.nextPartialName = false;
Yapiys.Internal.setNextPartialInformation = function(module_name,name){
    Yapiys.Internal.nextPartialModule=module_name;
    Yapiys.Internal.nextPartialName = name;
};

//Quando o controlador de um elemento estiver pronto
Yapiys.UI.whenReady = function(name,controller){
    return Yapiys.Internal.fireControllerReady(name,controller);  
};


//Loader desabilitado por default
Yapiys.UI.disableLoader = false;

//Definicoees de loaders
Yapiys.UI.loaderSettings = {};


//Cria um closure javascript para uma determinada view
Yapiys.UI.js = function(closure){
    

//Obtem informacao sobre o closure que esta sendo registrado
var closure_information = Yapiys.Internal.nextClosureInformation;    

//Resolved route
var route = Yapiys.routes.resolve(closure_information);  

//Rota correspondente a esta view
closure.$route = route;
    
//Route path
var route_path = Yapiys.Internal.getURL(route);
    
//Definicoes de loaders encontradas
if(closure.hasOwnProperty('loader')){
    
    //Arquiva as definicoes de loaders
    Yapiys.UI.loaderSettings[route_path] = {};
        
    
}else{
    
    //Nao foram encontradas definicoes de loader
    //Permitido mostrar loader
    if(!Yapiys.UI.disableLoader){
        
        Yapiys.UI.loaderSettings[route_path] = {};
        
    }
    
    
}
    
//Regista o closure
Yapiys.Internal.pushViewClosure(closure_information.module_name,closure_information.model,closure_information.action,closure);

//Gera o path do closure    
var path = Yapiys.Internal.generateViewPath(closure_information.module_name,closure_information.model,closure_information.action);    

//Considera este closure declarado
Yapiys.UI.viewsClosuresDeclared.push(path);   
        

//Declara todos closures filhos deste
Yapiys.Internal.declareClosureChilds(closure_information);    

    
    
};


Yapiys.Internal.buildClosureChild = function(parent_closure,child){
    
    //Closure resultante;
    var resulting_closure = {};
    
      
    var closure_properties=parent_closure[0];
    
    
     //Copia as propriedades do closure pai
    for(var property_name in closure_properties){
        
        resulting_closure[property_name] = closure_properties[property_name];
        
    }
    
    //Para cada propriedade do closure filho
    for(var property in child){

        //Propriedades a serem adicionadas
        if(property.toString().startsWith('$')){
            
            var property_name_length = property.toString().length;
            var parent_property_name = property.toString().substr(1,property_name_length-1);
                  
                 
            var property_value = child[property];
        
                   
            //Existe a properiedade existe
            if(resulting_closure.hasOwnProperty(parent_property_name)){
                
                             
                var parent_property_value = resulting_closure[parent_property_name];
            
                
                //Copia todos os atributos
                for(var child_prop in property_value){
                                      
                       parent_property_value[child_prop] = property_value[child_prop];
                    
                }
                
                
                
            }
            
            
        }else{
            

   
            //Faz override da propriedade 
            resulting_closure[property] = child[property];
                        
            
        }
        
    }
    
    //Retorna o closure
    return resulting_closure;
    
};

//Obtem o HTML de um template
Yapiys.UI.getTemplateHTML = function(name){
    
    
};

//Obtem o HTML de uma view
Yapiys.UI.getViewHTML = function(module_name,model,controller){
    
};


//Bloqueio de interface grafica
Yapiys.UI.locker = {};

//Views bloqueadas sao nodos de bloqueioz
Yapiys.UI.locker.nodes = {};
Yapiys.UI.locker.waiters = {};

//Espera que uma view seja debloqueada 
Yapiys.UI.locker.waitFor = function(path,waiterID,unlockWaiter){
    
    if(Yapiys.UI.locker.isLocked(path)){
        
                
        //Ja existem waiters
        if(Yapiys.UI.locker.waiters.hasOwnProperty(path)){
           
            var waiters = Yapiys.UI.locker.waiters[path]; 
            
            //Waiter ainda nao tinha sido registado
            if(!waiters.hasOwnProperty(waiterID)){
                waiters[waiterID]=unlockWaiter;
            }
            
            
        }else{
            
            //Ainda nao existem waiters
            var waiters = {};
            waiters[waiterID]=unlockWaiter;
            Yapiys.UI.locker.waiters[path]=waiters;
            
        }
        
    }else{
        
        Yapiys.messages.error('Locker: '+path+" IS NOT LOCKED");
        
    }
    
};

//Bloqueia uma view
Yapiys.UI.locker.lock = function(path){
    
    //A view nao esta bloqueada
    if(!Yapiys.UI.locker.isLocked(path)){
    
        var lock_node = {};
        lock_node.path = path;
        Yapiys.UI.locker.nodes[path] = lock_node;
        
    }
    
};

//Verifica se uma view esta bloqueada
Yapiys.UI.locker.isLocked = function(path){
    
    return Yapiys.UI.locker.nodes.hasOwnProperty(path);
    
};


//Desbloqueia uma view
Yapiys.UI.locker.unlock = function(path){
    
    //Se a view estiver bloqueada
    if(Yapiys.UI.locker.isLocked(path)){
                
        //Carrega o no de bloqueio
        var lock_node = Yapiys.UI.locker.nodes[path];

        
        //Existe um array de waiters
        if(Yapiys.UI.locker.waiters.hasOwnProperty(path)){
            
            
            //Pega o array de waiters
            var waiters = Yapiys.UI.locker.waiters[path];
            
            //Itera sobre o array
            for(var waiter_index in waiters){                
                
                var waiter = waiters[waiter_index];                
                
                //Invoca o waiter
                waiter();                
                
            }
            
            //Remove todos os waiters
            delete Yapiys.UI.locker.waiters[path];
            
        }       
                
    }    
    
};

Yapiys.routes.toSlashes = function(route){   
    
    return Yapiys.Internal.getURL(route);
    
};

//Resolve uma rota
Yapiys.routes.resolve = function(param){
    
    
    if(typeof param==='string'){
        
        //Possui slashes
        if(param.indexOf('/')!==-1){
            
            var lastIndex = param.length-1;
            var previousIndex = -1;
            
            var parts = new Array();
            var params = new Array();
            var params_str = false;

            var pending_word = '';
            
            for(var charIndex in param){
                
                var char = param[charIndex];
                
                if(char!=='/'){
                    
                    pending_word = pending_word+char;
                    
                }else{


                    if(previousIndex!=-1){

                        if (param[previousIndex] == '/') {

                            params_str = param.substr(charIndex);
                            params = Yapiys.explode('/', params_str);
                            break;

                        }
                    }

                    parts.push(pending_word);
                    pending_word = '';
                    
                    
                }

                if(charIndex == lastIndex){
                    
                    parts.push(pending_word);
                    
                }


                previousIndex = charIndex;
                
            }
            
            if(parts.length>=2){
                
                var route = {};
                
                //MVC
                if(parts.length==2){                    
                    route.controller = parts[0];
                    route.action =  parts[1];
                    
                //HMVC    
                }else if(parts.length==3){
                        
                    route.module = parts[0];
                    route.controller = parts[1];
                    route.action =  parts[2];
                    
                }


                if(params.length>0){

                    route.params = params;

                }
                
                return route;
                
            }
            
            return false;
            
        }
        
        return Yapiys.routes.get(param);
        
    }else{
        
        if(param.hasOwnProperty('model')||param.hasOwnProperty('view')||param.hasOwnProperty('moule_name')){
            
            var resolved = JSON.parse(JSON.stringify(param)); 
            
            if(param.hasOwnProperty('model')){
                resolved['controller'] = param.model;
                delete resolved['model'];
            }
            
            if(param.hasOwnProperty('view')){
                resolved['action'] = param.view;
                delete resolved['view'];
            }
            
            if(param.hasOwnProperty('module_name')){
                resolved['module'] = param.module_name;
                delete resolved['module_name'];
            }
            
            return resolved;
        }
        
        return param;
        
    }
    
};

Yapiys.UI.dumpView = function(route,dumpCallback){
    
    var route_object = Yapiys.routes.resolve(route);
    
    if(route_object){
    
        var url = Yapiys.Internal.getURL(route_object,'@view_dump');    

        $.get(url,function(view_content){

            if(dumpCallback){

                dumpCallback(view_content);

            }      

        });
        
    }
    
    
};


Yapiys.Internal.compileExtendedView = function(parent_route,child_route,parent_view,child_view){

    
            var parent_path = Yapiys.Internal.getURL(parent_route);    
            var child_path = Yapiys.Internal.getURL(child_route);
            
 
            var child_div = $(child_view);            
            var result_div = $('<did>');
            $(result_div).html(parent_view);
            
            var prepend_content = $(child_div).find('prepend').html();            
            var append_content  = $(child_div).find('append').html();    
            var remove_content  = $(child_div).find('remove');
      
    
            //Encontra todos filhos da tag remove
            $(remove_content).find('*').each(function(index,element){
                
                //Pega a propriedade ID do elemento
                var elementID = $(element).attr('id');
                
                //Elemento possui ID
                if(elementID!==undefined){                    
                    //Remove o elemento
                    $(result_div).find("#"+elementID).remove();
                                        
                }
            
            });
    
    
    
            $(result_div).append(append_content);
            $(result_div).prepend(prepend_content);            
            var final_html = $(result_div).html();            
                    
            //Coloca a view resultante na cache
            Yapiys.UI.cache.storeView(child_path,final_html);     
    
     
    
            return final_html;
                   
};

Yapiys.UI.extend = function(parent_view_name,closure){

    var parent = Yapiys.Internal.nextClosureInformation;   
        
    var child = JSON.parse(JSON.stringify(parent));    
    child.action = parent_view_name;
    
    var child_route = Yapiys.routes.resolve(parent);        
    var parent_route = Yapiys.routes.resolve(child);
    
    var parent_path = Yapiys.Internal.getURL(parent_route);    
    var child_path = Yapiys.Internal.getURL(child_route);

    //Bloqueia as duas views
    Yapiys.UI.locker.lock(parent_path);    
    Yapiys.UI.locker.lock(child_path);
     
    //Faz dump da parent view
    Yapiys.UI.dumpView(child,function(parent_view){
       
	   
        //Coloca a parent view na cache
        Yapiys.UI.cache.storeView(parent_path,parent_view);
       
		
        //Desbloqueia a view
        Yapiys.UI.locker.unlock(parent_path);
        
        //Faz dump da child view
        Yapiys.UI.dumpView(parent,function(child_view){
            
              
            //Compila a view extendida
            Yapiys.Internal.compileExtendedView(parent_route,child_route,parent_view,child_view);
            
            //Desloqueia a view
            Yapiys.UI.locker.unlock(child_path);           
            
                                                
        });
                
        
    });
    
    /*
    if(model){
        
        child.model=model;
           
    }*/
    
    var parent_closure = false;
    
    var child_closure = false;
    
    var path = Yapiys.Internal.generateViewPath(child.module_name,child.model,child.action);
 
    
    
    //Ja foi declarado om closure pai deste closure
    if(Yapiys.UI.viewsClosuresDeclared.indexOf(path)!==-1){
            
            parent_closure = Yapiys.Internal.getClosures(child.module_name,child.model,child.action);
            Yapiys.Internal.nextClosureInformation = parent;
            child_closure = Yapiys.Internal.buildClosureChild(parent_closure,closure); 
            Yapiys.UI.js(child_closure);
        
            
    }else{
        

        //Closure ainda nao foi declarado. Fica esperando
        Yapiys.Internal.closureWaiting(child,function(){            
      
            Yapiys.Internal.nextClosureInformation=parent;
        
            Yapiys.UI.extend(parent_view_name,closure);             
        
        });
        
        
    }
    
    
    
};


Yapiys.UI.partial=function(options) {
    var name = Yapiys.Internal.nextPartialName;
    var module_name = Yapiys.Internal.nextPartialModule;

    var styles = {};
    if(options.$styles){

        styles = options.$styles;

    }

    delete options['$styles'];

    Yapiys.UI.cpstyles[name]=styles;

     var cpcontroller = function(el){

       //Constroi o partial
       this.$construct = function(params,scope){

            if(options.$constructor){

                options.$constructor.call(scope,params);

            }

       };

       this.$setup = function(element,attrs,scope){


               if (options.$setup) {


                   options.$setup.call(scope, element, attrs);


               }


       };

       this.$postLoad = function(element,attrs,scope){


             if (options.$postLoad) {


                 options.$postLoad.call(scope,element, attrs);


             }

             


         };


        this.$setup_all = function(){


             if (options.$setup_all) {

                 options.api = function(path){

                     return partialApi(module_name,name,path);

                 };

                 options.$setup_all();


             }


         };



         this.getLoaderTemplate  = function(){

            if(options.hasOwnProperty('$loader')){


                if(typeof options['$loader'] =='function' ){


                    return options['$loader']();

                }

                return options['$loader'];

            }


           if( typeof Yapiys.UI.partials_loader=='function'){


               return Yapiys.UI.partials_loader();

           }

           return Yapiys.UI.partials_loader;

       };

       this.element=el;
         
       this.setElement = function(el){
            this.element=el;          
       };
         
       this.getElement = function(){
            return this.element;  
       };


       this.$module = module_name;
       this.$partial = name;

        
       this.css = function(name,value) {
            $(this.element).css(name,value);
       };
         
       this.styles=styles;


       this.context_to_set = options;

       this.context={};  
    
       this.getContext = function(){
            return this.context;
       };
            
       this.setScope = function(scope){
           scope.element=this.element;
           if(this.hasOwnProperty("context_to_set")){
                             
                for(var context_variable in this.context_to_set){
                    var context_variable_value = this.context_to_set[context_variable];
                    scope[context_variable]=context_variable_value;
                }
               
                //Copia os estilos para o escopo
                scope.style=this.style;


           }else{

                  
           }

           //Function to build an API Url
           scope.api = function(url){

               return partialApi(module_name,name,url);


           };

           this.context=scope;
       };
       
        
       this.styleProperties = function(name) {
           
            if(this.styles.hasOwnProperty(name)){
                return this.styles[name];            
            }
           
           return false;
       }; 
         
    
       
         
         
       this.style = {}; 
         
       this.styleInterface = function(){
                
            for(var style_name in this.styles){ 
                var properties = this.styleProperties(style_name);
                new Yapiys.Internal.StylePropertiesSetter(this,style_name,properties,this.element);  

            }
          
           
       };
    
       
      this.bindStyleSetters = function() {
            for(var style_name in this.styles){           
                this.style[style_name].bind({properties:this}); 
            }
      };

      if(el){

          this.styleInterface();

      }

      
         
     };
         
   
    
     Yapiys.UI.controllers[name]=cpcontroller;
     Yapiys.Internal.createDirectives(module_name,name,styles,cpcontroller);

     
    
};


Yapiys.Internal.StylePropertiesSetter = function(controller,style_name,properties) {
        this.styleName=style_name;
        controller.style[this.styleName] = function() {
            for(var property_name in properties){
                var property_value = properties[property_name];
                
                //Adicionar uma classe
                if(property_name==='addClass'){
                    $(controller.element).addClass(property_value);
                //Remover uma classe
                }else if(property_name==='removeClass'){
                    $(controller.element).removeClass(property_value);

                //Realizar uma operacao    
                }else if(property_name==='do'){
                    property_value(controller.element);
                    
                //Definir uma propriedade CSS    
                }else{     
                    controller.css(property_name,property_value);
                }
            }       
            
            
        };
      
         
};

Yapiys.Internal.createDirectives = function(module_name,name,styles,cpcontroller){

    //Initialize all instances
    var temp = new cpcontroller(false);
    temp.$setup_all();

    //Cria a directiva principal
    var main_directive_name=name;
    var main_directiveCreator = new Yapiys.Internal.DirectiveCreator(module_name,name,main_directive_name,false,cpcontroller);
    main_directiveCreator.create();

    
    //Cria directivas dos estilos
    for(var style_name in styles){
            var style_uppercase= style_name.charAt(0).toUpperCase()+style_name.slice(1);
            var directive_name=name+style_uppercase;
            var directiveCreator = new Yapiys.Internal.DirectiveCreator(module_name,name,directive_name,style_name,cpcontroller);
            directiveCreator.create();
    }
 
};


Yapiys.UI.partialsInstances = {};

Yapiys.Internal.registerCpInstance = function(instance){
    var element = instance.getElement();
    var name = $(element).attr("name");
    
    if(name){

        Yapiys.UI.partialsInstances[name] = instance;
        Yapiys.Internal.fireControllerReady(name,instance);
        
    }
    
};

Yapiys.Internal.setPartialReady = function(instance,element){

    var name = $(element).attr("name");
    if(name){

        Yapiys.UI.partialsInstances[name] = instance;
        Yapiys.Internal.fireControllerReady(name,instance);

    }

};


Yapiys.Internal.postLoad = function(jsFiles,html,noMarkup){
   
    var js_elements = "";
    
    //Array de scripts
    if(Array.isArray(jsFiles)||typeof jsFiles ==='object'){
        
        for(var jsFile_index in jsFiles){
            
            var js_filename = jsFiles[jsFile_index];
            
            //Trata-se de uma funcao
            if(typeof js_filename ==='function'){
                      
                continue; 
             
                
                
            //Trata-se de um array    
            }else if(Array.isArray(js_filename)){             
             
                var only_scripts = Yapiys.Internal.postLoad(js_filename,html,true);    
                js_elements=js_elements+only_scripts;
                
                
            }else{
                
                //Trata-se de um ficheiro de script            
                var js_element = "<script src='"+js_filename+"'></script>";
                js_elements = js_elements+js_element;      
                
            }
            
        }
        
    }else{
        
        
        if(typeof jsFiles ==='function'){
           
                return html;         
        }
        
        //Um e unico script
        js_elements = "<script src='"+jsFiles+"'></script>";    
            
        
    }
    
    
    //Nao retornar HTML
    if(noMarkup){
        
       
        return js_elements;
        
    }
    
    var result = html+"\n"+js_elements;
  
    return result;
    
};



Yapiys.Internal.runPostLoad = function(jsFiles,context){
   
    var js_elements = "";
    
    //Array de scripts
    if(Array.isArray(jsFiles)||typeof jsFiles ==='object'){
        
        for(var jsFile_index in jsFiles){
            
            var js_filename = jsFiles[jsFile_index];
            
            if(typeof js_filename ==='function'){
                
                 //Executar funcao
                js_filename.apply(context);
                
               
         
                     
            }
        
            
        }
        
    }else{
        
        if(typeof jsFiles ==='function'){
              
             //Executar funcao
            jsFiles.apply(context);
            
        }            
        
    }

};

Yapiys.Internal.preLoad = function(jsFiles,html,context,noMarkup){



    var js_elements = "";
    
    //Array de scripts ou objecto
    if(Array.isArray(jsFiles)||typeof jsFiles ==='object'){
        
        //Para cada chave do array/objecto
        for(var jsFile_index in jsFiles){
            
            //Pega o valor associado a chave
            var js_filename = jsFiles[jsFile_index];
            
            //Se for uma funcao
            if(typeof js_filename ==='function'){


                //Executar funcao
                js_filename.apply(context,new Array(html));

                //Saltar para o proximo elemento.
                continue;
                
            }else if(Array.isArray(js_filename)){
                
                var only_scripts = Yapiys.Internal.preLoad(jsFiles,html,context,true);
                
                js_elements = js_elements + only_scripts;                
                
            }else{
                

                var js_element = "<script src='"+js_filename+"'></script>";          
                js_elements = js_elements+js_element;    
                
            }
            
               
            
        }
        
    }else{
        
        
        if(typeof jsFiles ==='function'){
            
                //Executar funcao
            var returned_html = jsFiles.apply(context,new Array(html));

            if(typeof returned_html==="string"){

                return returned_html;

            }


            return html;
                
        }
        
        //Um e unico script
        js_elements = "<script src='"+jsFiles+"'></script>";                
        
    }


    if(noMarkup){
        
        return js_elements;
        
    }
      
    return js_elements+"\n"+html;
    
};


Yapiys.Internal.DirectiveCreator= function(module_name,name,directiveName,style_name,cpcontroller){
    this.directiveName=directiveName;
    this.created = false;


    if(module_name){

        var partial_view_url="$partials/"+module_name+"/"+name;

    }else{

        var partial_view_url="$partials/"+name;

    }

    partial_view_url = App.base_url+partial_view_url;

    var tempController = new cpcontroller(false);
    var componentLoader = tempController.getLoaderTemplate();

    if(componentLoader){

        var loaderElem = $(componentLoader);
        $(loaderElem).attr('ng-hide','loaded');


        var include = $('<partial-load>');
        $(include).attr('src',""+partial_view_url+"");
        $(include).attr('onload','$doneLoading()');

        var newElem = $('<div>');
        $(newElem).append(loaderElem);
        $(newElem).append(include);


        componentLoader = $(newElem).html();


    }


     this.create = function(){

         if(this.created){

             return false;

         }

         this.created = true;


         Yapiys.UI.ngApp.directive(this.directiveName,function($compile){


                    var $directive = {};
                    $directive.cpcontroller = cpcontroller;




                    //User defined a loader
                    if(componentLoader){

                        $directive.template = componentLoader;

                    }else{

                        $directive.templateUrl = partial_view_url;

                    }


                    $directive.restrict = 'E',
                    $directive.scope = false;

                

                    $directive.compile = function(element,attrs){

                        return  {


                            pre : function($scope,element,attrs){


                                var tagName = $(element)[0].tagName.toLowerCase();
                                var partialName = Yapiys.explode('-',tagName)[0];
                                var cpcontroller = Yapiys.UI.controllers[partialName];


                                $scope.$doneLoading = function(){

                                    $scope.loaded=true;


                                };

                                var controller = new cpcontroller(element);


                                this.controller = controller;


                                //Put me in my parent context
                                if(attrs.name){

                                    var instanceName = attrs.name;


                                    if(Yapiys.$this){

                                        Yapiys.$this[instanceName] = $scope;

                                    }else{

                                        Yapiys.Internal.$thisInclude(instanceName,$scope);

                                    }



                                }else{

                                    console.error('Error setting <'+directiveName+'> partial instance! All partial instances should have a name attribute');
                                    return false;

                                }


                                    //Construir o partial
                                    if(attrs.construct){

                                        var $contructor_params = $scope.$eval(attrs.construct);
                                        controller.$construct($contructor_params,$scope);


                                    }




                                    if (style_name) {
                                        if (controller.style.hasOwnProperty(style_name)) {
                                            var style_properties = controller.styleProperties(style_name);
                                            controller.style[style_name]();
                                            controller.setScope($scope);
                                        }
                                    } else {

                                        controller.setScope($scope);

                                    }



                                this.controller = controller;

                                

                            },

                            post : function($scope,element,attrs){


                                this.controller.$setup(element, attrs, $scope);
                                Yapiys.Internal.setPartialReady($scope,element);


                            }


                        }


                    };



                    return $directive;

         }); 
     };
};



/*
Yapiys.setup = {};
Yapiys.setup.modules = [];
Yapiys.setup.run = function(){


};*/


var modulesInjected = [];
var appRun = false;


if(typeof Startup !=='undefined'){

if(Startup.setup){

    if(Startup.setup.modules){

        modulesInjected = Startup.setup.modules;

    }

    if(Startup.setup.run){

        appRun = Startup.setup.run;

    }

}

}



var angularApp = angular.module('yapiys', modulesInjected);


//Callback do application run
if(appRun){

    angularApp.run(appRun);

}

//angularApp.run(function(editableOptions) {
//    editableOptions.theme = 'bs3';
//});

Yapiys.UI.setApp(angularApp);


Yapiys.UI.ngApp.directive('scrollForever',function(){


    return  {

        restrict : 'EA',
        scope : true,
        link : function($scope,element,attrs){


            if(!attrs.name){

                console.error('Set the name attribute to use scroll-forever directive');
                return false;

            }



            $scope.distance = 0;
            $scope.fire_at_distance = 45;
            $scope.fire_at_top_distance = 0;
            $scope.last_distance = 0;

            $scope.both_directions = false;
            $scope.handler =  false;
            $scope.top_handler =  false;
            $scope.busy = false;

            $scope.height_read = false;
            $scope.height = false;


            var scrolling = { done: function(){

                $scope.busy = false;

            }};


            //Sets the scrolling object in the next view
            Yapiys.Internal.$thisInclude(attrs.name,{$scroll:scrolling});


            //Scroll up and down
            if(attrs.hasOwnProperty('scrollBoth')){

                $scope.both_directions = true;

            }


            if($scope.both_directions){


                if(!(attrs.scrollDownAction&&attrs.scrollTopAction)){

                    console.error('Set scroll-down-action scroll-top-action and attributes to use scroll-forever directive');
                    return false;

                }

                $scope.handler = attrs.scrollDownAction;
                $scope.top_handler = attrs.scrollTopAction;

                if(attrs.scrollDistance){

                    $scope.fire_at_distance = attrs.scrollDistance;

                }else{

                    $scope.fire_at_distance = 10;

                }

                $scope.fire_at_top_distance = 0;



            }else{


                if(!attrs.scrollAction){

                    console.error('Define scroll-action attribute to use scroll-forever directive');
                    return false;

                }else{


                    $scope.handler = attrs.scrollAction;

                }


                if(attrs.scrollDistance){

                    $scope.fire_at_distance = attrs.scrollDistance;

                }else{

                    $scope.fire_at_distance = 10;
                    //$scope.height_read = true;


                }




            }


            //When scrolling
            $(element).on('scroll',function(){


                if(!$scope.height_read){

                    $scope.fire_at_distance = $(element).height()-$scope.fire_at_distance;

                    $scope.height_read = true;

                }




                var distance = $(element).scrollTop();
                $scope.distance = distance;
                console.log(distance);
                console.log('offset : '+$(element)[0].offsetHeight);
                console.log('Scroll-height : '+$(element)[0].scrollHeight);
                $scope.updateDistance(distance);


            });


            $scope.updateDistance = function(value){


                if(typeof value=='number'){


                    //Scrolling down
                    if(value>$scope.last_distance){


                        //Fire bottom scroll event
                        if(value>=$scope.fire_at_distance){


                            if($scope.busy){


                                return;

                            }

                            $scope.busy = true;
                            var done = $scope.$eval($scope.handler);
                            if(done){

                                $scope.busy = false;

                            }




                        }


                    }else{

                        //Scrolling up

                        if($scope.both_directions){

                            //Fire top scroll event
                            if(value<=$scope.fire_at_top_distance){

                                if($scope.busy){

                                    return;

                                }


                                $scope.busy = true;
                                var done = $scope.$eval($scope.top_handler);


                                if(done){

                                    $scope.busy = false;

                                }

                            }


                        }


                    }

                    $scope.last_distance = value;


                }


            };


        }


    }


});

Yapiys.UI.partialViews = {};


Yapiys.UI.ngApp.directive('partialLoad',function($compile){


    return {

        restrict : "EA",

        scope : true,

        link : function($scope,element,attrs){

            if(attrs.src&&attrs.onload){


                var htmlCompiler = function(html){

                    var compiler = $compile(html);

                    var html_compiled = compiler($scope);

                    $(element).before(html_compiled);
                    $(element).remove();

                    $scope.$eval(attrs.onload);

                    $scope.$apply();

                };

                if(Yapiys.UI.partialViews.hasOwnProperty(attrs.src)){

                    htmlCompiler(Yapiys.UI.partialViews[attrs.src]);

                }

                $.get(attrs.src,function(html){

                    Yapiys.UI.partialViews[attrs.src] = html;
                    htmlCompiler(html);

                });

            }


        }

    };


});

Yapiys.UI.ngApp.directive('ajaxify',function(){
    return  {
        
        restrict : 'A',
        link : function($scope,element,attrs){
            
             $(element).removeAttr('ajaxify');

            
            if(attrs.href){
                
                var href = attrs.href;

                var event = 'click';
                
                $(element).attr('href',App.base_url+href);
                
                if(attrs.on){
                    
                    event = attrs.on; 
                    $(element).removeAttr('on');
                    
                }
                
                Yapiys.bind($(element),href,event);
                
            }
            
        }
        
    };
    
    
});


Yapiys.UI.ngApp.directive('aload',function(){

    return {

        restrict : 'A',

        link : function(scope,element,attrs){


            if(attrs.hasOwnProperty('asrc')){

                var img_source = attrs.asrc;
                var loader = attrs.aload;

                //Mostra o loader
                $(element).attr('src',loader);

                //Carrega a imagem de forma asincrona
                var i = new Image();

                i.onload = function () {

                    //console.log('loaded image '+img_source);
                    $(element).attr('src',img_source);

                };
                i.onerror = function(){

                    //console.log('error loading image : '+img_source);

                };

                i.src = img_source;

            }


        }


    };

});

Yapiys.UI.ngApp.directive('bgaload',function(){

    return {

        restrict : 'A',

        link : function(scope,element,attrs){


            if(attrs.hasOwnProperty('asrc')){


                $(element).css('background-size','auto');

                var img_source = attrs.bgaload;

                //Carrega a imagem de forma asincrona
                var i = new Image();

                i.onload = function () {



                    $(element).css({'background-image':'url('+img_source+')',
                        'background-size' : 'cover',
                        'background-position': 'center'
                    });

                };
                i.onerror = function(){

                    //console.log('error loading image : '+img_source);

                };

                i.src = img_source;

            }


        }


    };

});

/*
Yapiys.UI.ngApp.filter('date', function(){
    return function(input){
        if(input === null){ 
            return ""; 
        }
        var result = input.split("-");
        return  result[2]+"/"+result[1]+"/"+result[0];
    };
});
Yapiys.UI.ngApp.filter('datetime', function(){
    return function(input){
        if(input === null){ 
            return ""; 
        }
        var result = input.split("-");
        var res = result[2].split(" ");
        
        return res[0]+"/"+result[1]+"/"+result[0]+" "+res[1]+":"+result[3]+":"+result[0];
    };
});*/


Yapiys.UI.ngApp.directive('placeholderx',function($compile){
    
    return { 
        
     restrict: "E",
        
     link: function($scope, element, attributes) {
    
            $scope.self=$scope;  
         
            $scope.remake = function(){                
                var template_to_load = attributes.template;            
                var compile_function = $compile(template_to_load);         
                var compiled_template = compile_function($scope);            
                element.html(compiled_template);                
            };
            
            
         
            attributes.$observe('template',function(value){
                  $scope.template=value;
                  $scope.remake();
            });
         
      
         Yapiys.UI.placeholderx=$scope;
            
         },
         
        replace: true
             
      };
 
      
        
    });


//Cria a directiva de extend
Yapiys.UI.ngApp.directive('extends',function($compile){
    
    return { 
        
     restrict: "A",
        
     link: function($scope, element, attributes) {
            
            var parentView = false;
            
            if(attributes.hasOwnProperty('extends')){
                
                parentView=attributes['extends'];
                
            }
            
         
            if(parentView){
                                                
                //alert('heloo');
                
            }
            
           
                                
      }
             
      }; 
      
        
    });


    
    String.prototype.startsWith = function(str){    
        return this.indexOf(str)===0;    
    };


    
   Array.prototype.removeVal = function(el){
    var array = this;
    var index = array.indexOf(el);
      if (index > -1) {
    array.splice(index, 1);
       }
   };



    window.onhashchange = function(){

            //console.log(window.location.hash);


    };


    window.onpopstate = function(param){



        var the_base_url = App.base_url;//The base URL

        var the_requested_url = window.location.toString(); //The requested URL



        //The base URL is the same
        if(the_requested_url.startsWith(the_base_url)){


            if(param.state){


                if(param.state.action){

                    Yapiys.route(param.state);

                }


            }else{



                if(the_requested_url[the_requested_url.length-1]==='#'){

                    the_requested_url = the_requested_url.substr(0,the_requested_url.length-1);

                    var route = Yapiys.getLog(the_requested_url);

                    if(route){


                        //alert(JSON.stringify(route));
                        //Yapiys.redirect(route);

                    }else{

                       // alert('not found : '+the_requested_url);

                    }

                }



            }


        }

    };


Yapiys.UI.ajaxLoader = {};

//Loader actual
Yapiys.UI.ajaxLoader.current = false;

//API para criar um loader
Yapiys.UI.ajaxLoader.set = function(loader_closure) {
    
    var new_loader = {};
    
    var loader = {};
    
    if(loader_closure){
        
        loader = loader_closure;
        
    }
    
    new_loader.setElement = function(el){          
        this.element = el;
    };
    
    new_loader.init = function(){
      
        Yapiys.messages.log('initilializing loader');
        
    };
    
    //Configuracoes pre definidas
    new_loader.defaults = {};
    
    //Quando o processamento terminar
    new_loader.onComplete = function(){
    
        $(this.element).hide();
    
    };
    
    
    new_loader.completed = function(){
        
        this.onComplete();
        
    };
    
    new_loader.updateState = function(state){
        
        this.onStateUpdate(state);
          
    };
    
    new_loader.loading = function(config){
        
        this.onLoad(config);
        
    };
    
    //Quando um erro acontecer
    new_loader.onError = function(err){
        
        Yapiys.messages.error('loder received an error notification');
    
        
    };
    
    //Quando for para mostrar o loader
    new_loader.onLoad = function(config){
        
        Yapiys.messages.log('showing loader');
                        
    };
    
    
    new_loader.getElement = function(){
        
        return this.element;
        
    };
    
    
    new_loader.onStateUpdate = function(state){
        
            
    };
    
    
    

    
    //Define o loader actual
    Yapiys.UI.ajaxLoader.current=jQuery.extend(new_loader,loader);
    
        
    //Inicializa o loader
    new_loader.init();
    
    
};	


Yapiys.serverGroups = {};

Yapiys.ServerGroup = function(route,events){
    
    this.events = events;
    
    this.route = route;
    
    //Path da view
    this.path = Yapiys.Internal.generateViewPath(route.module,route.model,route.action); 
    
    //Invoca o evento no servidor
    this.invokeEvent = function(name,callback,params,pre,post,fail){
      
        //Evento existe
        if(this.events.hasOwnProperty(name)){
            
            //alert('invoking event '+name);
            
            var event = this.events[name];
            var http_req_url = Yapiys.Internal.eventURL(this.route,name);
     
            if(!params){
                
                params =  {};
                
            }
            
            //Pega a ID da instancia da aplicacao
            params.$iid = App.instanceID;
            
            
            if(!callback){
                
                callback = function(data){
                    
                    Yapiys.messages.log("server_response : "+data);
                      
                };
                
            }
            
            
            //Efectuar requisicao get
            if(event.get){
                
                Yapiys.messages.log('get request');
                
                
                 var req = $.ajax({
                    
                   url:http_req_url,
                   data: params,
                   type:'GET',
                   success:callback,
                   beforeSend:pre,
                   complete:post,
                   error:fail                   
                    
                    
                });
                
                return req;
                
            }else{
             
                Yapiys.messages.log('post request');
                //Efectuar requisicao post
                //$.post(http_req_url,params,callback);
                
               var req = $.ajax({
                    
                   url:http_req_url,
                   data: params,
                   type:'POST',
                   success:callback,
                   beforeSend:pre,
                   complete:post,
                   error:fail                   
                    
                    
                });
                
                return req;
                
            }           
            
            
        }
 
        
        
    };
    
    
    
    this.getStub = function(){
        
        var stub = {};
        
        var me = this;
        
        var invokers = {};
        
        for(var eventName in this.events){
            
            
            
            /*
            stub[eventName] = function(params,callback){
                
                
                //Invoca o evento exacto
                me.invokeEvent.apply(me,[this.eventName,callback,params]);
                
            };
            */
    
         
            
            
            var default_invoker = new Yapiys.EventInvoker(eventName,me,false,false);
            var pre_and_post_invoker = new Yapiys.EventInvoker(eventName,me,false,true);
            var noparams_invoker = new Yapiys.EventInvoker(eventName,me,false,false,true);
            
            
            //Evento com callback apenas
            stub[eventName] = default_invoker.execute;   
            stub['checkIf'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = default_invoker.execute;  
            stub['do'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = default_invoker.execute;
            stub['check'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = default_invoker.execute;
            stub['execute'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = default_invoker.execute;
            
            //Evento com pre e post
            stub[eventName.toString().toUpperCase()] = pre_and_post_invoker.execute;
           
            //Evento sem parametros
            stub['fetch'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = noparams_invoker.execute;
                        
            stub['get'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = noparams_invoker.execute;
            
            stub['read'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = noparams_invoker.execute;
            
            stub['pick'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = noparams_invoker.execute;
            
            stub['retrieve'+eventName.charAt(0).toUpperCase()+eventName.substr(1)] = noparams_invoker.execute;
        
            
            
            
        }
        
        
        return stub;
        
    };
    
};


//Grupo de eventos
Yapiys.setServerGroup = function(route,events){
    
    var serverGroup = new Yapiys.ServerGroup(route,events);
    var path = Yapiys.Internal.generateViewPath(route.module,route.model,route.action);
    Yapiys.serverGroups[path] = serverGroup;
    
       
    
};


Yapiys.EventInvoker = function(name,group,nocallback,pre_and_post,noparams){
    
    
    this.eventName = name;
    this.group = group;
    
    this.setup = function(name,group){
        this.eventName = name;
        this.group = group;
    };
    
   
    
    //Sem parametros
    if(noparams){
        
        this.execute = function(callback){
                        
          //Invoca o evento exacto
          return group.invokeEvent.apply(group,[name,callback,false,false,false]);       
                
        };
        
     }
    
    
    //preExecute e postExecute disponiveis
    if(pre_and_post&&!nocallback&&!noparams){
        
         this.execute = function(params,pre,callback,post,fail){
                        
          //Invoca o evento exacto
          return group.invokeEvent.apply(group,[name,callback,params,pre,post,fail]);    
          
                
        };
       
    }
    
    
   //Apenas callback disponivel
    if(!nocallback&&!pre_and_post&&!noparams){
        
         this.execute = function(params,callback){
                        
          //Invoca o evento exacto
          return group.invokeEvent.apply(group,[name,callback,params]);    
          
                
        };
       
    }
    
   
    
};



Yapiys.UI.lang = {};
Yapiys.UI.lang.dict = {};
Yapiys.UI.lang.getItem = function(key,default_value){

    if((typeof Yapiys.UI.lang.dict[key] !=undefined) && (Yapiys.UI.lang.dict[key] != false) && Yapiys.UI.lang.dict[key]!=undefined  ){

        return Yapiys.UI.lang.dict[key];

    }

    if(default_value){

        return default_value;

    }else{

        return key;

    }

};

function __t(string,key){

    if(key){

        return Yapiys.UI.lang.getItem(key,string);

    }else{

        return Yapiys.UI.lang.getItem(string);

    }

}

Yapiys.setLanguage = function(dictionary){

    Yapiys.UI.lang.dict = dictionary;

};


function getKidProperties(name,attrs){

    var kids = [];


    for(var propertyIndex in attrs){

        if(propertyIndex.indexOf(name)>-1){

            var property = propertyIndex.replace(name,'').trim();

            if(property!=''){

                kids.push(property.toLowerCase());

            }

        }

    }


    return kids;



}

function camelCase(parts){

    var camelCased = '';

    parts.forEach(function(item,index){

        if(index==0){

            camelCased = item;

        }else{

            var firstCapital = item[0].toUpperCase();
            var restLowercase = item.substr(1,item.length-1).toLowerCase();

            var unitedCamel = firstCapital+restLowercase;

            camelCased = camelCased+unitedCamel;

        }

    });

    return camelCased;

}

//Directiva para traduzir conteudo
Yapiys.UI.ngApp.directive('translate',function(){
    return  {

        restrict : 'A',
        link : function($scope,element,attrs){


            var props = getKidProperties('translate',attrs['$attr']);


            props.forEach(function(name,index){


                var propName = camelCase(['translate',name]);
                var propKey = attrs[propName];

                //A key was defined for this property
                if(propKey!=''){

                    var propValue = $(element).attr(name);

                    if(typeof propValue == 'undefined' || typeof propValue != 'string'){

                        return;

                    }

                    var translatedKey = Yapiys.UI.lang.getItem(propKey.trim(),propValue.trim());
                    $(element).attr(name,translatedKey);


                }else{
                    //There is no key. Translate the property value


                    var propValue = $(element).attr(name);


                    if(typeof propValue == 'undefined' || typeof propValue != 'string'){

                        return;

                    }


                    var translatedValue = Yapiys.UI.lang.getItem(propValue.trim());
                    $(element).attr(name,translatedValue);


                }

                $(element).removeAttr('translate-'+name);


            });

            var translateKey = attrs.translate;

            //Translate inner HTML by Key
            if(translateKey!=''){

                var innerHTML = $(element).html();
                var translatedKey = Yapiys.UI.lang.getItem(translateKey.trim(),innerHTML.trim());
                $(element).html(translatedKey);


            }else{

                //Translate inner HTML content

                var innerHTML = $(element).html();
                var translatedHtml = Yapiys.UI.lang.getItem(innerHTML.trim());
                $(element).html(translatedHtml);


            }

            $(element).removeAttr('translate');

        }

    };


});

Yapiys.app = {};
Yapiys.module = {};
Yapiys.module.name = false;

Yapiys.module.setNextBorderName = function(name){

    Yapiys.module.name = name;


};


/**
 * Sets up the module border
 * @param closure
 */
Yapiys.module.border = function(closure){

    var border_closure = closure;

    if(Yapiys.module.name){

        Yapiys.app[Yapiys.module.name] = border_closure;

    }

};


Yapiys.getServerGroup = function(path_object_){
    
    var path_object = Yapiys.routes.resolve(path_object_);
    var path = Yapiys.Internal.generateViewPath(path_object.module,path_object.controller,path_object.action);    
    
    
    //Existe um server group para este path
    if(Yapiys.serverGroups.hasOwnProperty(path)){
                
        //Obtem o server group
        var serverGroup = Yapiys.serverGroups[path];
        
        //Gera o stub do server group
        var stub = serverGroup.getStub();        
        
        return stub;
        
    }
    
    
    return false;
    
};

Yapiys.Stream = {};
Yapiys.Stream.host="localhost";
Yapiys.Stream.port=1995;





/*

    MODULO DE APLICACOES EM TEMPO REAL
*/

Yapiys.ServerStream = function(name){
   
this.host = Yapiys.Stream.host; //Host do stream
this.port = Yapiys.Stream.port; //Porta do stream
this.name = name; //Nome do stream
    
    
this.setPort = function(p){
    this.port =p;    
};
    
this.setHost = function(h){
    this.host=h;    
};
    
this.changeName = function(n){
    
    this.name = n;
    
};
    
this.close = function(){
    
    this.socket.disconnect();  
    
};
    
var parent = this;
this.connect = function(){
    
     //Cria o socket
     parent.socket = io(this.getURL());

     //Configura o socket
     parent.setupSocket(parent.socket);    
    
};

this.start = function(callback){
    
        //Yapiys.messages.log("loading javascript");
        parent.loadJS(function(){
        
           //Yapiys.messages.log("javascript loaded");
            parent.connect();
            
            if(callback){
                
                callback();
                
            }
            
        });
           
};

this.broadcast = function(event,data){
    
   parent.socket.emit(event,data);
    
};

this.getURL = function(){
    
    return 'http://'+this.host+":"+this.port+"/";

};
    
this.onStatusUpdate = false;
this.OnDisconnect = false;
this.onReconnect = false;
this.onReconnecting = false;
this.onStatus = false;    
    
this.setupSocket = function(socket){
  
    var stream = this;
    
    //Quando o socket se desconectar
    socket.on("disconnect",function(){
        
        Yapiys.messages.log("connection lost");        
        //Perdeu conexao do servidor
        if(stream.onDisconnect){
                        
            stream.onDisconnect();
            
            
        }
        
        
    });
    
    //Quando receber o estado do servidor
    socket.on("server-stream-status",function(data){
        
        Yapiys.messages.log('data: ['+JSON.stringify(data)+"]");
        
        
        //Servidor informou seu estado actual
        if(stream.onStatus){
            
            stream.onStatus(data);
               
        }
        
    
    });
    
    
    socket.on("connect",function(data){
    
        //Yapiys.messages.log("conectou-se ao servidor");
        stream.sendIdentification();
        //Envia pedido de status ao servidor
        
    
    });
    
    //Servidor informa o cliente sobre actualizacao de status 
    socket.on("server-stream-status-update",function(data){
    
        Yapiys.messages.log('data: ['+JSON.stringify(data)+"]");
        
    
        if(stream.onStatusUpdate){
            
            stream.onStatusUpdate(data);
            
        }
        
    
    });
    
    
    //Quando o socket reconectar-se
    socket.on("reconnect",function(data){
    
        //Yapiys.messages.log("reconectado...");        
        //Envia pedido de status ao servidor
        
        if(stream.onReconnect){
            
            
            stream.onReconnect();
            
        }
        
    
    });
    
    
    
    //Quando o socket estiver reconectando-se
    socket.on("reconnecting",function(data){
    
        //Yapiys.messages.log("reconectando...");   
        
        if(stream.onReconnecting){
            
            
            stream.onReconnecting();
            
        }
        
    
    });
    
};
    
//Envia identificacao do cliente que conectou-se ao stream    
this.sendIdentification = function(){
    
    var identification = {};
    identification.name = this.name; //Nome do 
    identification.context=App.context;
    this.socket.emit("server_stream_consumer_id",identification);
    //Yapiys.messages.log(" + identificacao enviada");
    
};

this.loadJS = function(callback){
    
    var jsfile = this.getURL()+'socket.io/socket.io.js';
    //Yapiys.messages.log("Loading script: "+jsfile);
    
    $.getScript(jsfile,function(){
    
        if(callback){
            
            callback();
            
        }
        
    });
    
};

};



function global(name,value){
   
   if(typeof value != "undefined"){
        
         Yapiys.globals[name] = value;
       
   }else{
       
       
       if(Yapiys.globals.hasOwnProperty(name)){

           return Yapiys.globals[name];
           
       }
       
       return false;
       
   }    
   
    
};


//Adiciona o token de proteccao csrf
function secureForm(params){

    if(typeof params =='object'){

        params[App.csrf_param_name] = App.csrf_token;

    }else if(typeof params =='string'){

        params=params+'&'+App.csrf_param_name+'='+App.csrf_token;

    }

    return params;

};


Yapiys.messages = {};
Yapiys.messages.outputErrors = false;
Yapiys.messages.outputWarnings = false;
Yapiys.messages.outputLogs = false;
Yapiys.messages.outputInfo = false;

Yapiys.messages.log = function(msg){

    if(Yapiys.messages.outputLogs){

        console.log(msg);

    }


};

Yapiys.messages.warn = function(msg){

    if(Yapiys.messages.outputWarnings){

        console.warn(msg);

    }


};


Yapiys.messages.info = function(msg){

    if(Yapiys.messages.outputInfo){

        console.info(msg);

    }


};

Yapiys.messages.error = function(msg){

    if(Yapiys.messages.outputErrors){

        console.error(msg);

    }


};

Yapiys.explode = function(glueChar,string){

    var array = new Array();
    var pendingString ="";

    if(string.trim()==""){

        return [];

    }

    for(var charIndex in string){
        var char = string[charIndex];

        if(typeof char !='string'){

            continue;

        }

        if(char==glueChar){

            if(pendingString!=''){

                array.push(pendingString);
                pendingString="";

            }

        }else{

            pendingString = pendingString+char;

        }

    }

    if(pendingString!=''&&array.length==0){

        array.push(pendingString);

    }else if(array.length>0){

        if(array[array.length-1].valueOf()!=pendingString){

            array.push(pendingString);

        }


    }

    return array;

};


Yapiys.run = function(){
    
    //Desliga a cache em modo de desenvolvimento
    Yapiys.UI.cache.on = true;
    
    
    //Aplicacao em desenvolvimento
    if(App.under_development){
        
        //Limpa a cache
        Yapiys.UI.cache.cleanup();
        
    }


    Yapiys.UI.fireMasterLoadEvent();
    Yapiys.UI.cache.initialize();

    
};


Yapiys.UI.ngApp.directive('aload',function(){

    return {

        restrict : 'A',

        link : function(scope,element,attrs){

            var i = new Image();

            var img_source = false;
            var loader = false;
            var loadError = false;




            if(attrs.hasOwnProperty('asrc')){

                img_source = attrs.asrc;

            }else{

                return;

            }


            var doMagic = function(){

                loader = attrs.aload;
                loadError = loader;

                //Error image
                if(attrs.hasOwnProperty("err")){

                    loadError = attrs.err;

                }


                //Mostra o loader
                $(element).attr('src',loader);

                //Carrega a imagem de forma asincrona
                var i = new Image();

                i.onload = function () {

                    //console.log('loaded image '+img_source);
                    $(element).attr('src',img_source);

                };
                i.onerror = function(){

                    $(element).attr('src',loadError);
                    //console.error('error loading image : '+img_source);

                };

                i.src = img_source;


            };

            doMagic();

            attrs.$observe("asrc",function(new_value){


                if(typeof new_value!="undefined"){


                    img_source = attrs.asrc;
                    doMagic();

                }

            });





        }


    };

});

//Ignition function found
if(typeof $ignition=='function'){

    $ignition();


}else{

    Yapiys.messages.error('Yapiys $ignition function not defined.');

}
