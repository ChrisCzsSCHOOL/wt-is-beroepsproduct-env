<?php 

function krijgTabel($tabel, $extraWhere = '', $limit = null, $extraConditie = '', $params = array())
{

    // Ter verduidelijking voor deze functie, deze is gemaakt door mij tijdens het iproject.

    $db = maakVerbinding();
    $sql = "SELECT"; // begin van SQL

    if (!is_null($limit)) 
    { // Kijkt of er een top in het sql statement moet
        $sql .= " TOP $limit";
    }

    if ($extraConditie == '') 
    { // voor een eventuele extra conditie zoals alleen bepaalde kolommen krijgen
        $sql .= " * FROM $tabel WHERE 1=1";
    } 
    else 
    {
        $sql .= " " . $extraConditie . " FROM $tabel WHERE 1=1";
    }

    if (!empty($extraWhere) || $extraWhere !== '') 
    { // voor een eventuele extra WHERE conditie
        $sql .= " AND $extraWhere";
    }
    $query = $db->prepare($sql);

    if (!empty($params)) 
    { // bind de parameters aan de variabelen
        foreach ($params as $paramName => &$paramValue) 
        {
            $query->bindParam(":$paramName", $paramValue);
        }
    }
    $query->execute();

    return $query->fetchAll(PDO::FETCH_ASSOC); // retourneert de hele array.
}

function formatTime($time)
{
    $time = date_create($time);
    $time = date_format($time, "H:i:s");
    return $time;
}

function formatDate($datum)
{
    $datum = date_create($datum);
    $datum = date_format($datum, "d-M-Y");
    return $datum;
}

function inloggen($passagiernummer, $wachtwoord, $wachtwoordHash)
{
    $db = maakVerbinding();
    $sql = "SELECT * FROM Passagier WHERE passagiernummer = :passagiernummer";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':passagiernummer', $passagiernummer);
    $stmt->execute();
    $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC); 

    if ($gebruiker) // kijkt of gebruiker bestaat.
    {
        // echo "gebruiker if statement";
        // echo $gebruiker['wachtwoord'];
        // echo $wachtwoord;
        if (password_verify($wachtwoord, $gebruiker['wachtwoord']) || $wachtwoord == $gebruiker['wachtwoord'])
        {
            $check = true;
            // echo "Passagier geverifieerd";
        }
        else 
        {
            $check = false;
        }
    }
    else 
    {
        $check = false;
        $medewerkerSql = "SELECT * FROM Balie WHERE balienummer = :balienummer";

        $stmt = $db->prepare($medewerkerSql);
        $stmt->bindParam(':balienummer', $passagiernummer);
        $stmt->execute();
        $medewerker = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($medewerker) // kijkt of medewerker bestaat.
        {
            // echo "medewerker if statement";
            // echo $medewerker['wachtwoord'];
            // echo $wachtwoord;
            if ($wachtwoord == $medewerker['wachtwoord'])
            {
                $check = true;
                // echo "Medewerker geverifieerd";
            }
            else 
            {
                $check = false;
            }
        }
        else 
        {
            $check = false;	
        }

        return $check;
    }

    return $check;
}

function isMedewerker($balienummer)
{
    try 
    {
        $db = maakVerbinding();
        $sql = "SELECT * FROM Balie WHERE balienummer = :balienummer";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':balienummer', $balienummer);
        $stmt->execute();
        $medewerker = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($medewerker)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    catch (Exception $e)
    {
        return false;
    }
}

function bepaalHoogsteNummer($tabel, $kolom)
{
    $db = maakVerbinding();
    $sql = krijgTabel($tabel, '', null, "MAX($kolom) AS hoogsteNummer");

    $hoogsteNummer = $sql[0]['hoogsteNummer'] + 1;
    return $hoogsteNummer;
}

function registreren($vluchtnummer, $passagiernummer, $wachtwoord, $naam, $gender = 'x')
{
    $db = maakVerbinding();

    if (!isUniekNummer('Passagier', 'passagiernummer', $passagiernummer)) {
        throw new Exception('Duplicate passagiernummer detected. Please close the site and try again.');
    }

    // echo $vluchtnummer, $passagiernummer, $wachtwoord, $naam, $gender;
    try 
    {
        if (maxAantalBereikt($vluchtnummer, $passagiernummer))
        {
            return false;
        }
        else
        {
            $sql = "INSERT INTO Passagier (vluchtnummer, passagiernummer, wachtwoord, naam, geslacht) VALUES (:vluchtnummer, :passagiernummer, :wachtwoord, :naam, :geslacht)";

            $stmt = $db->prepare($sql); 
            $stmt->execute([
                'vluchtnummer' => $vluchtnummer,
                'passagiernummer' => $passagiernummer, 
                'wachtwoord' => $wachtwoord,
                'naam' => $naam,
                'geslacht' => $gender,
            ]);

            return true;
        }
    }
    catch (Exception $e)
    {
        echo $e->getMessage();
        return false;
    }
}

function maxAantalBereikt($vluchtnummer, $passagiernummer)
{
    $extraWhere = "vluchtnummer = :vluchtnummer";
    $params = array('vluchtnummer' => $vluchtnummer);
    $queryV = krijgTabel("Vlucht", $extraWhere, 1, '', $params);
    $queryP = krijgTabel("Passagier", $extraWhere, null, "COUNT(*) AS aantalPassagiers", $params);

    // print_r($queryP);
    // print_r($queryV);

    $max_aantal = 0;
    foreach ($queryV as $rij)
    {
        $max_aantal = $rij['max_aantal'];
    }

    
    // echo $max_aantal;
    
    $aantalPassagiers = $queryP[0]['aantalPassagiers'];
    if ($aantalPassagiers + 1 > $max_aantal)
    {
        // Kan niet nog een persoon bij de vlucht toegevoegd worden, want deze is vol.
        return false;
    }
    else
    {
        // Er is ruimte voor nog een persoon.
        return true;
    }

}

