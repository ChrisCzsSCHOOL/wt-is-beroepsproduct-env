<?php

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
            $url = "medewerkeroverzicht.php";
            header("Location: $url");
            exit();
        }
        else 
        {
            $url = " mijnvluchten.php";
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

?>