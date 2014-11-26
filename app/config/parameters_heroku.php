<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

if(!function_exists('getFirstEnvVarFromArray')) {
    /**
     * Process a list of possible environment variables, returning the first one.
     * @param $envVarList
     * @return null|string
     */
    function getFirstEnvVarFromArray($envVarList) {
        foreach ($envVarList as $envVar) {
            if($value = getenv($envVar)) {
                return $value;
            }
        }
        return null;
    }
}


if(!function_exists('populateDatabaseParameters')) {
    /**
     * Attempt to parse a DSN string into standard parameters
     * @param $dsn
     */
    function populateDatabaseParameters(ContainerBuilder $container, $dsn) {
        $parameters = parse_url($dsn);
        $container->setParameter('database_host', $parameters['host']);
        $container->setParameter('database_user', $parameters['user']);
        $container->setParameter('database_password', $parameters['pass']);
        $container->setParameter('database_name', substr($parameters['path'],1));

        if(array_key_exists('port', $parameters)) {
            $container->setParameter('database_port', $parameters['port']);
        }

        $driver = null;
        switch($parameters['scheme']) {
            case 'mysql':
                $driver = 'pdo_mysql';
        }

        if(null !== $driver) {
            $container->setParameter('database_driver', $driver);
        }
    }
}

if(getenv('SYMFONY_ON_HEROKU') || getenv('DYNO')) {
    // setup log output
    $container->setParameter('log_output', 'php://stderr');

    // configure database
    $dsn = getFirstEnvVarFromArray(
        array(
            'SYMFONY_DATABASE_DSN',
            'CLEARDB_DATABASE_URL',
        )
    );
    if((null !== $dsn) && (false !== filter_var($dsn, FILTER_VALIDATE_URL))) {
        populateDatabaseParameters($container, $dsn);
    }
}
