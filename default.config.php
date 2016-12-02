<?php

$config['monolog.logfile'] = __DIR__ . '/../log/peach_fuzz.log';
$config['cache.dir'] = __DIR__ . './tmp';
$config['twig.dir'] = __DIR__ . './twig';
$config['wsdl-templates.directory'] = __DIR__ . '/wsdl-templates/';
$config['wsdl.directory'] = __DIR__ . '/wsdl/';

$config['jwt_key'] = 'RacsrcWRfJ0TMh6y5Bhx7m';
$config['token_authentication.expires'] = '+6 days';

$config['swiftmailer.options'] = array(
    'host' => 'host',
    'port' => '25',
    'username' => 'username',
    'password' => 'password',
    'encryption' => null,
    'auth_mode' => null,
);

if (TRUE) {
  $config['debug'] = TRUE;
  $config['swiftmailer.delivery_addresses'] = 'dan@zivtech.com';
}
