<?php
namespace src\controllers;
use \core\Controller;
use \src\handlers\UserHandler;
class ConfigController extends Controller {
    
    private $loggedUser;
    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin(true);
        
        if(!$this->loggedUser) $this->redirect('/login');
    }
    public function index() {
        $user = $this->loggedUser;
        $flash = '';
        if(!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $this->render('config', [
            'loggedUser' => $user,
            'flash' => $flash
        ]);
    }
    public function save()
    {
        $user = $this->loggedUser;
        if(!$user) $this->redirect('/config');
        $updateFields = [];
        // Campos obrigatórios
        $name = filter_input(INPUT_POST, 'name');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $birthdate = filter_input(INPUT_POST, 'birthdate');
        if(!$name || !$email || !$birthdate) {
            $_SESSION['flash'] = 'Nome, Email e/ou Data de nascimento são obrigatórios';
            $this->redirect('/config');
        }
        // ID | name
        $updateFields['id'] = $user->id;
        $updateFields['name'] = $name;
        // birthdate
        $birthdate = explode('/', $birthdate);
        if(count($birthdate) !== 3) {
            $_SESSION['flash'] = 'Data de nascimento inválida';
            $this->redirect('/config');
        }
        $birthdate = $birthdate[2] . '-' . $birthdate[1] . '-' . $birthdate[0];
        if(!strtotime($birthdate)) {
            $_SESSION['flash'] = 'Data de nascimento inválida';
            $this->redirect('/config');
        }
        $updateFields['birthdate'] = $birthdate;
        // email
        $updateFields['email'] = $user->email;
        if($email !== $user->email) {
            if(UserHandler::emailExists($email)) {
                $_SESSION['flash'] = 'Email já cadastrado';
                $this->redirect('/config');
            }
            $updateFields['email'] = $email;
        }
        // password
        $password = filter_input(INPUT_POST, 'password');
        $updateFields['password'] = $user->password;
        $password_confirmation = filter_input(INPUT_POST, 'password_confirmation');
        if(!empty($password) || !empty($password_confirmation)) {
            if($password === $password_confirmation) {
                $updateFields['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $_SESSION['flash'] = 'Senhas não coincidem';
                $this->redirect('/config');
            }
        }
        $city = filter_input(INPUT_POST, 'city');
        $work = filter_input(INPUT_POST, 'work');
        // city |  work (can be null)
        $updateFields['city'] = $city ;
        $updateFields['work'] =  $work;

        // avatar 
        if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
            $newAvatar = $_FILES['avatar'];

            if(in_array($newAvatar['type'], ['image/jpeg', 'image/jpg,', 'image/png'])) {
                $avatarName = $this->cutImage($newAvatar, 200, 200, 'media/avatars');
                $updateFields['avatar'] = $avatarName;
            }

         }

         //cover

         if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])) {
            $newCover = $_FILES['cover'];

            if(in_array($newCover['type'], ['image/jpeg', 'image/jpg,', 'image/png'])) {
                $coverName = $this->cutImage($newCover, 850, 310, 'media/covers');
                $updateFields['cover'] = $coverName;
            }

         }
        
  
         
        // update
        UserHandler::updateUser($updateFields);
        $this->redirect('/config');
    }

    private function cutImage($file, $w, $h, $folder) {
        list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
        $ratio = $widthOrig / $heightOrig;

        $newWidth = $w;
        $newHeight = $newWidth / $ratio;

        if($newHeight < $h) {

            $newHeight = $h;
            $newWidth = $newHeight * $ratio;
        }

        $x = $w - $newWidth;
        $y = $h - $newHeight;
        $x = $x > 0 ? $x / 2 : $x;
        $y = $y > 0 ? $y / 2 : $y;

        $finalImage = imagecreatetruecolor($w, $h);
        switch ($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
            break;
        }

        imagecopyresampled(
            $finalImage, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );

        $fileName = md5(time().rand(0,9999)).'.jpg';

        imagejpeg($finalImage, $folder.'/'.$fileName);

        return $fileName;
    }
}