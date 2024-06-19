<?php 
session_start();
ob_start();

// ----------------------------- algemene functies ----------------------------- 
function maakHeader() 
{   
    $html = "";
    $html .= '
        <!DOCTYPE html>
            <html lang="nl">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="css/normalize.css">
                <link rel="stylesheet" href="css/style.css">
                <title>Gelre airport</title>
            </head>
            <body>
                <header class="titel">
                    <a href="index.php">
                    <img 
                    src="images/gelreairportlogo.png" 
                    alt="Gelre airport logo"
                    width="200"            
                    height="200"
                    />
                    <h1>Gelre airport</h1>
                    </a>
                    
                </header>
                
                <div class="menu">
                    <div class="menuitem"><a href="index.php">Startpagina</a></div>
                    <div class="menuitem"><a href="allevluchten.php">Vluchtenoverzicht</a></div>
                    <div class="menuitem"><a href="incheckpagina.php">Inchecken</a></div>
                    <div class="menuitem"><a href="inlogpagina.php">Inloggen</a></div>
                </div>
    ';

    return $html;
}

function maakFooter() 
{
    $html = "";
    $html .= '
        <footer>
            <a href="https://www.han.nl">
                <img 
                src="images/gelreairportlogo.png" 
                alt="Gelre Airport"
                width="200"
                height="200"
                />
            </a>
            &copy; 2023 Christiaan Smits
            <a href="privacyverklaring.php">Privacyverklaring.</a>
        </footer>
    ';

    return $html;
}

function vluchtSortering($vluchtnummer, $vluchthaven)
{
    if ($vluchtnummer !== '' && is_numeric($vluchtnummer)) // kijkt of vluchtnummer een nummer is en niet leeg 
    {
        if ($vluchthaven !== '')
        {
            $extraWhere = "vluchtnummer = '" . $vluchtnummer . "' AND bestemming = '". $vluchthaven ."' AND vertrektijd > GETDATE()"; 
            $query = krijgTabel("Vlucht", $extraWhere);
        }
        else 
        {
            $extraWhere = "vluchtnummer = '" . $vluchtnummer . "' AND vertrektijd > GETDATE()"; 
            $query = krijgTabel("Vlucht", $extraWhere);    
        }
    } 
    else 
    {
        if ($vluchthaven !== '')
        {
            $extraWhere = "bestemming = '". $vluchthaven ."' AND vertrektijd > GETDATE()"; 
            // echo $extraWhere;
            $query = krijgTabel("Vlucht", $extraWhere);
            // print_r($query);
            
        }
        else 
        {
            $extraWhere = "vertrektijd > GETDATE()";
            $query = krijgTabel("Vlucht", $extraWhere);
        }
    }
    return $query;
}

// ----------------------------- einde algemene functies------------------------- 


// ----------------------------- index.php ----------------------------- 
function maakIndexMenu() 
{
    $html = "";
    $html .= '
    <h1>Welkom bij Gelre airport!</h1>
        <p>Log in als medewerker of als passagier:</p>

        <div class="grid">
            <div class="blokje">
                <a href="inlogpagina.php">
                <h4>Medewerker</h4>
                    <img 
                    src="images/Logo-Transparency-1-1.png" 
                    alt="Logo van medewerker"
                    width="200"
                    height="200"
                    />
                </a>
            </div>

            <div class="blokje">
                <a href="inlogpagina.php">
                <h4>Passagier</h4>
                    <img 
                    src="images/pictogram-passagier_609277-385.avif" 
                    alt="Logo van passagier"
                    width="200"
                    height="200"
                    />
                </a>    
            </div>

            <div class="blokje">
                <a href="registratieformulier.php">
                <h4>Sign up</h4>
                <img 
                src="images/5599504.png" 
                alt="Sign up logo"
                width="200"
                height="200"
                />
                </a>
            </div>
        </div>
    ';
    return $html;
}

// ----------------------------- einde index.php ----------------------------- 


// ----------------------------- begin allevluchten.php ----------------------------- 

