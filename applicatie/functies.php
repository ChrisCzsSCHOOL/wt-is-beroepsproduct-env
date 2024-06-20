<?php 
session_start();
ob_start();

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
                    <div class="menuitem"><a href="allevluchten.php">Vluchtenoverzicht</a></div>
                    <div class="menuitem"><a href="incheckpagina.php">Inchecken</a></div>
                    <div class="menuitem"><a href="inlogpagina.php">Inloggen</a></div>
                    <div class="menuitem"><a href="medewerkeroverzicht.php">Medewerker</a></div>
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
                <a href="inlogpagina.php">
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

include_once("functies/functies_vluchten.php");

// ----------------------------- einde allevluchten.php ----------------------------- 


// ----------------------------- begin inlogpagina.php ----------------------------- 

include_once("functies/functies_inloggen.php");

// ----------------------------- einde inlogpagina.php ----------------------------- 


// ----------------------------- begin incheckpagina.php ---------------------------

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
            // echo 'great succes';
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
            <form method="post" action="">
                <div class="formitem">
                    <label for="passagiernummer">Maximaal aantal personen</label>
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

// ----------------------------- einde incheckpagina.php ---------------------------

include_once("functies/functies_medewerker.php");



?>