<?php

function maakMedewerkerPagina()
{
    if (isset($_SESSION['gebruikersnaam']))
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
                            src="images/images-removebg-preview.png" 
                            alt="passagier toevoegen"
                            width="200"
                            height="200"
                            />
                        </a>
                        </div>
                    
                    
                    <div class="blokje">
                        <a href="passagierwijzigen.php">
                        <h4>Passagier wijzigen</h4>
                            <img 
                            src="images/pngtree-edit-icon-image_1344389-removebg-preview.png" 
                            alt="passagier wijzigen"
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
    }
    
    return $html;
}

function maakPassagierToevoegen() 
{
    $html = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["toevoegen"])) 
    {
        regelRegistratieVariabele('medewerker');
    }

    $html .= '
        <h2>Maak nieuwe passagier aan:</h2>

        <div class="gridform">
            <form method="POST" action="">
                <div class="formitem">
                    <label for="vluchtnummer">Vluchtnummer:</label>
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
                    pattern="^(?=.*[A-Z])(?=.*\d).+$"
                    />
                </div>
                <button type="submit" name="toevoegen">Meld passagier aan</button>
            </form>
        </div>
    ';

    return $html;
}

function regelRegistratieVariabele($RegOfPas)
{
    $html = '';

    $vluchtnummer = is_numeric($_POST['vluchtnummer']) ? htmlspecialchars($_POST["vluchtnummer"]) : 0;
    $naam = htmlspecialchars($_POST['naam']);
    $wachtwoord = htmlspecialchars($_POST['password']); 
    $wachtwoordHash = password_hash($wachtwoord, PASSWORD_DEFAULT);

    $maxAttempts = 5;
    $attempt = 0;
    do {
        $passagiernummer = bepaalHoogstePassagiernummer();
        $attempt++;
    } while (!isUniekPassagiernummer($passagiernummer) && $attempt < $maxAttempts);

    if ($attempt == $maxAttempts) {
        $html .= '<h2>Kon geen uniek passagiernummer genereren, probeer opnieuw.</h2>';
    } 
    else 
    {
        $_SESSION['passagiernummer'] = $passagiernummer;
        $_SESSION['vluchtnummer'] = $vluchtnummer;
        $_SESSION['naam'] = $naam;
        $_SESSION['wachtwoord'] = $wachtwoordHash;

        $toevoegenGelukt = registreren($vluchtnummer, $passagiernummer, $wachtwoordHash, $naam); 

        if ($toevoegenGelukt) {
            if($RegOfPas == 'medewerker')
            {
                $url = 'medewerkeroverzicht.php';
                header("Location: $url");
                exit();
            }
            elseif ($RegOfPas == 'registreren')
            {
                $url = 'incheckpagina.php';
                header("Location: $url");
                exit();
            }
        } 
        else 
        {
            $html .= '<h2>Registratie mislukt, probeer opnieuw.</h2>';
        }
    }
    return $html;
}

function isUniekPassagiernummer($passagiernummer) {
    $db = maakVerbinding();
    $sql = "SELECT COUNT(*) FROM Passagier WHERE passagiernummer = :passagiernummer";
    $stmt = $db->prepare($sql);
    $stmt->execute([':passagiernummer' => $passagiernummer]);
    return $stmt->fetchColumn() == 0;
}

function maakVluchtToevoegen()
{
    $html = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["toevoegen"]))
    {
        $bestemming = htmlspecialchars($_POST["vluchthaven"]);
        $max_aantal = is_numeric($_POST['max_aantal']) ? $_POST['max_aantal'] : null;
        $max_gewicht_pp = is_numeric($_POST['max_gewicht_pp']) ? $_POST['max_gewicht_pp'] : null;
        $max_totaalgewicht = is_numeric($_POST['max_totaalgewicht']) ? $_POST['max_totaalgewicht'] : null;
        $maatschappijcode = htmlspecialchars($_POST['maatschappijcode']);

        do 
        {
            $vluchtnummer = bepaalHoogsteVluchtnummer();
        } while (!isUniekVluchtnummer($vluchtnummer));

        // echo $vluchtnummer;

        $_SESSION['bestemming'] = $bestemming;
        $_SESSION['max_aantal'] = $max_aantal;
        $_SESSION['max_gewicht_pp'] = $max_gewicht_pp;
        $_SESSION['max_totaalgewicht'] = $max_totaalgewicht;
        $_SESSION['maatschappijcode'] = $maatschappijcode;

        // var_dump($vluchtnummer, $bestemming, $max_aantal, $max_gewicht_pp, $max_totaalgewicht, $maatschappijcode);

        $toevoegenGelukt = aanmakenVlucht($vluchtnummer, $bestemming, $max_aantal, $max_gewicht_pp, $max_totaalgewicht, $maatschappijcode, null, null);
        if ($toevoegenGelukt)
        {
            echo 'toevoegengelukt if statement';
            $url = 'medewerkeroverzicht.php';
            header("Location: $url");
            exit();
        }
        else 
        {
            echo 'toevoegengelukt else statement';
            $html .= '<h2>Vlucht toevoegen mislukt. Probeer opnieuw.</h2>';
        }
    }

    $html .= '
        <h2>Maak vlucht aan</h2>
        <div class="gridform">
            <form method="post" action="">
                <div class="formitem">
                    <label for="vluchthaven">Vluchthaven</label>
                    <select name="vluchthaven" id="vluchthaven">
                        <option value="">Maak een keuze</option>
                        '. maakAlleVluchtcodes() .'
                    </select>
                </div>
                <div class="formitem">
                    <label for="vluchthaven">Maatschappijcode</label>
                    <select name="maatschappijcode" id="maatschappijcode">
                        <option value="">Maak een keuze</option>
                        '. maakAlleMaatschappijcodes() .'
                    </select>
                </div>
                <div class="formitem">
                    <label for="max_aantal">Maximaal aantal personen</label>
                    <input 
                    required 
                    type="number" 
                    name="max_aantal" 
                    id="max_aantal"
                    placeholder="aantal personen"
                    />
                </div>
                <div class="formitem">
                    <label for="max_gewicht_pp">Maximaal gewicht per persoon</label>
                    <input 
                    required 
                    type="number" 
                    name="max_gewicht_pp" 
                    id="max_gewicht_pp"
                    placeholder="gewicht in kg"
                    />
                </div>
                <div class="formitem">
                    <label for="max_totaalgewicht">Maximaal totaalgewicht</label>
                    <input 
                    required 
                    type="number" 
                    name="max_totaalgewicht" 
                    id="max_totaalgewicht"
                    placeholder="gewicht in kg"
                    />
                </div>
                <input type="submit" name="toevoegen" value="Maak nieuwe vlucht aan!" />
            </form>
        </div>
    ';

    return $html;
}

function isUniekVluchtnummer($vluchtnummer) {
    $db = maakVerbinding();
    $sql = "SELECT COUNT(*) FROM Vlucht WHERE vluchtnummer = :vluchtnummer";
    $stmt = $db->prepare($sql);
    $stmt->execute([':vluchtnummer' => $vluchtnummer]);
    return $stmt->fetchColumn() == 0;
}

function maakAlleMaatschappijcodes()
{
    $query = krijgTabel("Maatschappij");
    $html = '';

    foreach ($query as $rij) 
    {
        $html .= '<option value="'. htmlspecialchars($rij['maatschappijcode']) .'">'. htmlspecialchars($rij['maatschappijcode']) .'</option>';
    }

    return $html;
}

?>