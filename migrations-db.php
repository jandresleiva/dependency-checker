<?php
const DATABASE_CONFIG_PATH = "db.json";

$dbSecrets = file_get_contents(__DIR__ . "/Secrets/" . DATABASE_CONFIG_PATH);

if ($dbSecrets === false) {
    throw new Exception("Invalid or empty database configuration. Verify you have Secrets/db.json");
}

$dbConfig = json_decode($dbSecrets, true);

if ($dbConfig === null) {
    throw new Exception("Invalid or empty database configuration. Verify your Secrets/db.json have the right JSON schema");
}

return $dbConfig;