function maakAlleVluchten()
{
    $vluchtnummer = isset($_GET['vluchtnummer']) ? htmlspecialchars($_GET['vluchtnummer']) : ''; // vluchtnummer sanitizen
    $vluchthaven = isset($_GET['vluchthaven']) ? htmlspecialchars($_GET['vluchthaven']) : ''; // vluchthaven sanitizen
    // echo $vluchthaven;

    $query = vluchtSortering($vluchtnummer, $vluchthaven);

    $html = '<div>';
    $html .= '<table>';
    $html .= '<tr>';
    $html .= '<th>Vluchtnummer</th>';
    $html .= '<th>Bestemming</th>';
    $html .= '<th>Vertrektijd</th>';
    $html .= '<th>Gatecode</th>';
    if (isMedewerker($_SESSION['gebruikersnaam']))
    {
        $html .= '<th>Max aantal</th>';
        $html .= '<th>Max gewicht pp</th>';
        $html .= '<th>Max totaalgewicht</th>';
    }
    $html .= '</tr>';

    foreach ($query as $rij)
    {
        $html .= '<tr>';
        $html .= '<td>'. $rij['vluchtnummer'] .'</td>';
        $html .= '<td>'. $rij['bestemming'] .'</td>';
        $html .= '<td>'. formatDate($rij['vertrektijd']).' '. formatTime($rij['vertrektijd']) .'</td>';
        $html .= '<td>Gate '. $rij['gatecode'] .'</td>'; 
        if (isMedewerker($_SESSION['gebruikersnaam']))
        {
            $html .= '<td>'.$rij['max_aantal'].'</td>';
            $html .= '<td>'.$rij['max_gewicht_pp'].'</td>';
            $html .= '<td>'.$rij['max_totaalgewicht'].'</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</table>';
    $html .= '</div>';

    return $html;
}

function maakAlleVluchtenMenu()
{
    $html = "";
    $html.= '
        <h2>Alle vluchten:</h2>
        <h3>Sorteren op:</h3>

        <form method="get" action="">
            <div class="grid">
                <div class="formitem">
                    <label for="vluchtnummer">Vluchtnummer</label>
                    <input type="text" name="vluchtnummer" id="vluchtnummer">
                </div>
            </div>
            <div class="grid">
                <div class="formitem">
                    <label for="vluchthaven">Vluchthaven</label>
                    <select name="vluchthaven" id="vluchthaven">
                        <option value="">Maak een keuze</option>
                        '. maakAlleVluchtcodes() .'
                    </select>
                </div>
            </div>
            <div class="grid">
                <div class="formitem">
                    <button type="submit">Submit</button>
                </div>
            </div>
        </form>
    ';
    return $html;
}

function maakAlleVluchtcodes()
{
    $query = krijgTabel("Luchthaven");
    $html = '';

    foreach ($query as $rij) 
    {
        $html .= '<option value="'. htmlspecialchars($rij['luchthavencode']) .'">'. htmlspecialchars($rij['luchthavencode']) .'</option>';
    }

    return $html;
}

// ----------------------------- einde allevluchten.php ----------------------------- 


// ----------------------------- begin inlogpagina.php ----------------------------- 

function maakInlogpagina()
{
    $html = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["inloggen"]))
    {
        if (is_numeric($_POST["username"])){ $passagiernummer = htmlspecialchars($_POST["username"]); } else { $passagiernummer = 0; }
        $wachtwoord = htmlspecialchars($_POST["password"]);
        $wachtwoordHash = password_hash($wachtwoord, PASSWORD_DEFAULT);
        $html .= valideerLogin($passagiernummer, $wachtwoord);
    }

    $html .= '
            <div class="gridform">
                <form method="post" action="">
                    <div class="formitem">
                        <label for="username">Passagiersnummer</label>
                        <input 
                        required
                        pattern="[0-9]+"
                        title="Passagiersnummer mag alleen bestaan uit nummers"
                        type="text" 
                        name="username" 
                        id="username"
                        />
                    </div>
                    
                    <div class="formitem">
                        <label for="password">Wachtwoord</label>
                        <input required 
                        type="password" 
                        name="password" 
                        id="password"
                        />
                    </div>
                    <button type="sumbit" name="inloggen">Log in</button>
                <p>Nog geen account? <a id="zwartelink" href="registratieformulier.php">Klik dan hier!</a></p>
                </form>
            </div>';


    return $html;
}

