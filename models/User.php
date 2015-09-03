<?php
class User extends Model
{
	public static function signin($email, $password) {

		$user = Model::factory('User')->where('email',$email)->find_one();
		
	    if($user->password === md5($password) and $user->email === $email) {
	         $hash = md5(User::generateHash(10));
	         $user->hash = $hash;
	         $user->save();
	 
	         $cookieAddTime = time() + 60*60*24;
	         setcookie("id", $user->id, $cookieAddTime);
	         setcookie("hash", $user->hash, $cookieAddTime);
	         setcookie("login", $user->name, $cookieAddTime);
	    }
	}

	public static function isLoginned() {
		$isLoginned = false;
		if( isset( $_COOKIE['id']) )
		{
			$user =  Model::factory('User')->where('id',$_COOKIE['id'])->find_one();
			if ( isset( $_COOKIE['hash'] ) and $user->hash === $_COOKIE['hash'])
				$isLoginned = true;
		}

		return $isLoginned;
	}

	public static function logout() {
		$cookieDelTime = time() - 3600;

	    setcookie("id", "", $cookieDelTime);
	    setcookie("hash", "", $cookieDelTime);
	    setcookie("login", "", $cookieDelTime);
	}

	public static function register($email, $name, $password) {
		if($name != "" and $password != "" and $email != "") {
			$user = Model::factory('User')->create();
			$user->name = $name;
			$user->email = $email;
			$user->password = md5($password);
			$user->save();
		}  
	}

	public static function generateHash($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
	}

}