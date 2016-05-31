

/**
 * Load scripts and then load the Yapiys script
 */
function prepareYapiys(onstart,syncscripts,scripts,plugins,beforeIgnition,templateImages,pushUpdates){

    //Load started
    onstart();

    if(!Array.isArray(syncscripts)){

        syncscripts = [];

    }

    if(!Array.isArray(plugins)){

        plugins = [];

    }

    if(!Array.isArray(templateImages)){

        templateImages = [];

    }

    if(!Array.isArray(scripts)){

        scripts = [];

    }


    var updateStatus = function(){

        var total = syncscripts.length+scripts.length+templateImages.length+plugins.length+1;
        var done = sync_scripts_done_loading.length+scripts_done_loading.length+images_loaded.length+plugins_done_loading.length;

        if(total>0&&done>0){

            var percent = (done/total)*100;

            if(typeof pushUpdates=='function'){

                pushUpdates.call({},percent);

            }

        }


    };



    var waiting_for_images = false;

    //Load template Images
    var images_loaded = [];


    var async_images_load = function(){


        for(var imageIndex in templateImages){

            var imageSrc = templateImages[imageIndex];


            var element = document.createElement("img");
            element.src = imageSrc;
            element.style="display:none";


            var finished = function(){

                images_loaded.push(this.src);
                updateStatus();

                if(images_loaded.length==templateImages.length){


                    if(waiting_for_images){

                        all_done();

                    }


                }

            };

            //When finish loading script
            element.onload = finished;
            element.onerror = finished;


            //document.body.appendChild(element);


        }


    };


    //Load scripts syncronously

    var sync_scripts_done_loading = [];


    //Load next sync script
    var sync_scripts_next = function(){


        if(sync_scripts_done_loading.length<syncscripts.length){


            var sync_script_path = syncscripts[sync_scripts_done_loading.length];

            var element = document.createElement("script");
            element.src = sync_script_path;


            var finished = function(){

                sync_scripts_done_loading.push(this.src);
                updateStatus();

                //Load the next sync script
                sync_scripts_next();


            };

            //When finish loading sync script
            element.onload = finished ;
            element.onerror = finished;

            document.body.appendChild(element);


        }else{

            //Finished loading the sync scripts
            //Load the sync scripts
            //if(scripts.length){
                load_async_scripts();
            //}

        }


    };



    var scripts_done_loading = [];

    //Trigger before ingition callback and then ignite yapiys
    var all_done = function(){


        if(typeof  beforeIgnition=='function'){


            beforeIgnition();


        }


        //Boot UP the Yapiys Application

        angular.element(document).ready(function() {

            angular.bootstrap(document, ["yapiys"]);




        });

        $ignition();


    };



    var loadYapiys_script = function(){


        var element = document.createElement("script");
        element.src = 'yp/yapiys.js';


        //When finish loading yapiys script
        element.onload = function(){


            //Load the yapiys plugin
            load_yapiys_plugins();


        };

        document.body.appendChild(element);




    };


    var plugins_done_loading = [];


    //Finished loading all other scripts, load yapiys script and then load plugins
    var load_yapiys_plugins = function(){


        if(!Array.isArray(plugins)){

            plugins = [];

        }


        if(Array.isArray(plugins)){

            //There are no plugins
            if(plugins.length==0){

                //Everthing is done
                if(images_loaded.length==templateImages.length){

                    all_done();

                }else{

                    waiting_for_images = true;

                }

                return;

            }


            for(var pluginIndex in plugins){

                var pluginPath = plugins[pluginIndex];

                if(typeof pluginPath =='string'){


                    var element = document.createElement("script");
                    element.src = pluginPath;

                    var finished= function(){


                        plugins_done_loading.push(true);
                        updateStatus();

                        //Done loading plugins
                        if(plugins_done_loading.length==plugins.length){

                            //Everything is done
                            if(images_loaded.length==templateImages.length){

                                all_done();

                            }else{

                                waiting_for_images = true;

                            }


                        }

                    };

                    //When finish loading script
                    element.onload = finished;
                    element.onerror=finished;

                    document.body.appendChild(element);


                }

            }

        }

    };



    var load_async_scripts = function(){

        if(scripts.length==0){

            //Load the yapiys javascript
            loadYapiys_script();

            return;

        }

        //Load each script
        for(scriptIndex in scripts){

            var scriptPath = scripts[scriptIndex];

            var element = document.createElement("script");
            element.src = scriptPath;

            var finished = function(){

                scripts_done_loading.push(this.src);
                updateStatus();

                if(scripts_done_loading.length==scripts.length){

                    //Load the yapiys javascript
                    loadYapiys_script();

                }

            };

            //When finish loading script
            element.onload = finished;
            element.onerror = finished;

            document.body.appendChild(element);

        }


    };


    //Loads all the sync scripts
    sync_scripts_next();

    //Load  template
    async_images_load();



}


