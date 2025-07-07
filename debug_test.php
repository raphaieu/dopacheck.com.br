<?php
echo "Xdebug Configurações:\n";
echo "xdebug.mode: " . ini_get('xdebug.mode') . "\n";
echo "xdebug.client_host: " . ini_get('xdebug.client_host') . "\n";
echo "xdebug.client_port: " . ini_get('xdebug.client_port') . "\n";
echo "xdebug.start_with_request: " . ini_get('xdebug.start_with_request') . "\n";
echo "xdebug.idekey: " . ini_get('xdebug.idekey') . "\n";

// Teste simples
$test = "Hello Debug!";
var_dump($test);
?> 