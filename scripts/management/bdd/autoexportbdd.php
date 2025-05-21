<?php
$exportScript = 'exportbdd.php';
$lastExportFile = '../../../last_export.txt';
$interval = 7 * 24 * 60 * 60;

if (file_exists($lastExportFile)) {
    $lastExport = file_get_contents($lastExportFile);
    $lastExportTime = strtotime($lastExport);

    if (time() - $lastExportTime >= $interval) {
        include($exportScript);
        file_put_contents($lastExportFile, date(format: 'Y-m-d H:i:s')); // Enregistre la date actuelle
    } else {
        echo "Dernier export effectué le : " . $lastExport . ". Prochain export dans " . round(($interval - (time() - $lastExportTime)) / 3600, 1) . " heures.";
    }
} else {
    include($exportScript);
    file_put_contents($lastExportFile, date('Y-m-d H:i:s'));
}
?>
