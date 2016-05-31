

/**
    Extensao do DTable para fazer checks em uma DTable
**/



var checker = {

    html : '<label class="i-checks m-b-none"><input ng-checked="@name.checker.isChecked(@rowName)" ng-click="@name.checker.click(@rowName)" type="checkbox"><i></i></label>',
    head : '<label class="i-checks m-b-none"><input ng-disabled="@name.data.length==0" ng-model="@name.checker.all" ng-checked="@name.checker.areAllChecked&&@name.data.length>0" ng-click="@name.allCheck()" type="checkbox"><i></i></label>',

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


            scope[name].checker = {

                areAllChecked : false,

                checked:[],


                click : function(item){


                    if(typeof item !='undefined'){

                        var id = this.idRow(item);

                        if(!id){

                            return false;

                        }

                        if(this.isChecked(item)){

                            this.checked.splice(this.checked.indexOf(id),1);

                        }else{

                            this.checked.push(id);

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
                this.html=this.html.replace(new RegExp('@rowName','g'),rowName);


         

            }

            //Substitui o nome da dtable
            this.html=this.html.replace(/@name/g,name);

            if(this.head){

                this.head=this.head.replace(/@name/g,name);

            }


            //Foi passado o HTML do DTable
            if(writable.html){

                //Modifica o Html
                this.setHtml(writable);

            }


        },

        data : function(readable,writable,scope){

            //Chegada de dados do servidor

        }


    }


};


//Regista a extensao do DTable
DTable.Extensions.register(checker);