function valideerLogin($gebruikersnaam, $wachtwoord)
{
    $valideer = inloggen($gebruikersnaam, $wachtwoord);
    if ($valideer)
    {
        ob_end_clean();
        $_SESSION['gebruikersnaam'] = $gebruikersnaam;
        $_SESSION['loggedIn'] = true; 
        return loginMelding("Goed");
    }
    else 
    {
        return loginMelding("Fout");
    }
}

function loginMelding($goedOfFout)
{
    if ($goedOfFout == "Goed")
    {
        if (isMedewerker($_SESSION['gebruikersnaam'])) 
        { // als de gebruikersnaam waarmee ingelogd is een werknemer is, dan moet deze doorgestuurd worden naar het medewerkeroverzicht
            $url = "allevluchten.php";
            header("Location: $url");
            exit();
        }
        else 
        {
            $url = " mijnvluchten.html";
            header("Location: $url");
            exit();
        }
    }
    elseif ($goedOfFout == "Fout")
    {
        $html = '<h2>Ingevulde gegevens zijn fout</h2>';
        return $html;
    }
    elseif ($goedOfFout == "NummerFout")
    {
        $html = '<h2>Passagiersnummer moet een nummer zijn</h2>';
        return $html;
    }
}

// ----------------------------- einde inlogpagina.php ----------------------------- 


// ----------------------------- begin incheckpagina.php ---------------------------

function maakIncheckFormulier()
{
    //if (isset($_SESSION['gebruikersnaam']) || true)
    if (true)
    {
        //if (isMedewerker($_SESSION['gebruikersnaam']) || true)
        if (true)
        {
            $html = '';
            $html .=  
            '<h3>Inchecken:</h3>
                <div class="gridform">
                <form method="post" action="mijnvluchten.html">
                    <div class="formitem">
                        <label for="ticketnummer">Ticketnummer: (GELRVLCHT...)</label>
                        <input required 
                        type="text" 
                        name="ticketnummer" 
                        id="ticketnummer"
                        pattern="GELRVLCHT.*" 
                        title="Ticketnummer moet starten met GELRVLCHT..." 
                        placeholder="GELRVLCHT..."
                        />
                    </div>

                    <div class="formitem">
                        <label for="vluchtnummer">Vluchtnummer: (KLMGELR...)</label>
                        <input required 
                        type="text" 
                        name="vluchtnummer" 
                        id="vluchtnummer"
                        pattern="KLMGELR.*" 
                        title="Vluchtnummer moet starten met KLMGELR..." 
                        placeholder="KLMGELR..."
                        />
                    </div>
                    <!-- https://chat.openai.com/share/bb7700a9-906a-4660-9565-46d9e687b05c voor pattern en title hierboven!-->
                    
                    <div class="formitem">
                        <label for="firstname">Voornaam</label>
                        <input required 
                        type="text" 
                        name="firstname" 
                        id="firstname"
                        placeholder="Voornaam"
                        />
                    </div>
                    
                    <div class="formitem">
                        <label for="lastname">Achternaam</label>
                        <input 
                        required 
                        type="text" 
                        name="lastname" 
                        id="lastname"
                        placeholder="Achternaam"
                        />
                    </div>
                            
                    <div class="formitem">
                        <label for="koffergewicht">Koffergewicht</label>
                        <select required name="koffergewicht" id="koffergewicht">
                            <option value="">Maak een keuze</option>
                            <option value="10">10kg</option>
                            <option value="20">20kg</option>
                            <option value="30">30kg</option>
                            <option value="40">40kg</option>
                        </select>
                    </div>
                    
                    <div class="formitem">
                        <label for="koffergewicht2">Koffergewicht koffer 2 (optioneel)</label>
                        <select name="koffergewicht2" id="koffergewicht2">
                            <option value="">Maak een keuze</option>
                            <option value="10">10kg</option>
                            <option value="20">20kg</option>
                            <option value="30">30kg</option>
                            <option value="40">40kg</option>
                        </select>
                    </div>
                    <input 
                    type="submit" 
                    value="Check in!"
                    />
                </form>
            </div>
            <div class="grid">
                <div class="blokje2">
                    <h4>Meer dan 2 koffers?</h4>
                    <p>Klik dan <strong><a href="kofferextra.html">hier!</a></strong></p>
                </div>
            </div>';

        }
        else 
        {
            // $url = ' index.php';
            // header("Location: $url");
            // exit();
        }
    }
    return $html;
}

