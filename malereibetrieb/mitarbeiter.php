<?php
if(count($_POST)==0) {
    $_POST["NN_MA"] = "";
    $_POST["VN_MA"] = "";
    $_POST["NN_KD"] = "";
    $_POST["VN_KD"] = "";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiter</title>
</head>
<body>
    <form method="post">
        <fieldset>
            <legend>Mitarbeiter</legend>
            <label>
                Nachname:
                <input type="text" name="NN_MA" value="<?php echo($_POST["NN_MA"]); ?>">
            </label>
            <label>
                Vorname:
                <input type="text" name="VN_MA" value="<?php echo($_POST["VN_MA"]); ?>">
            </label>
            <input type="submit" value="suchen">
        </fieldset>
        <fieldset>
            <legend>Kunde</legend>
            <label>
                Nachname:
                <input type="text" name="NN_KD" value="<?php echo($_POST["NN_KD"]); ?>">
            </label>
            <label>
                Vorname:
                <input type="text" name="VN_KD" value="<?php echo($_POST["VN_KD"]); ?>">
            </label>
            <input type="submit" value="suchen">
        </fieldset>
    </form>

    <?php
    $where = "";
    $filter = [];

    // Filterung der Mitarbeiter
    if (count($_POST) > 0){

        if (strlen($_POST["NN_MA"] > 0)){
            $filter[] = "tbl_mitarbeiter.NachN_Mitarbeiter = '" . $_POST["NN_MA"] . "'" ;
        }

        if (strlen($_POST["VN_MA"] > 0)){
            $filter[] = "tbl_mitarbeiter.VorN_Mitarbeiter = '" . $_POST["VN_MA"] . "'";
        }

        if(count($filter) > 0) {
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

    // Abfrage der Mitarbeiterliste
    $sql = "SELECT * FROM tbl_mitarbeiter " . $where . " Order by NachN_Mitarbeiter ASC, VorN_Mitarbeiter ASC";
    $mitarbeiterliste = $conn->query($sql);

    // Ausgabe der Mitarbeiterliste
    if ($mitarbeiterliste->num_rows > 0){
        echo("<ol>");
        while($mitarbeiter = $mitarbeiterliste->fetch_object()){
            echo("<li>Nachname: " . $mitarbeiter->NachN_Mitarbeiter . " Vorname: " . $mitarbeiter->VorN_Mitarbeiter . "</li>");

            $where = "";
            $filter = ["tbl_auftragsliste.ID_Mitarbeiter=" . $mitarbeiter->ID];
            if(count($_POST)>0) {
                if(strlen($_POST["VN_KD"])>0) {
                    $filter[] = "tbl_kunde.VorN_Kunde='" . $_POST["VN_KD"] . "'";
                }
                if(strlen($_POST["NN_KD"])>0) {
                    $filter[] = "tbl_kunde.NachN_Kunde='" . $_POST["NN_KD"] . "'";
                }
            }

            $sql = "SELECT  tbl_auftragsliste.Arbeitsbeginn, 
                            tbl_auftragsliste.Arbeitsende, 
                            tbl_kunde.VorN_Kunde, 
                            tbl_kunde.NachN_Kunde 
                    FROM tbl_auftragsliste LEFT JOIN tbl_kunde ON tbl_kunde.ID=tbl_auftragsliste.ID_Kunde  
                    WHERE (" . implode(" AND ",$filter) . ")
                    ORDER BY tbl_auftragsliste.Arbeitsbeginn ASC";

            $AuftragslisteZeit = $conn->query($sql);

            // Ausgabe der Aufträge
            if ($AuftragslisteZeit->num_rows > 0){
                echo("<ul>");
                while($Auftrag = $AuftragslisteZeit->fetch_object()){
                    echo("<li>Arbeitsbeginn: " . $Auftrag->Arbeitsbeginn . " Arbeitsende: " . $Auftrag->Arbeitsende . " Kunde: " . $Auftrag->VorN_Kunde . " " . $Auftrag->NachN_Kunde);
                }
                echo("</ul>");

            }
        }
        echo("</ol>");
    } else {
        echo("Keine Mitarbeiter gefunden!");
    }

    // Verbindung schließen
    $conn->close();

    ?>
</body>
</html>