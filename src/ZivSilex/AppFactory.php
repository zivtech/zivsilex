<?php
/**
 * @file
 * @description A class to encapsulate PeachFuzz routes.
 */

namespace ZivSilex;
use \Silex\Application;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ZivSilex\ZivSilexClient;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

abstract class AppFactory {
  /**
   * Factory function for getting our Silex Application.
   *
   * @param array $config
   *   An array of configuration.
   *
   *  If NULL the config.php file will be loaded from the root
   *  of this repoistory.
   */
  static public function instantiateApp($config = NULL) {
    if (!is_null($config) && !is_array($config)) {
      throw new \Exception('Invalid configuration specified, expected an array.');
    }
    if (is_null($config)) {
      $path = \realpath(__DIR__ . '/../../config.php');
      $config = self::getConfig($path);
    }
    $app = new Application();
    $app['config'] = $config;
    // Populate the app object with relevant configuration.
    foreach ($config as $name => $value) {
      $app[$name] = $value;
    }

    $app['ziv_client'] = function() use($app) {
      return new ZivSilexClient($app);
    };

    $app->register(new \Moust\Silex\Provider\CacheServiceProvider(), array(
      'cache.options' => array(
        'driver' => 'file',
        'cache_dir' => $app['cache.dir'],
      ),
    ));

    $app->register(new \Silex\Provider\MonologServiceProvider(), array(
        'monolog.logfile' => $app['monolog.logfile'],
        'monolog.level' => Logger::ERROR,
        'monolog.name' => 'peach_fuzz',
        'monolog.use_error_handler' => TRUE,
    ));

    $app->register(new \Silex\Provider\TwigServiceProvider(), array(
      'twig.path' => $app['twig.dir'],
    ));

    AppFactory::addRoutes($app);

    return $app;
  }

  static private function addRoutes(&$app) {
    $app->post('/api/authenticate', function(Request $request) use($app) {
      return $app['ziv_client']->authenticateUser($request);
    });

    $app->get('/api/basic', function(Request $request) use($app) {
      $app['ziv_client']->validateJWTFromRequest($request);
      return $app['ziv_client']->basicGet($request);
    });

    $app->get('/api/dogs', function(Request $request) use($app) {
      $app['ziv_client']->validateJWTFromRequest($request);
      $dog_index = $request->query->get('dog_index');
      return $app['ziv_client']->getDogs($dog_index);
    });

    return $app;
  }

  /**
   * A method to retrieve a configuration array defined in a file.
   *
   * @param string $path
   *   The path to the config.php file that defines a $conf variable.
   */
  static public function getConfig($path) {
    if (!is_file($path)) {
      throw new \Exception(sprintf('Configuration file missing: %s', $path));
    }
    require $path;
    if (!empty($config) && is_array($config)) {
      return $config;
    }
    else {
      throw new \Exception(sprintf('Config file "%s" found but no config variable is defined.', $path));
    }
  }
}
