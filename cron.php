<?php

$xml=simplexml_load_file("protected/application.xml") or die("Error: Cannot create object");
echo $xml->parameters[0]->parameters;
$host = 'localhost';
$user = 'root';
$password = '1';
$db = 'stisipolrh';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $q = $conn->query ("SELECT value FROM setting WHERE setting_id=1 OR setting_id=2");
    $q->setFetchMode(PDO::FETCH_ASSOC);
    $variable = $q->fetchAll();
    
    $ta=$variable[0]['value'];
    $idsmt = $variable[1]['value'];
    
    $mhs = $conn->query ("SELECT vdm.no_formulir,vdm.nim,vdm.nama_mhs,vdm.tahun_masuk,vdm.k_status,vdm.idkelas FROM v_datamhs vdm WHERE vdm.nim NOT IN (SELECT nim FROM dulang WHERE idsmt=$idsmt AND tahun=$ta) AND vdm.k_status != 'K' AND vdm.k_status!='L' AND vdm.k_status!='D' ORDER BY vdm.nama_mhs ASC");
    $mhs->setFetchMode(PDO::FETCH_ASSOC);
    $datamhs = $mhs->fetchAll();
    
    while (list($k,$v)=each($datamhs)) {
        
    }
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
?>