<?php

function maakIncheckFormulier()
{
    $html = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["toevoegen"])) 
    {
        $passagiernummer = is_numeric($_POST['passagiernummer']) ? htmlspecialchars($_POST["passagiernummer"]) : 0;
        // objectvolgnummer wordt bepaald door of er al eentje is.
        $gewicht = is_numeric($_POST['gewicht']) ? htmlspecialchars($_POST["gewicht"]) : 0;
        
        $_SESSION['passagiernummer'] = $passagiernummer;
        $_SESSION['gewicht'] = $gewicht;
        
        $kofferGeregistreerd = kofferRegistratie($passagiernummer, $gewicht);
        if ($kofferGeregistreerd)
        {
            // echo 'koffer geregistreerd';
            $url = 'incheckpagina.php';
            header("Location: $url");
            exit();
        }
        else 
        {
            $html .= '<h2>Koffer is te zwaar!</h2>';
        }
    }

    $html .= '
        <h2>Vul hier een koffer in!</h2>
        <p>Vul hier uw koffer in. Wanneer deze ingecheckt is kunt u de volgende koffer invullen.</p>
        <div class="gridform">
            <form method="post" action="incheckpagina.php">
                <div class="formitem">
                    <label for="passagiernummer">Passagiernummer</label>
                    <input 
                    required 
                    type="number" 
                    name="passagiernummer" 
                    id="passagiernummer"
                    placeholder="Passagiernummer"
                    />
                </div>
                <div class="formitem">
                    <label for="gewicht">Koffer gewicht in kg</label>
                    <input 
                    required 
                    type="number" 
                    name="gewicht" 
                    id="gewicht"
                    placeholder="gewicht in kg"
                    />
                </div>
                <input type="submit" name="toevoegen" value="Check koffer in!" />
            </form>
        </div>
    ';



   return $html;
}


?>