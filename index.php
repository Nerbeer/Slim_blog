<?php
// Slim PHP
require 'Slim/Slim.php';
require 'Views/TwigView.php';

// Paris and Idiorm
require 'Paris/idiorm.php';
require 'Paris/paris.php';

// Models
require 'models/Article.php';
require 'models/User.php';
// Configuration
TwigView::$twigDirectory = __DIR__ . '/Twig/lib/Twig/';

ORM::configure('mysql:host=localhost;dbname=blog');
ORM::configure('username', 'root');
ORM::configure('password', '');

// Start Slim.
$app = new Slim(array(
	'view' => new TwigView
));


// Blog Homepage.
$app->get('/', function() use ($app) {
	$articles = Model::factory('Article')->order_by_desc('timestamp')->find_many();
	$isLoginned	= User::isLoginned();	
	if($isLoginned)
		return $app->redirect('/newtest/user/'.$_COOKIE['login']);	
	else
		return $app->redirect('/newtest/feed');		
});

// Blog Feed.
$app->get('/feed', function() use ($app) {
	$articles = Model::factory('Article')->order_by_desc('timestamp')->find_many();
	$isLoginned	= User::isLoginned();
	$user_name = '';
	if($isLoginned)	
		$user_name = $_COOKIE['login'];
	return $app->render('blog_home.html', array(
		'articles'   => $articles,
		'isLoginned' => $isLoginned,
		'B_author'   => 'People',
		'User_name'  => $user_name
	));		
});

// Blog View.
$app->get('/posts/view/(:id)', function($id) use ($app) {
	$article = Model::factory('Article')->find_one($id);
	if (! $article instanceof Article) {
		$app->notFound();
	}
	$isLoginned	= User::isLoginned();
	$user_name = '';
	if($isLoginned)	
		$user_name = $_COOKIE['login'];
	//$app->response()->header('Content-Type', 'application/json');
	//echo json_encode($article->as_array());
	 return $app->render('blog_detail.html', array(
	 	'article'    => $article,
	 	'isLoginned' => $isLoginned,
	 	'User_name'  => $user_name
	 ));
});

// Blog User Feed.
$app->get('/user/(:name)',  function($name) use ($app) {
	$articles = Model::factory('Article')->where('author', $name)->order_by_desc('timestamp')->find_many();
	$isLoginned	= User::isLoginned();
	$user_name = '';
	if($isLoginned)	
		$user_name = $_COOKIE['login'];
	return $app->render('blog_home.html', array(
		'articles'   => $articles,
		'isLoginned' => $isLoginned,
		'B_author'   => $name,
		'User_name'  => $user_name
	));
});

// Blog User signup
$app->post('/signup', function() use ($app) {
	$name = $app->request()->post('alias');
    $password = $app->request()->post('password');
    $email = $app->request()->post('Email');

    User::register($email,$name,$password);
	$app->redirect('/newtest');
});


// Blog User signin
$app->post('/signin', function() use ($app) {
    $password = $app->request()->post('pass');
    $email = $app->request()->post('email');

    User::signin($email,$password);
	$app->redirect('/newtest');
});

// Blog User logout
$app->get('/logout', function() use ($app) {

    User::logout();
	$app->redirect('/newtest');
});


// Posts Add.
$app->get('/posts/add', function() use ($app) {
	$isLoginned	= User::isLoginned();
	$user_name = '';
	if($isLoginned)	
	{
		$user_name = $_COOKIE['login'];
		return $app->render('posts_input.html', array(
			'action_name' => 'Add', 
			'action_url'  => '/newtest/posts/add',
			'isLoginned'  => $isLoginned,
			'User_name'   => $user_name
		));
	}
	else
		return $app->redirect('/newtest/feed');	
});	


// Posts Add - POST.
$app->post('/posts/add', function() use ($app) {
	$isLoginned	= User::isLoginned();	
	if($isLoginned)
	{
		$title 	= $app->request()->post('title');
		$author 	= $_COOKIE['login'];
		$summary 	= $app->request()->post('summary');
		$content 	= $app->request()->post('content');
		$timestamp = date('Y-m-d H:i:s');
		Article::add($title,$author,$summary,$content,$timestamp);
	}
	$app->redirect('/newtest');
});

// Posts Delete.
$app->get('/posts/delete/(:id)', function($id) use ($app) {
	$isLoginned	= User::isLoginned();	
	if($isLoginned)
	{
		$article = Model::factory('Article')->find_one($id);
		if($article->author == $_COOKIE['login'])
			if ($article instanceof Article) {
				$article->delete();
			}
		
		return $app->redirect('/newtest/user/'.$_COOKIE['login']);	
	}
	return $app->redirect('/newtest');
});

// Posts Edit.
$app->get('/posts/edit/(:id)',  function($id) use ($app) {
	$isLoginned	= User::isLoginned();
	$user_name = '';	
	if($isLoginned)
	{
		$article = Model::factory('Article')->find_one($id);
		if (! $article instanceof Article) {
			$app->notFound();
		}	
		if($article->author === $_COOKIE['login'])
		{
			$user_name = $_COOKIE['login'];
			return $app->render('posts_input.html', array(
				'action_name' 	=> 	'Edit', 
				'action_url' 	=> 	'/newtest/posts/edit/' . $id,
				'article'		=> 	$article,
				'isLoginned'    =>  $isLoginned,
				'User_name'     =>  $user_name
			));
		}
	}
	$app->redirect('/newtest');
});


// Posts Edit - POST.
$app->post('/posts/edit/(:id)', function($id) use ($app) {
	$isLoginned	= User::isLoginned();	
	if($isLoginned)
	{
		$article = Model::factory('Article')->find_one($id);
		if (! $article instanceof Article) {
			$app->notFound();
		}
		if($article->author === $_COOKIE['login'])
		{
			$title 		= $app->request()->post('title');
			$summary 	= $app->request()->post('summary');
			$content 	= $app->request()->post('content');
			$timestamp  = date('Y-m-d H:i:s');
			Article::edit($id,$title,$summary,$content,$timestamp);
		}
	}
	$app->redirect('/newtest');
});


// Slim Run.
$app->run();