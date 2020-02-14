<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use web\Security;


require '../vendor/autoload.php';
require '../classes/Security.php';

// Create container
$container = new Container();
AppFactory::setContainer($container);

// Add Twig helper into the container
$container->set('view', function () {
    return new Twig('../template');
});

// Add Medoo-based database into the container
$container->set('db', function () {
    include_once('../config/database.php');
    return new \Medoo\Medoo($database_config);
});

// Add session helper into the container
$container->set('session', function () {
    return new \SlimSession\Helper();
});

// Create the app
$app = AppFactory::create();

// Add the session middleware
$app->add(new \Slim\Middleware\Session([
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

// Add twig Twig-View middleware
$app->add(TwigMiddleware::createFromContainer($app));

// the homepage for user to provide his/her name
$app->get('/', function ($request, $response, $args) {
  // get the session object from the container
  $session = $this->get('session');

  // remove the session named as 'user'
  $session->delete("user");

  // display the page by using template
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'index.html',
    'user' => $session->get('user', false),
  ]);
});

// here, even though the path pattern equals to the previous
// the method is different.
$app->post('/', function ($request, $response, $args) {
  // get the session object from the container
  $session = $this->get('session');

  // read the user's name.
  if (!$session->exists("user")) {
    $form_data = $request->getParsedBody();
    $user = $form_data["name"];
    // set a new session named 'user' with value of the user's name.
    $session->set('user', $user);
  }

  return $response
    ->withHeader('Location', '/order/all')
    ->withStatus(302);
});

// See all orders
$app->get('/order/all', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  // retrieve all orders
  // see https://medoo.in/api/select
  $orders = $db->select(
    "order",
    [
      "[><]cake" => ["cake" => "code"]
    ],
    [
      "order.no",
      "order.customer_name",
      "cake.code",
      "cake.name",
      "order.quantity",
      "cake.price",
      "order.placed_at"
    ],
    [
      "order.canceled" => 0,
      "ORDER" => ["order.placed_at" => "ASC"]
    ]
  );

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'order-all.html',
    'user' => $session->get('user', false),
    'orders' => $orders
  ]);
});

// Create a new order
$app->get('/order/new', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $cakes = $db->select(
    "cake",
    "*",
    [
      "ORDER" => ["cake.code" => "ASC"]
    ]
  );

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'order-new.html',
    'user' => $session->get('user', false),
    'cakes' => $cakes
  ]);
});

// Save the new order
$app->post('/order/new', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $form_data = $request->getParsedBody();

  // for order no generator, please see web\Security.php
  $order = [
    'no' => Security::random(20),
    'customer_name' => $form_data["customer"],
    'address' => $form_data["address"],
    'phone' => $form_data["phone"],
    'cake' => $form_data["cake"],
    'quantity' => $form_data["quantity"],
    'placed_at' => date("Y-m-d H:i:s")
  ];

  // save it permanently
  $db->insert('order', $order);

  return $response
    ->withHeader('Location', '/order/all')
    ->withStatus(302);
});

// Cancel an order
$app->get('/order/cancel/{no}', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = 
    $db->has('order', [
      'no' => $args['no'],
      'customer_name' => $session->get('user', false)
    ]);

  if($exists){
    $db->update('order', 
      ['canceled' => true],
      ['no' => $args['no']]
    );
  }

  return $response
    ->withHeader('Location', '/order/all')
    ->withStatus(302);
});

// View an order in detail
$app->post('/order/detail/{no}', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  // retrieve an order with specific number
  // see https://medoo.in/api/select
  $order = $db->select(
    "order",
    [
      "[><]cake" => ["cake" => "code"]
    ],
    [
      "order.no",
      "order.customer_name",
      "order.address",
      "order.phone",
      "cake.code",
      "cake.name",
      "order.quantity",
      "cake.price",
      "order.placed_at"
    ],
    [
      'order.no' => $args['no'],
      'LIMIT' => 1
    ]
  );

  if(!$order){
    return $response
      ->withHeader('Location', '/order/all')
      ->withStatus(302);
  }

  $order = $order[0];
  
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'order-detail.html',
    'user' => $session->get('user', false),
    'order' => $order
  ]);
});
  
// Run the app
$app->run();
