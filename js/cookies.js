var allow_cookies = cookie_value("allow_cookies",'');

function cookie_value(n, v = false){
    var a = document.cookie.split(";");
    for(i=0;i<a.length;i++){
        var b = a[i].split("=",2);
        if(b[0].trim()===n) return b[1];
    }
    return v;
}

function cookie_set(n, v, perm = true){//alert(allow_cookies);
    if(allow_cookies==='no') return;
    if(allow_cookies!=='yes'){
        if(confirm(cookie_message)){
            allow_cookies = 'yes';
            cookie_set("allow_cookies",allow_cookies);
        }
        else{
            allow_cookies = 'no';
            cookie_set("allow_cookies",allow_cookies,false);
        }
    }
    var ex = "";
    if(perm){
        var d = new Date();
        d.setTime(d.getTime() + (30*24*60*60*1000));
        ex = "expires=" + d.toUTCString() + ";";
    }
    document.cookie = n + "=" + v + ";" + ex + "path=/;SameSite=Lax";
}
