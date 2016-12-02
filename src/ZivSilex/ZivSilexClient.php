<?php

namespace ZivSilex;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Firebase\JWT\JWT;
use GuzzleHttp\Message\MessageFactory;

class ZivSilexClient {
  private $logger;
  private $app;

  public function __construct($app) {
    $this->logger = $app['monolog'];
    $this->app = $app;
  }

  public function basicGet($request) {
    return new Response('Success!', 200);
  }

  public function getDogs($index) {
    $dogs = array(
      '1' => 'finn',
      '2' => 'marcy',
      '3' => 'zappa',
    );
    return new Response(json_encode($dogs[$index]), 200);
  }

  public function authenticateUser($request) {
    $data = json_decode($request->getContent(), true);

    if (!empty($data['email'])) {
      $jwt = $this->createJWTAuth($data);
      return new Response($jwt, 200);
    }

    // Pause for 5 seconds, then respond with 403 error.
    return new Response('Invalid Password or Email', 403);
  }

  public function createJWTAuth($data) {
    // @TODO add more data about the user here in the token array.
    $token = array(
      'email' => $data['email'],
      'expires' => strtotime($this->app['token_authentication.expires']),
    );
    $jwt = JWT::encode($token, $this->app['jwt_key']);
    return $jwt;
  }

  /**
   * Test JWT token. If invalid, throw a Silex 403 exception.
   */
   public function validateJWTFromRequest($request) {
     try {
       $jwt = $request->headers->get('Authorization');
       $jwt = trim(substr($jwt, 6));
       if (!$jwt) {
         throw new \Exception('Missing Authorization header');
       }
       $token = JWT::decode($jwt, $this->app['jwt_key'], array('HS256'));
       if (time() > $token->expires) {
         throw new \Exception('Expired token. Please log in again');
       }
       return FALSE;
     }
     catch (\UnexpectedValueException $e) {
       $this->logger->error('Error while decoding JWT: ' . $e->getMessage() . PHP_EOL . $jwt);
       throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Invalid HTTP_AUTH_TOKEN header', null, 403);
     }
     catch (\Exception $e) {
       $message = $e->getMessage();
       $this->logger->error('Error while validating JWT: ' . $message . PHP_EOL . $jwt);
       throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException($message, null, 403);
     }
   }
}
