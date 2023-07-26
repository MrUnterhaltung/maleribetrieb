<?php
if (count($_POST)==0){
    $_POST["NN_K"] = "";
    $_POST["VN_K"] = "";
}

?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="includes/style.css">
    <title>Kunde</title>
</head>
<body>
    <form method="post">
        <fieldset>
            <legend>Kunde</legend>
            <label>
                Nachname:
                <input type="text" name="NN_K" value="<?php echo($_POST["NN_K"]); ?>">
            </label>
            <label>
                Vorname:
                <input type="text" name="VN_K" value="<?php echo($_POST["VN_K"]); ?>">
            </label>
            <input type="submit" value="suchen">
        </fieldset>
    </form>

    <?php
        $where = "";
        $filter = [];

        // Check ob der Filter leer ist
        if (count($_POST)> 0){
            if (strlen($_POST["NN_K"])>0){
                $filter[] = "tbl_kunde.NachN_Kunde = '" . $_POST["NN_K"] . "'";
            }

            if (strlen($_POST["VN_K"])>0){
                $filter[] = "tbl_kunde.VorN_Kunde = '" . $_POST["VN_K"] . "'";
            }

            if (count($filter) > 0) {
                $where = "WHERE(" . implode(" AND ",$filter) . ")";
            }
        }

        // Verbindung zur Datenbank herstellen
        require_once 'includes/connection.php';
        $conn = new mysqli(DB['host'], DB['user'], DB['pwd'], DB['name']);

        // Überprüfe auf Verbindungsfehler
        if ($conn->connect_error) {
            die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
        }

        $sql = "SELECT  * From tbl_kunde " . $where . " ORDER BY NachN_Kunde";
        $kundenliste = $conn->query($sql);

        echo("<ol>");
        while($kunde = $kundenliste->fetch_object()){
            echo("<li>Kunde: " . $kunde->NachN_Kunde . " " . $kunde->VorN_Kunde . "
                <br><ul> Adresse: " . $kunde->Adresse . " " . $kunde->Ort . "</ul>
                <ul> Email: " . $kunde->Email . " Telefonnummer: " . $kunde->Telefonnummer . "</ul>");

            $sql = "SELECT * FROM tbl_Auftragsliste WHERE " . $kunde->ID . " = tbl_auftragsliste.ID_Kunde ORDER BY Arbeitsbeginn ASC";
            $auftragsliste = $conn->query($sql);

            $insgesammteStunden = 0;
            $kosten = 0;

            while($auftrag = $auftragsliste->fetch_object()){

                // DateTime-Objekte erstellen
                $beginn_dt = new DateTime($auftrag->Arbeitsbeginn);
                $ende_dt = new DateTime($auftrag->Arbeitsende);

                // Stundendifferenz berechnen
                $diff = $ende_dt->diff($beginn_dt);

                // Stundendifferenz in Dezimal umwandeln
                $stundendifferenz = $diff->h + $diff->i / 60;

                echo("<ul>Von: " . $auftrag->Arbeitsbeginn . " Bis: " . $auftrag->Arbeitsende . "(Stunden: " . $stundendifferenz . ")" . "</ul>");

                // Rechne die Gesammten Stunden aus
                $insgesammteStunden = $insgesammteStunden + $stundendifferenz;

                // Kosten für die Gesammten Stunden
                $kosten = $insgesammteStunden * 60;
            }
            // Gibt die Gesammten Stunden aller Aufträge pro Kunde aus
            if ($kosten > 0){
                echo ("<ul> Stunden: " . $insgesammteStunden . " Kosten: " . $kosten . "</ul>");
            } else {
                echo ("<ul>Keine Aufträge vorhanden!</ul>");
            }
            
            echo("</li>");  
        }
        echo("</ol>");
    ?>

    
</body>
</html>