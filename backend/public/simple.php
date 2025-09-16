<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $response = $app->handle(
        $request = \Illuminate\Http\Request::createFromGlobals()
    );
    
    $response->send();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
}
?>


