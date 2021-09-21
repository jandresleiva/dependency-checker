<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

require_once "vendor/autoload.php";

$isDevMode = true; //Se podría cargar de una variable de entorno
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src/Entities"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

$conn = require('migrations-db.php');

$entityManager = EntityManager::create($conn, $config);
