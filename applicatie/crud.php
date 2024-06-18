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

    if (!empty($extraWhere)) 
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

    return $query->fetchAll(PDO::FETCH_ASSOC);
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

function inloggen($passagiernummer, $wachtwoord)
{
    $db = maakVerbinding();
    $sql = "SELECT * FROM Passagier WHERE passagiernummer = :passagiernummer";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':passagiernummer', $passagiernummer);
    $stmt->execute();
    $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC); 

    if ($gebruiker) // kijkt of gebruiker bestaat.
    {
        echo "gebruiker if statement";
        echo $gebruiker['wachtwoord'];
        echo $wachtwoord;
        if ($wachtwoord == $gebruiker['wachtwoord'])
        {
            $check = true;
            echo "Passagier geverifieerd";
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

?>