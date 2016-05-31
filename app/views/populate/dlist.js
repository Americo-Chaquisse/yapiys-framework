Yapiys.UI.js({
    preLoad:function () {

        DList.setDataSource('contacts',{url:api('util/dlist_data')},function(request){

        });

    }
});