function aanmakenVlucht($vluchtnummer, $bestemming, $max_aantal, $max_gewicht_pp, $max_totaalgewicht, $maatschappijcode, $vertrektijd = null, $gatecode = null)
{
    $db = maakVerbinding();

    try
    {
        $sql = "INSERT INTO Vlucht (vluchtnummer, bestemming, gatecode, max_aantal, max_gewicht_pp, max_totaalgewicht, vertrektijd, maatschappijcode)
                VALUES (:vluchtnummer, :bestemming, :gatecode, :max_aantal, :max_gewicht_pp, :max_totaalgewicht, :vertrektijd, :maatschappijcode)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'vluchtnummer' => $vluchtnummer,
            'bestemming' => $bestemming,
            'gatecode' => $gatecode,
            'max_aantal' => $max_aantal,
            'max_gewicht_pp' => $max_gewicht_pp,
            'max_totaalgewicht' => $max_totaalgewicht,
            'vertrektijd' => $vertrektijd,
            'maatschappijcode' => $maatschappijcode,
        ]);

        return true;
    }
    catch (Exception $e)
    {
        echo 'Error: ' . $e->getMessage();
        return false;
    }
}

function kofferRegistratie($passagiernummer, $gewicht)
{
    // insert into bagageobject
    // passagiernummer, objectvolgnummer op basis van of er eentje is of niet
    // gewicht mag samen niet meer dan max_gewicht_pp in Vlucht

    $db = maakVerbinding();

    try
    {
        $objectvolgnummer = bepaalObjectVolgNummer($passagiernummer);
        if (bepaalMaxGewicht($passagiernummer, $gewicht))
        {
            $sql = "INSERT INTO BagageObject (passagiernummer, objectvolgnummer, gewicht) 
                    VALUES (:passagiernummer, :objectvolgnummer, :gewicht)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'passagiernummer' => $passagiernummer,
                'objectvolgnummer' => $objectvolgnummer,
                'gewicht' => $gewicht,
            ]);

            return true;
        }
        else 
        {
            return false;
        }

    }
    catch (Exception $e)
    {
        echo 'Error: ' . $e->getMessage();
        return false;
    }
}

function bepaalObjectVolgNummer($passagiernummer)
{
    $extraWhere = "passagiernummer = :passagiernummer";
    $params = array('passagiernummer' => $passagiernummer);
    $query = krijgTabel("BagageObject", $extraWhere, null, "MAX(objectvolgnummer) AS maxObjectVolgnummer", $params);

    if (empty($query)) {
        return 0;
    }

    // echo $query[0]['maxObjectVolgnummer'] + 1;
    return $query[0]['maxObjectVolgnummer'] + 1;
}

function bepaalMaxGewicht($passagiernummer, $gewicht)
{
    $extraWhereBagage = "passagiernummer = :passagiernummer";
    $paramsBagage = array('passagiernummer' => $passagiernummer);
    $queryBagage = krijgTabel("BagageObject", $extraWhereBagage, null, "SUM(gewicht) AS totaalGewicht", $paramsBagage);


    $extraWherePV = 'P.passagiernummer = :passagiernummer';
    $paramsPV = array('passagiernummer' => $passagiernummer);
    $queryPV = krijgTabel('Passagier P LEFT JOIN Vlucht V ON V.vluchtnummer = P.vluchtnummer', $extraWherePV, null, 'P.vluchtnummer, P.passagiernummer, V.max_gewicht_pp', $paramsPV);

    $max_gewicht_pp = 0;
    foreach ($queryPV as $rij)
    {
        $max_gewicht_pp = $rij['max_gewicht_pp'];
    }
    
    $totaalGewichtPassagier = 0;
    foreach ($queryBagage as $rij)
    {
        $totaalGewichtPassagier = $rij['totaalGewicht'];
    }


    if ($totaalGewichtPassagier + $gewicht > $max_gewicht_pp)
    {
        // echo 'te zwaar';
        return false; // Dit gewicht is te zwaar en mag er niet bij
    }
    else 
    {
        // echo 'niet te zwaar';
        return true; // Dit gewicht is niet te zwaar en mag er bij
    }

}

function wijzigPassagierGegevens($passagiernummer, $vluchtnummer, $stoel)
{
    $db = maakVerbinding();

    try
    {
        if (!maxAantalBereikt($vluchtnummer, $passagiernummer))
        {
            // echo 'boombaclat';
            return false;
        }
        else
        {
            $sql = "UPDATE Passagier
                    SET stoel = :stoel, vluchtnummer = :vluchtnummer
                    WHERE passagiernummer = :passagiernummer";

            $stmt = $db->prepare($sql);

            if (!$stmt) {
                return false;
            }

            // echo "Uppdate passagiernummer $passagiernummer met vluchtnummer $vluchtnummer en stoel $stoel.";

            $stmt->execute([
                'vluchtnummer' => $vluchtnummer,
                'stoel' => $stoel,
                'passagiernummer' => $passagiernummer
            ]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                // echo "No rows updated.";
                return false;
            }
        }
    }
    catch (Exception $e)
    {
        // echo 'Error: ' . $e->getMessage();
        return false;
    }
}

?>