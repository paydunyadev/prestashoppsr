<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="https://paydunya.com/assets/psr/css/psr.paydunya.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>

        .btn_paydunya {
            transition-duration: 0.4s;
        }

        .btn_paydunya:hover {
            background-color: white; /* Green */
            color: #0070B2;
            border: #0070B2 solid 1px;
        }
        body{
            padding-top: 100px;
        }
        .btn_paydunya{
            background-color:#0070B2;
            color: white;
        }
    </style>
</head>
<body class="row justify-content-center p-auto">
    <button class="btn btn_paydunya" onclick="payWithPaydunya(this)">Payer avec PayDunya</button>

<script src="https://code.jquery.com/jquery.min.js"></script>
<script src="https://paydunya.com/assets/psr/js/psr.paydunya.min.js">

</script>
<script>
    function payWithPaydunya(btn) {
        PayDunya.setup({
            selector: $(btn),
            url: "http://"+"{$smarty.server.HTTP_HOST}"+"/prestashop/module/paydunya/api",
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

</script>

</body>
</html>