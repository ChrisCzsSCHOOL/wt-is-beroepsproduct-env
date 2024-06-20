<?php
function vluchtSortering($vluchtnummer = '', $vluchthaven = '')
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

function maakAlleVluchten()
{
    $vluchtnummer = isset($_GET['vluchtnummer']) ? htmlspecialchars($_GET['vluchtnummer']) : ''; // vluchtnummer sanitizen
    $vluchthaven = isset($_GET['vluchthaven']) ? htmlspecialchars($_GET['vluchthaven']) : ''; // vluchthaven sanitizen
    $_SESSION['gebruikersnaam'] = isset($_SESSION['gebruikersnaam']) ? htmlspecialchars($_SESSION['gebruikersnaam']) : '';
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
        if (isMedewerker($_SESSION['gebruikersnaam']))
        {
            $html .= '<td><a href="specifiekevlucht.php?vluchtnummer='. $rij['vluchtnummer'] .'">'.$rij['vluchtnummer'].'</a></td>';
        }
        else 
        {
            $html .= '<td>'.$rij['vluchtnummer'].'</td>';
        }
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

function maakSorteringMenu($VluchtOfPassagier, $vluchtnummer = '')
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


function maakEigenVluchten()
{
    $gebruikersnaam = isset($_SESSION['gebruikersnaam']) ? htmlspecialchars($_SESSION['gebruikersnaam']) : '';

    $extraWhere = 'P.passagiernummer = :passagiernummer';
    $params = array('passagiernummer' => $gebruikersnaam);

    $query = krijgTabel('Passagier P LEFT JOIN Vlucht V ON V.vluchtnummer = P.vluchtnummer', $extraWhere, null, 'P.vluchtnummer, P.passagiernummer, V.bestemming, V.vertrektijd, P.stoel', $params);


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

function maakAllePassagiersOpVlucht()
{
    if (isset($_SESSION['gebruikersnaam']) && isMedewerker($_SESSION['gebruikersnaam'])) {
        $vluchtnummer = isset($_GET['vluchtnummer']) ? htmlspecialchars($_GET['vluchtnummer']) : ''; 
        $passagiernummer = isset($_GET['passagiernummer']) ? htmlspecialchars($_GET['passagiernummer']) : ''; 
        
        $html = '';

        if ($vluchtnummer && is_numeric($vluchtnummer))
        {
            if (is_numeric($passagiernummer) && $passagiernummer)
            {
                $extraWhere = 'P.vluchtnummer = :vluchtnummer AND P.passagiernummer = :passagiernummer';
                $params = array('vluchtnummer' => $vluchtnummer, 'passagiernummer' => $passagiernummer);
            }
            else
            {
                $extraWhere = 'P.vluchtnummer = :vluchtnummer';
                $params = array('vluchtnummer' => $vluchtnummer);
            }

            $query = krijgTabel('Passagier P LEFT JOIN Vlucht V ON V.vluchtnummer = P.vluchtnummer', $extraWhere, null, 'P.vluchtnummer, P.passagiernummer, P.naam, V.bestemming, V.vertrektijd, P.stoel', $params);

            $html .= '<h2>Passagiers op vlucht '. $vluchtnummer.'</h2>';
            $html .= '<div><table><tr>';
            $html .= '<th>Passagiernummer</th>';
            
            if($passagiernummer)
            {
                $html .= '<th>Naam</th>';
            }
            
            $html .= '<th>Bestemming</th>';
            $html .= '<th>Stoelnummer</th>';
            $html .= '</tr>';

            foreach($query as $rij)
            {
                $html .= '<tr>';
                $html .= '<td><a href="specifiekevlucht.php?vluchtnummer='. $vluchtnummer .'&passagiernummer='. $rij['passagiernummer'] .'">'.$rij['passagiernummer'].'</td>';

                if($passagiernummer)
                {
                    $html .= '<td>'.$rij['naam'].'</td>';
                }

                $html .= '<td>'.$rij['bestemming'].'</td>';
                $html .= '<td>'.$rij['stoel'].'</td>';
                $html .= '</tr>';
            }
            $html .= '</table></div>';
        }
        else 
        {
            $url = 'allevluchten.php';
            header('Location: '. $url);
            exit();
        }
        return $html;
    }
    else 
    {
        $url = 'index.php';
        header('Location: '. $url);
        exit();
    }
}

?>