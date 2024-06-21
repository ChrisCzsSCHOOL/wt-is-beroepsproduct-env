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
            <form method="POST" action="passagiertoevoegen.php">
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
                    <label for="naam">Naam</label>
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
        $passagiernummer = bepaalHoogsteNummer("Passagier", "passagiernummer");
        $attempt++;
    } while (!isUniekNummer('Passagier', 'passagiernummer', $passagiernummer) && $attempt < $maxAttempts);

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
            $vluchtnummer = bepaalHoogsteNummer("Vlucht", "vluchtnummer");
        } while (!isUniekNummer('Vlucht', 'vluchtnummer', $vluchtnummer));

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
            <form method="post" action="vluchttoevoegen.php">
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

function isUniekNummer($tabel, $kolom, $nummer) {
    $db = maakVerbinding();
    $sql = "SELECT COUNT(*) FROM $tabel WHERE $kolom = :nummer";
    $stmt = $db->prepare($sql);
    $stmt->execute([':nummer' => $nummer]);
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

function maakPassagierWijzingen()
{
    $html = '';
    if (isMedewerker($_SESSION['gebruikersnaam']))
    {
        $passagiernummer = 0;
        if (isset($_GET['passagiernummer']) && is_numeric($_GET['passagiernummer']))
        {
            $passagiernummer = htmlspecialchars($_GET['passagiernummer']);
            $extraWhere = 'passagiernummer = :passagiernummer';
            $params = array('passagiernummer' => $passagiernummer);
        
            $query = krijgTabel("Passagier", $extraWhere, null,'', $params);
        }


        $html .= '
            <form method="get" action="passagierwijzigen.php">
                    <div class="grid">
                        <div class="formitem">
                            <label for="passagiernummer">Passagiernummer</label>
                            <input 
                            type="number" 
                            name="passagiernummer" 
                            id="passagiernummer"';

                            if (isset($_GET['passagiernummer']) && is_numeric($_GET['passagiernummer']))
                            {
                                $html .= ' placeholder="'. htmlspecialchars($_GET['passagiernummer']) .'"';
                            }

                            $html .= '>
                        </div>
                    </div>
                    <div class="grid">
                        <div class="formitem">
                            <button type="submit">Submit</button>
                        </div>
                    </div>
                </form>
        ';
        if (isset($query))
        {
            foreach ($query as $rij)
            {
                if ($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['wijzigen']))
                { 
                    if (is_numeric($_POST["vluchtnummer"])) { $vluchtnummer = htmlspecialchars($_POST["vluchtnummer"]); } else { $vluchtnummer = 0; }
                    $stoel = htmlspecialchars($_POST['stoel']);
                    $html .= valideerGegevens($passagiernummer, $vluchtnummer, $stoel);
                }

                $html .= '
                    <form method="post" action="passagierwijzigen.php">
                        <div class="grid">
                            <div class="formitem">
                                <label for="vluchtnummer">Vluchtnummer</label>
                                <input 
                                type="number" 
                                name="vluchtnummer" 
                                id="vluchtnummer"
                                placeholder="'. $rij['vluchtnummer'] .'">
                            </div>
                        </div>
                        <div class="grid">
                            <div class="formitem">
                                <label for="stoel">Stoel</label>
                                <input 
                                type="text" 
                                name="stoel" 
                                id="stoel"
                                placeholder="'. $rij['stoel'] .'">
                            </div>
                        </div>
                        <div class="grid">
                            <div class="formitem">
                                <button type="submit" name="wijzigen">Submit</button>
                            </div>
                        </div>
                    </form>
                ';
            }
        }
    }
    else 
    {
        $url = 'index.php';
        header("Location: $url");
        exit();
    }
    return $html;
}

function valideerGegevens($passagiernummer, $vluchtnummer, $stoel)
{
    $html = '';

    $valideer = wijzigPassagierGegevens($passagiernummer, $vluchtnummer, $stoel);
    if ($valideer)
    {
        $html .= '<h2>Wijzigen is gelukt!</h2>';
    }
    else 
    {
        $html .= '<h2>Wijzigen is NIET gelukt!</h2>';
    }
    return $html;
}

?>