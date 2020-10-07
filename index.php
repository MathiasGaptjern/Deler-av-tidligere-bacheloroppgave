<!DOCTYPE html>
<?php
session_start();
?>
<html>
    <head>
        <title>Innlogging</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <meta name="google-signin-client_id" content="394952128275-ric3p6qtai6jkav5adpc4449scev1726.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <style>
            .g-signin2 {
                margin-left: 50%;
                margin-top: 25%;
            }
            .g-signin {
                display: none;
                margin-left: 50%;
                margin-top: 25%;
                background-color: #4646a5;
                color: white;
                border-radius: 3px;
                height: 40px;
                border: solid 1px black;
                width: 130px;
                cursor: pointer;
            }
            
            .data {
                display: none;
            }
        </style>
    </head>
    <body>
        <!-- Google kaller funkjonen onSignIn når google innloggingen er fullført --> 
        <div name="loginKnapp" class="g-signin2" data-onsuccess="onSignIn"></div>
        <form name="form" action="login.php" method="post">
                <div class="data">
                    <input id="pic" name="bilde"></input>
                    <input id="gID" name="idok"></input>
                    <input id="name" name="navn"></input>
                    <button name="knapp" onclick="signOut()" class="btn btn-danger" id="start">videre</button>
                </div>
        </form>
        <form name="form" action="" method="post">
            <button name="loginKnapp" class="g-signin" data-onsuccess="onSignIn">Logg på igjen</button>
        </form>
        <script>
            function onSignIn(googleUser){
                //Henter brukernavn, id og bilde fra google konto
                var profile=googleUser.getBasicProfile();
                $(".g-signin2").css("display", "none");
                $(".data").css("display", "block");
                document.getElementById("pic").value = profile.getImageUrl();
                document.getElementById("gID").value = profile.getId() ;
                document.getElementById("name").value = profile.getName() ;


                <?php
                //Hvis brukeren er logget ut blir session destruert og en ny session startet, og funksjonen signOut blir kalt for å logge ut av google
                if(isset($_POST["logutknapp"])){
                    session_destroy();
                    session_start();
                    unset($_POST['logutknapp']);?>
                    signOut();
                <?php
                }else{
                    ?>
                    $(document).ready(function() {
                        $("#start").click();
                    });
                    <?php
                }
                ?>
            }
            //Logger ut brukeren fra google på siden, men er ikke en fullverdig utlogging før brukeren logger ut av google kontoen på browseren på google.com
            function signOut(){
                var auth2 = gapi.auth2.getAuthInstance();
                auth2.signOut().then(function(){
                    $(".g-signin2").css("display", "none");
                    $(".data").css("display", "none");
                    $(".g-signin").css("display", "block");
                    }
            )
            }
        </script>
    </body>
</html>