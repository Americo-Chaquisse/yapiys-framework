
Yapiys.angular.module.directive('aload',function(){


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
              i.src = img_source;
              i.onload = function () {

                  $(element).attr('src',img_source);

              };



          }


      }


    };

});
