<?php
$host     = 'localhost';
$dbname   = 'map-la';
$username = 'root';
$password = '';
$con = new mysqli($host, $username, $password, $dbname);
if ($con->connect_error) {
    die("Échec de la connexion : " . $con->connect_error);
}

// A Supprimer
$tables = array();
$result = mysqli_query($con, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Pour pas qu'il y est de problème de clé étrangère je l'ai mis en dur
$tables = ["typemarker","marker","users","userdata"];

$sqlDump = '';

foreach ($tables as $table) {
    
    $create = $con->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n\n$create;\n\n";

    
    $resData    = $con->query("SELECT * FROM `$table`");
    $metaFields = $resData->fetch_fields(); 
    while ($rowData = $resData->fetch_row()) {
        $values = [];
        foreach ($metaFields as $idx => $meta) {
            $val = $rowData[$idx];
            
            if (is_null($val)) {
                $values[] = "NULL";

            // BLOB si le nom est "image"
            } elseif (strtolower($meta->name) === 'image') {
                $values[] = "0x" . bin2hex($val);


            
            } else {
                $values[] = '"' . addslashes($val) . '"';
            }
        }
        $sqlDump .= "INSERT INTO `$table` VALUES(" . implode(",", $values) . ");\n";
    }
    $sqlDump .= "\n\n";
}


file_put_contents('../../../backup.sql', $sqlDump);
echo "Exportation réussie";
?>