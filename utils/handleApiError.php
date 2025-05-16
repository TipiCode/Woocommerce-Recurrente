<?php 

class HandleApiError {

    function __contructor() {    }



    function reportAuroraIssue(
        Exception $e, 
        //string $appId, 
        string $friendlyMsg = "Oops! Ocurrió un error", 
        string $projectUrl = "https://github.com/TipiCode/Woocommerce-Recurrente", 
        string $version = "v2.1.1"
        )
    {
        $trace = $e->getTrace();

        // Si hay un stack trace, tomamos la primera entrada para el archivo y línea
        $file = $e->getFile();
        $line = $e->getLine();

        $payload = [
            'line' => strval($line),
            'file' => basename($file),
            'friendlyMsg' => $friendlyMsg,
            'exception' => $e->getMessage(),
            'url' => $projectUrl,
            'version' => $version
        ];

        error_log("Payload: " . json_encode($payload));
        $ch = curl_init('https://aurora.codingtipi.com/support/v1/issues');
        $appId = "725fc065-3b13-47e5-8d23-39d2547a967";
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-App-Id: ' . $appId
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log("Aurora API request error: " . curl_error($ch));
        } elseif ($httpCode !== 201) {
            error_log("Aurora API responded with status $httpCode: $response");
        }

        curl_close($ch);
    }
    }

?>