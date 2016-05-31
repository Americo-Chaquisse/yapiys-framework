/**
 * Created by Americo Chaquisse on 1/13/2016.
 */
Yapiys.UI.js({

    time:null,
    preLoad: function(){
        //Write here what you want to do before load the view

        this.time = this.getTimeFormat();

    },
    postLoad:function(){
        //Write here what you want to do after load the view

        setInterval(function(){
            Yapiys.$this.time = Yapiys.$this.getTimeFormat();
            Yapiys.flush();
        },1000);

    },
    //Write your own functions
    getTimeFormat: function(){
        var time = new Date();
        return ("0" + time.getHours()).slice(-2)   + ":" +
            ("0" + time.getMinutes()).slice(-2) + ":" +
            ("0" + time.getSeconds()).slice(-2)
    }
});