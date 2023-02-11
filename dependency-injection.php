<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$containerBuilder = new ContainerBuilder();
$containerBuilder->register('FiniteAutomatonService', \Services\FiniteAutomatonService::class);
$containerBuilder->register('GrammarMapper', \Services\GrammarMapper::class)->addArgument($containerBuilder->get("FiniteAutomatonService"));
$containerBuilder->register('InputFileService', \Services\InputFileService::class);
$containerBuilder->register('PrintService', \Services\PrintService::class);
$containerBuilder->register('Configuration', \Configuration\Configuration::class);