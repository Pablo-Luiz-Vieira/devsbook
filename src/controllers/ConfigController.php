<?php
namespace src\controllers;
use \core\Controller;
use DateTime;
use \src\handlers\UserHandler;
class ConfigController extends Controller {
     
    private $loggedUser;
    public function __construct() {
        $this->loggedUser = UserHandler::checkLogin();
        if($this->loggedUser === false){
            $this->redirect('/signin');
        }
    }
    public function index(){
        //Mensagem exibida na tela, caso exista.
        $flash = "";        
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        
        //Montando a tela de configuração do usuário
        $this->render('config',[
            'loggedUser' => $this->loggedUser,
            'flash' => $flash                
        ]);
    }
    public function configAction(){
        //Recebe os dados do formulário
        $email = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST,'password');
        $name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $birthDate = filter_input(INPUT_POST,'birthDate',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $city = filter_input(INPUT_POST,'city',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $work = filter_input(INPUT_POST,'work',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $newPassword = filter_input(INPUT_POST,'newPassword');
    if(!$email && !$password && !$name && !$birthDate && !$city && !$work && !$avatar && !$cover ){
            $this->redirect("/config");
    }
        //Pega os dados do usuário logado no BD
        $user = UserHandler::getUser($this->loggedUser->id, false);
        
        //Verificação do email
        if(!$email){
            $email = $user->email;
        } else if((UserHandler::emailExists($email)) && ($email != $user->email)) {
            $_SESSION['flash'] = "Email já utilizado por outro usuário";
            $this->redirect("/config");
        }
        
        //Verificação da senha
        if( $password != $newPassword) {
            $_SESSION['flash'] = "Campos 'Nova Senha' e 'Confirmar Nova Senha' diferentes! ";
            $this->redirect("/config");
        } 
        //Verificão do nome
        if(empty($name)){
            $name = $user->name;
        }
        //Verificação da data
        if(empty($birthDate)){
            $birthDate = $user->birthdate;
        } else { 
           $birthDate = explode("-", $birthDate);
           // mes/dia/ano
           if(!checkdate($birthDate[1], $birthDate[2],$birthDate[0])){
               $_SESSION['flash'] = "Data de nascimento inválida!";
               $this->redirect("/config");
           }
           //ano, mes, dia
           $birthDate = $birthDate[0].'-'.$birthDate[1].'-'.$birthDate[2]; 
          
        }
        //Verificão da cidade
        if(empty($city)){
            $city = $user->city;
        }
        //Verificão do trabalho
        if(empty($work)){
            $work = $user->work;
        }
        //Verificão do avatar
        if(empty($avatar)){
            $avatar = $user->avatar;
        }
        //Verificão do trabalho
        if(empty($cover)){
            $cover = $user->cover;
        }

        //avatar
        if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])){
            $newAvatar = $_FILES['avatar'];

            if(in_array($newAvatar['type'], ['image.jpeg', 'image.jpg', 'image.png'])){
                $avatarName = $this->cutImage($newAvatar, 200, 200, 'media/avatars');
                $updateFields['avatar'] = $avatarName;
            }
        }

        //cover
        if (isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])){
        $newCover = $_FILES['cover'];

            if(in_array($newCover['type'], ['image.jpeg', 'image.jpg', 'image.png'])){
                $coverName = $this->cutImage($newCover, 850, 310, 'media/covers');
                $updateFields['cover'] = $coverName;
            }
        }

    UserHandler::updateUser($this->loggedUser->id, $email, $password, $name, $birthDate, $city, $work, $avatar, $cover);   
    $this->redirect("/config");   
    }
   
    private function cutImage($file, $w, $h, $folder){
        list($widthOrigin, $heightOrigin) = getImagesize($file['tmp_name']);
        $ratio = $widthOrigin / $heightOrigin;

        $newWidth = $w;
        $newHeight =$newWidth / $ratio;

        if($newHeight < $h) {   
            $newHeight = $h;
            $newWidth = $newHeight * $ratio;

        }

        $x = $w - $newWidth;
        $y = $h - $newHeight;
        $x = $x < 0 ? $x / 2 : $x;
        $y = $y < 0 ? $y / 2 : $y;

        $finalImage =  imagecreatetruecolor($w, $h);

        switch ($file['type']) {
            case 'image.jpeg':
            case 'image.jpg':    
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;
            case 'image.png':
                $image = imagecreatefrompng($file['tmp_name']);

            break;
           
        }

        imagecopyresampled(
            $finalImage , $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight, $widthOrigin, $heightOrigin
        );

        $fileName = md5(time().rand(0,9999)).'.jpg';

        imagejpeg($finalImage, $folder.'/'.$fileName);

        return $fileName;
    }
  
    
}//fim da classe 