// ----------------------------- einde incheckpagina.php ---------------------------


// --------------------------- begin registratieformulier.php ----------------------

function maakRegistratieformulier()
{
    $html = '';
    $nieuwPassagiernummer = bepaalHoogstePassagiernummer();

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registreren"]))
    {
        $passagiersnummer = $nieuwPassagiernummer;
        $wachtwoord = htmlspecialchars($_POST["password"]);
        $naam = htmlspecialchars($_POST['lastname']);
        $gender = htmlspecialchars($_POST['gender']);
        $html .= valideerRegistratie($passagiersnummer, $wachtwoord, $naam, $gender);

    }

    $html .= 
    '
    <h2>Aanmelden</h2>
    <p>Meld je aan om <em>klant</em> te worden!</p>
    
        <div class="gridform">
        <form method="post" action="">
            <div class="formitem">
                <label for="password">Wachtwoord</label>
                <input required 
                type="password" 
                name="password" 
                id="password"
                />
            </div>
            <div class="formitem">
                <label for="Vluchtnummer">Vluchtnummer</label>
                <input 
                required 
                type="text" 
                name="name" 
                id="name"
                />
            </div>
            <div class="formitem">
                <label for="lastname">Naam</label>
                <input 
                required 
                type="text" 
                name="name" 
                id="name"
                />
            </div>
            <div class="formitem">
                <label for="gender">Geslacht</label>
                <select required name="gender" id="gender">
                    <option value="">Maak een keuze</option>
                    <option value="M">Man</option>
                    <option value="V">Vrouw</option>
                    <option value="x">Anders</option>
                </select>
            </div>
            
            <label for="voortgang">Voortgang:</label>
            <progress id="voortgang" max="50" value="25">50%</progress>

            <button type="submit" name=registreren">Meld je aan!</button>
        </form>
        </div>
    ';

    return $html;
}

function valideerRegistratie($passagiernummer, $wachtwoord, $naam, $gender)
{
    if (registreren($passagiernummer, $wachtwoord, $naam, $gender))
    {
        // correcte registratie
        // doorsturen goede pagina
        echo 'goede registratie';
        echo $passagiernummer; 
        echo $wachtwoord;
        echo $naam;
        echo $gender;
    }
    else
    {
        // foute registratie
        // return $html .= <h2>Fout</h2>
    }

}

// --------------------------- einde registratieformulier.php ----------------------


// --------------------------- begin mijnvluchten.php ------------------------------

function maakEigenVluchten()
{
    echo $_SESSION['gebruikersnaam'];

    $db = maakVerbinding();
    $sql = "select P.vluchtnummer, P.passagiernummer, V.bestemming, V.vertrektijd, P.stoel 
             from Passagier P LEFT JOIN Vlucht V ON V.vluchtnummer = P.vluchtnummer
             
             WHERE passagiernummer = '". $_SESSION['gebruikersnaam']. "'";
//";

    $query = $db->prepare($sql);
    $query->execute();
    $queryArray = $query->setFetchMode(PDO::FETCH_ASSOC);
    
    $html = '';
    $html .= '<h2>Mijn vluchten</h2>';
    $html .= '<div><table><tr>';
    $html .= '<th>Vluchtnummer</th>';
    $html .= '<th>Passagiernummer</th>';
    $html .= '<th>Bestemming</th>';
    $html .= '<th>Vertrektijd</th>';
    $html .= '<th>Stoelnummer</th>';
    $html .= '</tr>';
    
    foreach($query as $rij)
    {   
        $html .= '<tr>';
        $html .= '<td>'.$rij['vluchtnummer'].'</td>';
        $html .= '<td>'.$rij['passagiernummer'].'</td>';
        $html .= '<td>'.$rij['bestemming'].'</td>';
        $html .= '<td>'. formatDate($rij['vertrektijd']).' '. formatTime($rij['vertrektijd']).'</td>';
        $html .= '<td>'.$rij['stoel'].'</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
    $html .= '</div>';

    return $html;

}

// --------------------------- einde mijnvluchten.php ------------------------------

?>