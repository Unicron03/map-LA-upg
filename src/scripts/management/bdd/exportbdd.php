<?php
try {
    $con = new PDO("mysql:host=db;dbname=map-la", "admin", "root", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Tables à exporter
    $tables = ["typemarker", "marker", "users", "userdata"];
    $sqlDump = '';

    foreach ($tables as $table) {
        // Récupération de la requête de création
        $stmt = $con->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $create = $row[1];

        $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n\n$create;\n\n";

        // Données de la table
        $resData = $con->query("SELECT * FROM `$table`");
        $columns = [];

        // Récupération des noms de colonnes
        for ($i = 0; $i < $resData->columnCount(); $i++) {
            $meta = $resData->getColumnMeta($i);
            $columns[] = $meta['name'];
        }

        while ($rowData = $resData->fetch(PDO::FETCH_NUM)) {
            $values = [];
            foreach ($rowData as $idx => $val) {
                $columnName = strtolower($columns[$idx]);

                if (is_null($val)) {
                    $values[] = "NULL";
                } elseif ($columnName === 'image') {
                    $values[] = "0x" . bin2hex($val);
                } else {
                    $values[] = '"' . addslashes($val) . '"';
                }
            }
            $sqlDump .= "INSERT INTO `$table` VALUES(" . implode(",", $values) . ");\n";
        }

        $sqlDump .= "\n\n";
    }

    file_put_contents('../../../backup/backup.sql', $sqlDump);
    echo "Exportation réussie";
} catch (PDOException $e) {
    echo "Erreur PDO : " . $e->getMessage();
}
?>