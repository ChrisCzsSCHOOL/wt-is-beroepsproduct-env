<?php

function maakMedewerkerPagina()
{
    if (isMedewerker($_SESSION['gebruikersnaam']))
    {
        $html = '';
        $html .= '
            <div class="grid">
                <div class="blokje">
                <a href="incheckpagina.php">
                <h4>Inchecken passagier</h4>
                    <img 
                    src="images/pngtree-airplane-destination-arrived-aeroplane-aircraft-picture-image_8175298.png" 
                    alt="Inchecken vliegtuig"
                    width="200"
                    height="200"
                    />
                </a>
                </div>

                <div class="blokje">
                    <a href="allevluchten.php">
                        <h4>Vluchtoverzicht</h4>
                        <img 
                        src="images/pngtree-flight-line-icon-png-image_9022398.png" 
                        alt="Vluchtenoverzicht"
                        width="200"
                        height="200"
                        />
                    </a>
                </div>
            
                <div class="blokje">
                    <a href="vluchttoevoegen.php">
                        <h4>Vlucht toevoegen</h4>
                        <img 
                        src="images/USP_boekjeeigenticket-01-244x300.png" 
                        alt="Vlucht toevoegen"
                        width="200"
                        height="200"
                        />
                    </a>
                </div>
            
            <div class="blokje">
                <a href="passagiertoevoegen.php">
                <h4>Passagier toevoegen</h4>
                    <img 
                    src="images/images.png" 
                    alt="passagier toevoegen"
                    width="200"
                    height="200"
                    />
                </a>
                </div>
            </div>
        ';
    }
    else 
    {
        $url = 'index.php';
        header("Location: $url");
        exit();
    }
    
    return $html;
}

function maakPassagierToevoegen() 
{
    $html = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["toevoegen"])) 
    {
        echo 'request is get';

        $vluchtnummer = is_numeric($_POST['vluchtnummer']) ? htmlspecialchars($_POST["vluchtnummer"]) : 0;
        $naam = htmlspecialchars($_POST['naam']);
        $wachtwoord = htmlspecialchars($_POST['password']); 
        $wachtwoordHash = password_hash($wachtwoord, PASSWORD_DEFAULT);

        // Zorgt ervoor dat er een uniek passagiersnummer komt
        do {
            $passagiernummer = bepaalHoogstePassagiernummer();
        } while (!isUniekPassagiernummer($passagiernummer));

        // Set session variables
        $_SESSION['passagiernummer'] = $passagiernummer;
        $_SESSION['vluchtnummer'] = $vluchtnummer;
        $_SESSION['naam'] = $naam;
        $_SESSION['wachtwoord'] = $wachtwoordHash;

        // Debug output
        echo $_SESSION['passagiernummer'], $_SESSION['vluchtnummer'], $_SESSION['naam'], $_SESSION['wachtwoord'];

        // Call the register function and check if it was successful
        $toevoegenGelukt = registreren($vluchtnummer, $passagiernummer, $wachtwoord, $naam); 
        if ($toevoegenGelukt) 
        {
            echo 'toevoegenGelukt';
            $url = 'medewerkeroverzicht.php';
            header("Location: $url");
            exit();
        } 
        else 
        {
            echo 'Registratie mislukt, probeer opnieuw.';
        }
    }

    $html .= '
        <h2>Maak nieuwe passagier aan:</h2>

            <div class="gridform">
            <form method="POST" action="">
                <div class="formitem">
                    <label for="vluchtnummer">Vluchtnummer: (KLMGELR...)</label>
                    <input required 
                    type="text" 
                    name="vluchtnummer" 
                    id="vluchtnummer" 
                    pattern="[0-9]+"
                    title="Vluchtnummer bestaat uit 6 cijfers" 
                    placeholder="Vluchtnummer"
                    />
                </div>

                <div class="formitem">
                    <label for="firstname">Naam</label>
                    <input required 
                    type="text" 
                    name="naam" 
                    id="naam"
                    placeholder="Naam"
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
                <button type="submit" name="toevoegen">Meld passagier aan</button>
            </form>
        </div>
    ';

    return $html;
}

function isUniekPassagiernummer($passagiernummer) {
    $db = maakVerbinding();
    $sql = "SELECT COUNT(*) FROM Passagier WHERE passagiernummer = :passagiernummer";
    $stmt = $db->prepare($sql);
    $stmt->execute([':passagiernummer' => $passagiernummer]);
    return $stmt->fetchColumn() == 0;
}

?>