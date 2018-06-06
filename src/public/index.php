<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "";
$config['db']['dbname'] = "homewater";

function DBConnection()
{	
	return new PDO('mysql:dbhost=localhost;dbname=homewater', 'root', '');
}

$app = new \Slim\App(["settings" => $config]);
	$container = $app->getContainer();
	$container['view'] = new \Slim\Views\PhpRenderer("../templates/");
	$container['logger'] = function($c) {
	$logger = new \Monolog\Logger('my_logger');
				$file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
				$logger->pushHandler($file_handler);
				return $logger;
	};

	$container['db'] = function ($c) {
		$db = $c['settings']['db'];
		$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
		$db['user'], $db['pass']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	};
			
	//setting tampilan awal
	$app->get('/', function (Request $request, Response $response) {
		$this->logger->addInfo("HomeWater Application");
		$response = $this->view->render($response, "login.phtml");
		return $response;
	});
		
	//home aplikasi admin
	$app->get("/home_admin", function (Request $request, Response $response, $args) {
		$response = $this->view->render($response, "home_admin.phtml");
		return $response;
	});
		
	//home aplikasi user
	$app->get("/home_user", function (Request $request, Response $response, $args) {
		$response = $this->view->render($response, "home_user.phtml");
		return $response;
	});
	
	//login
	$app->post('/login', function ($request, $response) {
		$username = $request->getParsedBody()['username'];
		$password = $request->getParsedBody()['password'];
		$ps = (DBConnection()->query("select password from user where username = '".$username."' LIMIT 1")->fetch());
		$ut = (DBConnection()->query("select usertype from user where username = '".$username."' LIMIT 1")->fetch());
		$db_ps = $ps['password'];
		$db_ut = $ut['usertype'];
		if($password === $db_ps && $db_ut === "admin")
		{
			$_SESSION['isLoggedIn'] = 'admin';
			session_regenerate_id();
			$response = $response->withRedirect("/home_admin");
			return $response;
		} else if
		(
			$password === $db_ps && $db_ut === "user"){
			$_SESSION['isLoggedIn'] = 'user';
			$_SESSION['username'] = $username;
			session_regenerate_id();
			$response = $response->withRedirect("/home_user");
			return $response;
		} else 
		{
			$message = "Username atau Password Anda Salah !";
			echo "<script type='text/javascript'>alert('$message');</script>";
			$response = $this->view->render($response, "login.phtml");
			return $response;
		}
		});

	//logout
	$app->get('/logout', function ($request, $response, $args) {
			unset($_SESSION['isLoggedIn']);
			unset($_SESSION['username']);
			session_regenerate_id();
			$response = $response->withRedirect("/");
			return $response;
	});
	
	//tabel user
	$app->get('/user', function (Request $request, Response $response) {
			$this->logger->addInfo("user list");
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUser();
			$response = $this->view->render($response, "user.phtml", ["user" => $user, "router" => $this->router]);
			return $response;
	});
		
	//insert user
	$app->get('/user/new', function (Request $request, Response $response) {
			$user_mapper = new UserMapper($this->db);
			$user = $user_mapper->getUser();
			$response = $this->view->render($response, "useradd.phtml", ["user" => $user]);
			return $response;
	});
	$app->post('/user/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$user_data = [];
			$user_data['username'] = filter_var($data['username'], FILTER_SANITIZE_STRING);
			$user_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);
			$user_data['nama'] 	   = filter_var($data['nama'], FILTER_SANITIZE_STRING);
			$user_data['usertype'] 	   = filter_var($data['usertype'], FILTER_SANITIZE_STRING);
			$user = new UserEntity($user_data);
			$user_mapper = new UserMapper($this->db);
			$user_mapper->save($user);
			$response = $response->withRedirect("/user");
			return $response;
	});
	
	//view user
	$app->get('/user/{username}', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$response = $this->view->render($response, "userdetail.phtml", ["user" => $user]);
			return $response;
	})->setName('user-detail');
		
	//update user
	$app->get('/user/{username}/update', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$response = $this->view->render($response, "userupdate.phtml", ["user" => $user]);
			return $response;
	})->setName('user-update');
	$app->post('/user/{username}/update', function (Request $request, Response $response, $args) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$nama = $request->getParam('nm');
			$password = $request->getParam('password');
			$usertype = $request->getParam('usertype');
			$username = $request->getParam('usrnm');
			DBConnection()->exec("update user set nama = '".$nama."' , password = '".$password."' , usertype = '".$usertype."' where username = '".$username."' ;");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/user");
			return $response;
			})->setName('user-update');
					
	//delete user
	$app->get('/user/{username}/delete', function (Request $request, Response $response, $args ) {
			$username_id = (String)$args['username'];
			$mapper = new UserMapper($this->db);
			$user = $mapper->getUserByUsername($username_id);
			$user_mapper = new UserMapper($this->db);
			$user_mapper->delete($user);
			$response = $response->withRedirect("/user");
			return $response;
	});
	
	//tabel depot 
	$app->get('/depot', function (Request $request, Response $response) {
			$this->logger->addInfo("depot list");
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepot();
			$response = $this->view->render($response, "depot.phtml", ["depot" => $depot, "router" => $this->router]);
			return $response;
	});
		
	//insert depot
	$app->get('/depot/new', function (Request $request, Response $response) {
			$depot_mapper = new DepotMapper($this->db);
			$depot = $depot_mapper->getDepot();
			$response = $this->view->render($response, "depotadd.phtml", ["depot" => $depot]);
			return $response;
	});
	$app->post('/depot/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$depot_data = [];
			$depot_data['lonlat'] 		= filter_var($data['lonlat'], FILTER_SANITIZE_STRING);
			$depot_data['owner'] 		= filter_var($data['owner'], FILTER_SANITIZE_STRING);
			$depot_data['install_date'] = filter_var($data['install_date'], FILTER_SANITIZE_STRING);
			$depot_data['saldo'] 		= filter_var($data['saldo'], FILTER_SANITIZE_STRING);
			$depot = new DepotEntity($depot_data);
			$depot_mapper = new DepotMapper($this->db);
			$depot_mapper->save($depot);
			$response = $response->withRedirect("/depot");
			return $response;
	}); 	
			
	//depot update
	$app->get('/depot/{id}/update', function (Request $request, Response $response, $args) {
			$depot_id	 = (int)$args['id'];
			$mapper 	 = new DepotMapper($this->db);
			$depot		 = $mapper->getDepotById($depot_id);
			$response = $this->view->render($response, "depotupdate.phtml", ["depot" => $depot]);
			return $response;
	})->setName('depot-update');	
	$app->get('/depot/{id}/updat', function (Request $request, Response $response, $args) {
			$depot_id = (int)$args['id'];
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepotById($depot_id);
			$lonlat = $request->getParam('lonlat');
			$owner = $request->getParam('owner');
			$install_date = $request->getParam('install_date');
			$saldo = $request->getParam('saldo');
			DBConnection()->exec("update depot set  lonlat = '".$lonlat."', owner = '".$owner."', install_date = '".$install_date."', saldo = '".$saldo."'
			where id = ".$args['id'].";");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/depot");
			return $response;
	})->setName('depot-update');
			
	//delete depot
	$app->get('/depot/{id}/delete', function (Request $request, Response $response, $args ) {
			$depot_id = (int)$args['id'];
			$mapper = new DepotMapper($this->db);
			$depot = $mapper->getDepotById($depot_id);
			$depot_mapper = new DepotMapper($this->db);
			$depot_mapper->delete($depot);
			$response = $response->withRedirect("/depot");
			return $response;
	});
		
	//tabel deposit
	$app->get('/deposit', function (Request $request, Response $response) {
			$this->logger->addInfo("Deposit list");
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDeposit();
			$response = $this->view->render($response, "deposit.phtml", ["deposit" => $deposit, "router" => $this->router]);
			return $response;
	});
			
	//insert deposit
	$app->get('/deposit/new', function (Request $request, Response $response) {
			$deposit_mapper = new DepositMapper($this->db);
			$deposit = $deposit_mapper->getDeposit();
			$response = $this->view->render($response, "depositadd.phtml", ["deposit" => $deposit]);
			return $response;
	});
	$app->post('/deposit/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$deposit_data = [];
			
			$deposit_data['depot_id'] 		= filter_var($data['depot_id'], FILTER_SANITIZE_STRING);
			
			$deposit_data['nilai_deposit'] 	= filter_var($data['nilai_deposit'], FILTER_SANITIZE_STRING);
			$deposit = new DepositEntity($deposit_data);
			$deposit_mapper = new DepositMapper($this->db);
			$deposit_mapper->save($deposit);
			$response = $response->withRedirect("/deposit");
			return $response;
	});
	
	//Deposit Update
	$app->get('/deposit/{id}/update', function (Request $request, Response $response, $args) {
			$deposit_id	 = (int)$args['id'];
			$mapper 	 = new DepositMapper($this->db);
			$deposit	 = $mapper->getDepositById($deposit_id);			
			$response = $this->view->render($response, "depositupdate.phtml", ["deposit" => $deposit]);
			return $response;
	})->setName('deposit-update');
	$app->get('/deposit/{id}/updat', function (Request $request, Response $response, $args) {
			$deposit_id = (int)$args['id'];
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDepositById($deposit_id);
			$depot_id = $request->getParam('depot_id');
			$nilai_deposit = $request->getParam('nilai_deposit');
			DBConnection()->exec("update deposit set  depot_id = '".$depot_id."', nilai_deposit = '".$nilai_deposit."' , waktu = now() where id = ".$args['id'].";");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/deposit");
			return $response;
	})->setName('deposit-update');
			
	//delete deposit
	$app->get('/deposit/{id}/delete', function (Request $request, Response $response, $args ) {
			$deposit_id = (int)$args['id'];
			$mapper = new DepositMapper($this->db);
			$deposit = $mapper->getDepositById($deposit_id);
			$deposit_mapper = new DepositMapper($this->db);
			$deposit_mapper->delete($deposit);
			$response = $response->withRedirect("/deposit");
			return $response;
	});

	//tabel delivery
	$app->get('/delivery', function (Request $request, Response $response) {
			$this->logger->addInfo("Delivery list");
			$mapper = new DeliveryMapper($this->db);
			$delivery = $mapper->getDelivery();
			$response = $this->view->render($response, "delivery.phtml", ["delivery" => $delivery, "router" => $this->router]);
			return $response;
	});
	
	//insert delivery
	$app->get('/delivery/new', function (Request $request, Response $response) {
			$delivery_mapper = new DeliveryMapper($this->db);
			$delivery = $delivery_mapper->getDelivery();
			$response = $this->view->render($response, "deliveryadd.phtml", ["delivery" => $delivery]);
			return $response;
	});
	$app->post('/delivery/new', function (Request $request, Response $response) {
			$data = $request->getParsedBody();
			$delivery_data = [];
			$delivery_data['depot_id'] 		= filter_var($data['depot_id'], FILTER_SANITIZE_STRING);
			$delivery_data['nilai'] 	= filter_var($data['nilai'], FILTER_SANITIZE_STRING);
			$delivery = new DeliveryEntity($delivery_data);
			$delivery_mapper = new DeliveryMapper($this->db);
			$delivery_mapper->save($delivery);
			$response = $response->withRedirect("/delivery");
			return $response;
	});
	
	//Delivery Update
	$app->get('/delivery/{id}/update', function (Request $request, Response $response, $args) {
			$delivery_id	 = (int)$args['id'];
			$mapper 	 = new DeliveryMapper($this->db);
			$delivery	 = $mapper->getDeliveryById($delivery_id);			
			$response = $this->view->render($response, "deliveryupdate.phtml", ["delivery" => $delivery]);
			return $response;
	})->setName('delivery-update');
	$app->get('/delivery/{id}/updat', function (Request $request, Response $response, $args) {
			$delivery_id = (int)$args['id'];
			$mapper = new DeliveryMapper($this->db);
			$delivery = $mapper->getDeliveryById($delivery_id);
			$nilai = $request->getParam('nilai');
			DBConnection()->exec("update delivery set   nilai = '".$nilai."' , waktu = now() where id = ".$args['id'].";");
			echo('Data berhasil diupdate !');
			$response = $response->withRedirect("/delivery");
			return $response;
	})->setName('delivery-update');
			
	//delete delivery
	$app->get('/delivery/{id}/delete', function (Request $request, Response $response, $args ) {
			$delivery_id = (int)$args['id'];
			$mapper = new DeliveryMapper($this->db);
			$delivery = $mapper->getDeliveryById($delivery_id);
			$delivery_mapper = new DeliveryMapper($this->db);
			$delivery_mapper->delete($delivery);
			$response = $response->withRedirect("/delivery");
			return $response;
	});
	
$app->run();
