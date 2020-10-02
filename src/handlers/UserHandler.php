<?php
namespace src\handlers;

use \src\models\User;
use \src\models\UserRelation;
use \src\handlers\PostHandler;


class UserHandler  {

  public static function checkLogin($allFields = false){
    if(!empty($_SESSION['token'])){
        $token = $_SESSION['token'];
        $data = User::select()->where('token', $token)->one();
                   
        if(count($data)>0){
            $loggedUser = new User();
            $loggedUser->id = $data['id'];
            $loggedUser->name = $data['name'];
            $loggedUser->avatar = $data['avatar'];
            
            if($allFields){
            $loggedUser->birthdate = $data['birthdate'];
            $loggedUser->password = $data['password'];
            $loggedUser->email = $data['email'];
            $loggedUser->city = $data['city'];
            $loggedUser->work = $data['work'];
            $loggedUser->cover = $data['cover'];
            }
            return $loggedUser;
        }
    }    
    return false;
}

    public static function verifyLogin($email, $password){
      $user = User::select()->where('email', $email)->one();

      if($user){

        if(password_verify($password, $user['password'])){
            $token = md5(time().rand(0,9999).time());

            User::update()
             ->set('token', $token)
             ->where('email', $email)
            ->execute();

            return $token;

        }

      }

      return false;
    
    }
    public function idExists($id) {
        $user = User::select()->where('id', $id)->one();
  
        return $user ? true : false;
      }
    public function emailExists($email) {
      $user = User::select()->where('email', $email)->one();

      return $user ? true : false;
    }

    public function getUser($id, $full = false){
      $data=User::select()->where('id', $id)->one();

      if($data){
        $user = new User;
        $user->id = $data['id'];
        $user->name = $data['name'];
        $user->birthdate = $data['birthdate'];
        $user->city = $data['city'];
        $user->work = $data['work'];
        $user->avatar = $data['avatar'];
        $user->cover = $data['cover'];

        if($full){
          $user->followers = [];
          $user->following = [];
          $user->photos = [];

          //folowers

          $followers =UserRelation::select()->where('user_to', $id)->get();
            
          foreach ($followers as $follower) {
            $userData = User::select()->where('id', $follower['user_from'])->one();
            
            $newUser = new User;
            $newUser->id = $userData['id'];
            $newUser->name = $userData['name'];
            $newUser->avatar = $userData['avatar'];

            $user->followers[] = $newUser;
          }

          //follwing
          $following =UserRelation::select()->where('user_from', $id)->get();
            
          foreach ($following as $follower) {
            $userData = User::select()->where('id', $follower['user_to'])->one();
            
            $newUser = new User;
            $newUser->id = $userData['id'];
            $newUser->name = $userData['name'];
            $newUser->avatar = $userData['avatar'];

            $user->following[] = $newUser;
          }


          //photos
          $user->photos = PostHandler::getPhotosFrom($id);

        }

        return $user;

      } 
      return false;


    }

    public static function addUser($name, $email, $password, $birthdate) {
        $hash = password_hash($password , PASSWORD_DEFAULT );
        $token = md5(time().rand(0,9999).time());

        User::insert([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'birthdate' => $birthdate,
            'token' => $token
        ])->execute();

        return $token;
        
    }

    public static function isFollowing($from, $to){
      $data = UserRelation::select()
      ->where('user_from', $from)
      ->where('user_to', $to)
      ->execute();

      if($data){
        return true;
      }

      return false;
    }
    public static function follow($from, $to){
      UserRelation::insert([
        'user_from' => $from,
        'user_to' => $to
      ])->execute();
    }
    public static function unfollow($from, $to){
      UserRelation::delete()
      ->where('user_from', $from)
      ->where('user_to',$to)
    ->execute();
      
    }

    public static function searchUser($term){
        $users = [];
      
      $data = User::select()->where('name', 'like', '%'.$term.'%')->get();

      if($data){
        foreach($data as $user){
          $newUser = new User();

          $newUser->id = $user['id'];
          $newUser->name = $user['name'];
          $newUser->avatar = $user['avatar'];

          $users[] = $newUser;
        }


      }

      return $users;

    }

    public static function updateUser($updateFields)
    {
        User::update()
            ->set('name', $updateFields['name'])
            ->set('email', $updateFields['email'])
            ->set('birthdate', $updateFields['birthdate'])
            ->set('password', $updateFields['password'])
            ->set('city', $updateFields['city'])
            ->set('work', $updateFields['work'])
            ->set('avatar', $updateFields['avatar'])
            ->set('cover', $updateFields['cover'])
            ->where('id', $updateFields['id'])
            ->execute();
    }

}