
window.addEventListener("load", function(event) {
    var form = document.getElementsByClassName("checkout");
    var url = window.location.href
    if(url.indexOf('order_id')>=0){
        payWithPaydunya();
    }


});

function payWithPaydunya(btn) {
    PayDunya.setup({
        selector: $(btn),
        url: window.location.host+"/module/paydunya/api",
        method: "GET",
        displayMode: PayDunya.DISPLAY_IN_POPUP,
        beforeRequest: function() {
            console.log("About to get a token and the url");
        },
        onSuccess: function(token) {
            console.log("Token: " +  token);
        },
        onTerminate: function(ref, token, status) {
            console.log(ref);
            console.log(token);
            console.log(status);
        },
        onError: function (error) {
            alert("Unknown Error ==> ", error.toString());
        },
        onUnsuccessfulResponse: function (jsonResponse) {
            console.log("Unsuccessful response ==> " + jsonResponse);
        },
        onClose: function() {
            console.log("Close");
        }
    }).requestToken();
}