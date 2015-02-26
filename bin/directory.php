#!/usr/bin/php
<?php

use Aura\Di\ContainerBuilder;

# Starts the project kernel, without auto-resolving.
$path = dirname(__DIR__);
require "{$path}/vendor/autoload.php";

$kernel = (new \Aura\Project_Kernel\Factory())->newKernel(
    $path,
    'Friendica\Directory\Kernel\ReactApplication',
    ContainerBuilder::DISABLE_AUTO_RESOLVE
);
$kernel();
