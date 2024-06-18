<?php
include_once("db_connectie.php");
include_once("crud.php");
include_once("functies.php");
?>

<?=maakHeader();?>

<main>
    <?=maakAlleVluchtenMenu();?>

    <?=maakAlleVluchten(); // TODO sorteren op vertrektijd met klikken ?> 
</main>

<?=maakFooter();?>