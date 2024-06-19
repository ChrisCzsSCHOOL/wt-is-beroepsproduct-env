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

function bepaalHoogstePassagiernummer()
{
    $db = maakVerbinding();
    $sql = krijgTabel("Passagier", '', null, "MAX(passagiernummer) AS hoogstePassagiernummer");

    // print_r($sql);
    // echo $sql[0]['hoogstePassagiernummer'] + 1;
    $hoogsteNummer = $sql[0]['hoogstePassagiernummer'] + 1;
    return $hoogsteNummer;
}

function bepaalHoogsteVluchtnummer()
{
    $db = maakVerbinding();
    $sql = krijgTabel("Vlucht", '', null, "MAX(vluchtnummer) AS hoogsteVluchtnummer");

    // print_r($sql);
    // echo $sql[0]['hoogsteVluchtnummer'] + 1;
    $hoogsteNummer = $sql[0]['hoogsteVluchtnummer'] + 1;
    return $hoogsteNummer;
}

function registreren($vluchtnummer, $passagiernummer, $wachtwoord, $naam, $gender = 'x')
{
    $db = maakVerbinding();

    // echo $vluchtnummer, $passagiernummer, $wachtwoord, $naam, $gender;
    try 
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
    catch (Exception $e)
    {
        echo $e->getMessage();
        return false;
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

?>