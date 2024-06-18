<?php 

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
                        <div class="menuitem"><a href="incheckpagina.php">Inchecken</a></div>
                        <div class="menuitem"><a href="vluchtenoverzicht.php">Vluchtenoverzicht</a></div>
                        <div class="menuitem"><a href="medewerkerinlogpagina.php">Medewerker</a></div>
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
            $extraWhere = "vluchtnummer = '" . $vluchtnummer . "' AND bestemming = '". $vluchthaven ."'"; 
            $query = krijgTabel("Vlucht", $extraWhere);
        }
        else 
        {
            $extraWhere = "vluchtnummer = '" . $vluchtnummer . "'"; 
            $query = krijgTabel("Vlucht", $extraWhere);    
        }
    } 
    else 
    {
        if ($vluchthaven !== '')
        {
            $extraWhere = "bestemming = '". $vluchthaven ."'"; 
            // echo $extraWhere;
            $query = krijgTabel("Vlucht", $extraWhere);
            // print_r($query);
            
        }
        else 
        {
            $query = krijgTabel("Vlucht");
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
                <a href="medewerkerinlogpagina.php">
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
    $html .= '
            <tr>
                <th>Vluchtnummer</th>
                <th>Bestemming</th>
                <th>Vertrektijd</th>
                <th>Gatecode</th>
            </tr>
    ';

    foreach ($query as $rij)
    {
        $html .= '<tr>';
        $html .= '<td>'. $rij['vluchtnummer'] .'</td>';
        $html .= '<td>'. $rij['bestemming'] .'</td>';
        $html .= '<td>'. formatDate($rij['vertrektijd']).' '. formatTime($rij['vertrektijd']) .'</td>';
        $html .= '<td>Gate '. $rij['gatecode'] .'</td>'; 
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
        $url = " mijnvluchten.html";
        header("Location: $url");
        exit();
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
?>