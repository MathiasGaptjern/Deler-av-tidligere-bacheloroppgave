<?php
session_start();
include 'Database.php';

$studentnr = $_SESSION['studnr'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>USN grupper</title>
    <link rel="Stylesheet" type="text/css" href="styling.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<style>
    .vside{
        height: 1130px;
    }
</style>
<body>
<div class="top">
    <a href="start.php"><img src="USN_logo.png" id="logo"></a>
    <div class="sideknapper">
        <form method="post" action="opprettGruppe.php"><input type="submit" value="Opprett gruppe" id="sideknapp1" style=""></form>
        <form method="post" action="#"><input type="submit" value="Mine grupper" id="sideknapp2" name="minegrupper" style=""></form>
        <form method="post" action="profil.php"><input type="submit" value="Min profil" id="sideknapp3" style=""></form>
    </div>
    <?php
    include "logout.php";
    ?>
</div>
<div class="vside" id="venstreside">
</div>
<div id="innhold">

    <button class="menyknapp" onclick="visSide()">
        <div class="bar1"></div>
        <div class="bar2"></div>
        <div class="bar3"></div>
    </button>

    <div class="sokboks" id="sokboksen">
            <p>Søk på gruppenavn</p>
            <input type="text" id="myInput" onkeyup="navneSok()" placeholder="Søk etter gruppenavn" title="Type in a name">
            <p>Velg forventning av medlemmer</p>
            <div class="sjekkbokser">
                <label><input type="checkbox" name="forventning" value="A-B">Høy (A-B)</label><br>
                <label><input type="checkbox" name="forventning" value="C-D">Middels (C-D)</label><br>
                <label><input type="checkbox" name="forventning" value="E">Lav (E)</label><br>
                <p>Velg gruppe størrelse</p>
                <label><input type="checkbox" name="medlemmer" value="2">2</label><br>
                <label><input type="checkbox" name="medlemmer" value="3">3</label><br>
                <label><input type="checkbox" name="medlemmer" value="4">4</label><br>
                <label><input type="checkbox" name="medlemmer" value="5">5</label><br>
                <label><input type="checkbox" name="medlemmer" value="6">6</label><br>
                <p>Velg tilgjenglihet</p>
                <label><input type="checkbox" name="tilgjenglihet" value="ofte">Ofte</label><br>
                <label><input type="checkbox" name="tilgjenglihet" value="sjelden">Sjelden</label><br>
                <label><input type="checkbox" name="tilgjenglihet" value="aldri">Over internett</label><br>
                <p>Velg emne</p>
                <?php
                    //Henter alle emner i databasen og skriver de ut som sjekkbokser
                    $resultat = mysqli_query($conn, "select emnekode, emnenavn from emne;");
                    if ($resultat->num_rows > 0) {
                        while($row = $resultat->fetch_assoc()){
                            echo "<label><input type='checkbox' name='emne' value='".$row["emnenavn"]."'>".$row["emnenavn"]."</label><br>";
                        }
                    }else{
                        echo "<p>Ingen emner</p>";
                    }
                ?>
            </div>
            <button id="soknapp" name="sokknappen" onclick="soket()">Søk</button>
            <?php
            //Legger til utskirft av grupper knapp dersom session variblen admin er satt
            if(isset($_SESSION["admin"])){
                echo "<button id='soknapp' onclick='utskrift()'>Utskrift av grupper</button><br>";
            };
            ?>
    </div>

    <div class="gruppeboks" id="gruppeboks1">
        <table id="thBesk">
            <th style='width: 38%;'>Gruppenavn  og  emne</th>
            <th style='text-align: center; width: 20%;'>Deltagere</th>
            <th style='text-align: center; width: 19%;'>Tilgjenlighet</th>
            <th style='text-align: center; width: 13%;'>Forventning</th>
            <th style='text-align: center;  width: 10%;'>Størrelse</th>
        </table>
        <br>
        <?php
        //Setter opp sql settning for oversikt over alle grupper
        $statement = $conn->prepare("select count(gruppedeltaglse.gruppeid) as deltar, gruppedeltaglse.gruppeid, navn, beskrivelse, emnenavn, gruppe.emnekode, tilgjenglihet, forventning, deltagere, skaper 
        from gruppe, emne, gruppedeltaglse
        where gruppe.emnekode = emne.emnekode and gruppe.gruppeid = gruppedeltaglse.gruppeid and deltagelse is null group by gruppedeltaglse.gruppeid order by gruppe.gruppeid DESC");
        
        //Hvis knappen Mine grupper er presset settes opp sql for grupper hvor brukeren som er logget er en deltager
        if (isset($_POST["minegrupper"])){
            $statement = $conn->prepare("select 1 as deltar, gruppe.gruppeid, gruppe.navn, beskrivelse,emnenavn, gruppe.emnekode, tilgjenglihet, forventning ,deltagere, gruppedeltaglse.studentnr as skaper 
            from gruppe, gruppedeltaglse, emne 
            where emne.emnekode = gruppe.emnekode and gruppe.gruppeid = gruppedeltaglse.gruppeid and studentnr=? order by gruppeid DESC");
            $statement->bind_param("s", $studentnr);
        }

        //Utfører sql og henter ut resultat
        $statement->execute();
        $resultat = $statement->get_result();

        //Setter opp variabel for inndeling av grupper på flere sider
        $antallSider = 0;

        //Hvis resultatet gir et resultat opprettes gruppe tabellen
        if ($resultat->num_rows > 0) {
            echo "<table id='gruppetabel'>";
            
            while($row = $resultat->fetch_assoc()){
                if($row["deltagere"]> $row["deltar"]){
                    //For hver rad i resultatet fra sql opprettes 2 rader i tabellen 
                    echo "<tbody>";
                    echo "<tr data-href='gruppe.php?gruppe=".$row["gruppeid"]."'>
                        <th style='width: 38%; padding-top: 20px;'>".$row["navn"]." ,  ".$row["emnenavn"]."</th>
                        <th style='width: 20%; padding-top: 20px; text-align: center;'></th>
                        <th style='text-align: center; width: 19%;'></th>
                        <th style='text-align: center; padding-top: 20px; width: 13%;'></th>
                        <th style='text-align: center;  padding-top: 20px; width: 10%;'></th></tr>
                        
                        <tr data-href='gruppe.php?gruppe=".$row["gruppeid"]."'>
                            <td style='width: 38%;'>".$row["beskrivelse"]."</td>";
                            //Henter ut alle gruppe medlemmer for hver enkelt gruppe
                            $deltagerResultat = mysqli_query($conn, "select student.studentnr, navn, deltagelse from gruppedeltaglse, student where gruppeid = ".$row["gruppeid"]." and gruppedeltaglse.studentnr = student.studentnr;");
                            if ($deltagerResultat->num_rows > 0) {
                                echo "<td style='width: 20%; text-align: center;'>";
                                //Setter opp variabler for å sjekke om brukeren søker eller deltar i en gruppe
                                $deltager = false;
                                $soker = false;
                                $teller = 0;
                                while($rad = $deltagerResultat->fetch_assoc()){
                                    $antallSider++;
                                    //Sjekker om medlemmene som deltar bare søker på gruppe eller faktisk har blitt godtatt in i gruppa
                                    if($rad["deltagelse"] == null){
                                        echo"".$rad["navn"]."<br> ";
                                        if($rad["studentnr"] == $studentnr){
                                            $deltager = true;
                                        }
                                    }else{
                                        $teller++;
                                        if($rad["studentnr"] == $studentnr){
                                            $soker = true;
                                        }
                                    }
                                }
                            }
                            //Hvis brukeren brukeren deltar i gruppa får de beskjed dersom det er noen nye søkere, hvis ikke får de beskjed om at de venter på deltagelse
                            if($deltager == true){
                                if($teller > 0){
                                    echo" <p style='margin-top: 0px; margin-bottom: -20px; padding: 0; color: red;'>Nye søkere: ".$teller."</p><br> ";
                                }
                            }else{
                                if($soker == true){
                                    echo" <p style='margin-top: 0px; margin-bottom: -20px; padding: 0; color: red;'>Venter på svar</p><br> ";
                                }
                            }
                            echo "</td>"; 
                            //forsetter utskrift for resten av tabellen
                            echo"
                            <td style='text-align: center; width: 19%;'>".$row["tilgjenglihet"]."</td>
                            <td style = 'text-align: center;  width: 13%;'>".$row["forventning"]."</td>";
                            echo "<td style = 'text-align: center;  width: 10%;'>".$row['deltagere']."</td></tr>";
                            echo "</tbody>";
                }
            }
            echo "</table>";
        }
        //Dersom resultat av spørringen er ingen ting skrives ut en melding
        else{
            echo "<p>Ingen grupper her enda</p>";
        }
        $statement->close();
        $conn->close();
        ?>
    </div>
    
    <div class="tabellknapper" id="tabellknapper">
        <button id="tilbake" onClick="sideteller(this.id)" style="width: 55px;">Forrige</button>
        <?php
        //Regner ut antall sider basert på hvor mange poster som skal være på hver side
        $antallSider =  $antallSider/16;
        $sideknappid = 1;
        for($i = 0; $i < $antallSider; $i++){
            echo "<button onClick='sideteller(this.id)' id = '$sideknappid'>$sideknappid</button>";
            $sideknappid++;
        }
        ?>
        <button id="neste" onClick="sideteller(this.id)" style="width: 55px;">Neste </button>
    </div>
</div>
<script>

    //Viser sokboksen dersom den har blitt gjemt (for hamburger meny på mobil skjerm)
    function visSide() {
        var x = document.getElementById("venstreside");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        };

        var x = document.getElementById("sokboksen");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        };
    }

    //Antall rader i tabellen for pagination
    var antallRader = 16;
    var under = 0;
    var over = 17;
    sidestart();

    //viser alle gruppene dersom de har blitt satt til å ikke vises
    function sidestart(){
        var table = document.getElementById("gruppetabel");
        var tr = table.getElementsByTagName("tr");
        document.getElementById("tilbake").disabled = true;

        var x = document.getElementById("tabellknapper");
        if (x.style.display === "none") {
            x.style.display = "block";
        }

        for (y = 0; y < tr.length; y++) {
            td = tr[y].getElementsByTagName("th")[0];
            if  (y > antallRader){
                if (td) {
                tr[y].style.display = "none";
                tr[y+1].style.display = "none";
                }
            }        
        }
    }

    //Dersom en pagination knapp blir trykket kommer den hit
    function sideteller(knapp){
        //Henter tabellen og setter et bestemt tall for hvormange rader som skal fjernes ved et trykk som en variabel for hvormange rader som vises
        var table = document.getElementById("gruppetabel");
        var tr = table.getElementsByTagName("tr");
        var tallet = 17;
        var rader = 17;

        //viser alle postene i tabellen
        for (y = 0; y < tr.length; y++) {
            td = tr[y].getElementsByTagName("th")[0];
                if (td) {
                rader++;
                tr[y].style.display = "";
                tr[y+1].style.display = "";
                }       
        }

        //Ettersom hvilken knapp ble trykket endres hvilke rader som skal vises
        if(knapp == "neste"){
            //viser alle over og alle under et bestemt tall
            document.getElementById("tilbake").disabled = false;
            under+=tallet;
            over+=tallet;
        }else{
            if(knapp == "tilbake"){
                //fjerner alle over og alle under et bestemt tall
                under-=tallet;
                over-=tallet;
            }else{
                //Dersom en knappen som ble trykket er 1 skal de første radene i tabellen visses
                if(knapp == "1"){
                    under=0;
                    over=17;
                }else{
                    //hvis ikke beregnes hvilke rader som skal vises basert på hvilket tall brukeren trykket på
                    document.getElementById("tilbake").disabled = false;
                    var tall = 1;
                    under=0;
                    over=17;
                    parseInt(knapp);
                    knapp -= tall;
                    for(i = 0; i < knapp; i++){
                        under+=tallet;
                        over+=tallet;
                    }
                }
            }
        }

        //Setter hvilken rader i tabellen skal vises og ikke vises basert på verdiene under og over
        for (y = 0; y < tr.length; y++) {
            td = tr[y].getElementsByTagName("th")[0];
            if  (y < under){
                if (td) {
                tr[y].style.display = "none";
                tr[y+1].style.display = "none";
                }
            }
            if  (y > over){
                if (td) {
                tr[y].style.display = "none";
                tr[y+1].style.display = "none";
                }
            }          
        }

        //Setter knapper trykkbare og ikke trykkbare basert på verdiene under og over
        if(under == 0){document.getElementById("tilbake").disabled = true;}

        if(over > rader){
            document.getElementById("neste").disabled = true;
        }else{
            document.getElementById("neste").disabled = false;
        }
    }

    // legger til eventlistener på alle radene i tabellen for å gjøre det mulig å klikke på de
    document.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll("tr[data-href]");
        rows.forEach(row => {
            row.addEventListener("click", () => {
                window.location.href = row.dataset.href;
            });
        });
    });

    //Dersom søkknappen blir trykket
    function soket(){
        //Henter tabellen og setter variabler for hver av sjekkboksene
        var input, filter, table, tr, td, i, txtValue;
        table = document.getElementById("gruppetabel");
        tr = table.getElementsByTagName("tr");

        var tabellknapper = document.getElementById("tabellknapper");
        tabellknapper.style.display = "none";

        var forventningSatt = null; 
        var forventning = document.getElementsByName('forventning');
        var medlemmerSatt = null;
        var medlemmer = document.getElementsByName('medlemmer');
        var tilgjenglihetSatt = null;
        var tilgjenglihet = document.getElementsByName('tilgjenglihet');
        var emneSatt = null;
        var emne = document.getElementsByName('emne');

        //Setter hele lista synelig før nytt søk
        for (y = 0; y < tr.length; y++) {
            td = tr[y].getElementsByTagName("th")[0];
            if (td) {
                tr[y].style.display = "";
                tr[y+1].style.display = "";
            }       
        }
        
        //Sjekker om en forventnings boks er satt
        for(var i=0; forventning[i]; ++i){
            if(forventning[i].checked){
                forventningSatt = forventning[i].value.toUpperCase();      
            }
        }

        //Sjekker om en antall medlemmer boks er satt
        for(var i=0; medlemmer[i]; ++i){
            if(medlemmer[i].checked){
                medlemmerSatt = medlemmer[i].value.toUpperCase();
            }
        }

        //Sjekker om en tilgjenglighets boks er satt
        for(var i=0; tilgjenglihet[i]; ++i){
            if(tilgjenglihet[i].checked){
                tilgjenglihetSatt = tilgjenglihet[i].value.toUpperCase();
            }
        }
        
        //Sjekker om en emne boks er satt
        for(var i=0; emne[i]; ++i){
            if(emne[i].checked){
                emneSatt = emne[i].value.toUpperCase();
            }
        }

        //Dersom en forventning boks er satt blir de andre boksene filtrert ut
        if(forventningSatt != null){
            for(var i=0; forventning[i]; ++i){
                if(forventning[i].checked){
                    
                }else{
                    forventningSatt = forventning[i].value.toUpperCase();
                    for (y = 0; y < tr.length; y++) {
                        td = tr[y].getElementsByTagName("td")[3];
                        if (td) {
                            txtValue = td.textContent || td.innerText;
                            if (txtValue.toUpperCase().indexOf(forventningSatt) > -1) {
                                tr[y].style.display = "none";
                                tr[y-1].style.display = "none";
                            }
                        }       
                    }
                }
            }
        }

        //Dersom en antall medlemmer boks er satt blir de andre boksene filtrert ut
        if(medlemmerSatt != null){
            for(var i=0; medlemmer[i]; ++i){
                if(medlemmer[i].checked){
                    
                }else{
                    medlemmerSatt = parseInt(medlemmer[i].value);
                    for (y = 0; y < tr.length; y++) {
                        td = tr[y].getElementsByTagName("td")[4];
                        if (td) {
                            txtValue = parseInt(td.textContent || td.innerText);
                            if (txtValue == medlemmerSatt) {
                                tr[y].style.display = "none";
                                tr[y-1].style.display = "none";
                            }
                        }       
                    }
                }
            }
        }

        //Dersom en tilgjenlighets boks er satt blir de andre boksene filtrert ut
        if(tilgjenglihetSatt != null){
            for(var i=0; tilgjenglihet[i]; ++i){
                if(tilgjenglihet[i].checked){
                    
                }else{
                    tilgjenglihetSatt = tilgjenglihet[i].value.toUpperCase();
                    for (y = 0; y < tr.length; y++) {
                        td = tr[y].getElementsByTagName("td")[2];
                        if (td) {
                            txtValue = td.textContent || td.innerText;
                            if (txtValue.toUpperCase().indexOf(tilgjenglihetSatt) > -1) {
                                tr[y].style.display = "none";
                                tr[y-1].style.display = "none";
                            }else{
                            }
                        }       
                    }
                }
            }
        }

        //Dersom en emne boks er satt blir de andre boksene filtrert ut
        if(emneSatt != null){
            for(var i=0; emne[i]; ++i){
                if(emne[i].checked){
                    
                }else{
                    emneSatt = emne[i].value.toUpperCase();
                    for (y = 0; y < tr.length; y++) {
                        td = tr[y].getElementsByTagName("th")[0];
                        if (td) {
                            txtValue = td.textContent || td.innerText;
                            if (txtValue.toUpperCase().indexOf(emneSatt) > -1) {
                                tr[y].style.display = "none";
                                tr[y+1].style.display = "none";
                            }else{
                            }
                        }       
                    }
                }
            }
        }

        //Dersom ingen av boksene er satt blir alle radene synlige
        if(forventningSatt == null){
            if(medlemmerSatt == null){
                if(tilgjenglihetSatt == null){
                    if(emneSatt == null){
                        sidestart();
                    }
                }
            }
        }
    }

    //Dersom brukeren begynner å skrive i navnesok inputen fjernes og legges til de radene som ligner på søke inputen
    function navneSok() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("gruppetabel");
        tr = table.getElementsByTagName("tr");
        tr1 = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("th")[0];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    tr1[i+1].style.display = "";
                } else {
                    tr[i].style.display = "none";
                    tr1[i+1].style.display = "none";
                }
            }       
        }
    }

    //ved trykk på utskrifts knappen åpnes utskrift.php i en ny side
    function utskrift(){
        window.open('utskrift.php', '_blank');
    }
</script>
<?php
//Dersom brukeren er admin endres min profil teksten til admin side
if(isset($_SESSION["admin"])){
    ?>
    <script>
        document.getElementById("sideknapp3").value="Admin side";
        </script>
    <?php
};
?>
</body>
</html>