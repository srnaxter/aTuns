<?php
/**
 * Created by PhpStorm.
 * User: navarrocantero
 * Date: 25/11/2017
 * Time: 03:01
 */

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\User;
use Sirius\Validation\Validator;

class AuthController extends BaseController
{
    public function getLogin()
    {

        $webIinfo = [
            'title' => "Login"
        ];

        return $this->render('auth/login.twig', ["webInfo" => $webIinfo]);
    }

    public function postLogin()
    {
        $errors = [];
        $validator = new Validator();

        $validator->add('inputEmail:Email', 'email', [], 'Type a correct email format');
        $validator->add('inputEmail:Email', 'required', [], 'The field {label} is required');
        $validator->add('inputPassword:Password', 'required', [], 'The field {label} is required');


        if ($validator->validate($_POST)) {

//            $user = User::query()->where('email', $_POST['inputEmail'])->first();
            $user = User::where('email', $_POST['inputEmail'])->first();

            if (password_verify($_POST['inputPassword'], $user->password)) {
                $_SESSION['userId'] = $user->id;
                $_SESSION['userName'] = $user->name;
                $_SESSION['userEmail'] = $user->email;


                header('Location: ' . BASE_URL);
            }

            $validator->addMessage('authError', 'User   or Pass are incorrect');
        }
        $errors = $validator->getMessages();
        return $this->render('auth/login.twig', ['errors' => $errors]);
    }

    public function getLogout()
    {
        unset($_SESSION['userId']);
        unset($_SESSION['userEmail']);
        unset($_SESSION['userName']);
        header("Location: " . BASE_URL);
    }

    public function getProfile()
    {
        $user = [
            'userName' => $_SESSION['userName'],
            'userEmail' => $_SESSION['userEmail']
        ];
        $webIinfo = [
            'title' => "Profile"
        ];

        return $this->render('auth/profile.twig', [
            "webInfo" => $webIinfo,
            "user" => $user
        ]);
    }

    public function postProfile()
    {
        $webIinfo = [
            'title' => "Profile"
        ];

        $validator = new Validator();
        $errors = [];

        // Extrct POST DATA
        $user['userEmail'] = htmlspecialchars(trim($_POST['inputEmail']));
        $user['userName'] = htmlspecialchars(trim($_POST['inputName']));

        // Extract OLD USER DATA FROM DATABASE
        $oldUserData = User::where('email', $_SESSION['userEmail'])->first();

        // Some validations ( like the RegisterController but no the same  )
        $validator->add('inputName:Nombre', 'required', [], 'The field {label} is required');
        $validator->add('inputName:Nombre', 'minlength', ['min' => 5], 'The field {label} must have 5 charachters at least');
        $validator->add('inputEmail:Email', 'required', [], 'The field {label} is required');
        $validator->add('inputEmail:Email', 'email', [], 'Type a correct email format');
        $validator->add('newPassword1:Password', 'required', [], 'The field {label} is required');
        $validator->add('newPassword1:Password', 'minlength', ['min' => 8], 'The field {label} must have 8 charachters at least');
        $validator->add('newPassword2:Password', 'required', [], 'The field {label} is required');
        $validator->add('newPassword1:Password', 'match', 'newPassword2', 'The passwords dont match');

        if ($validator->validate($_POST)) {
            // A test to validate if the user type the correct old password
                $oldUserData->update([
                    'id' => $oldUserData->id,
                    'name' => $user['userName'],
                    'email' => $user['userEmail'],
                    'password' => password_hash($_POST['newPassword1'], PASSWORD_DEFAULT)
                ]);
                header('Location: ' . BASE_URL);

        } else {
            $errors = $validator->getMessages();
        }
        return $this->render('auth/profile.twig', [
            "webInfo" => $webIinfo,
            'errors' => $errors,
            "user" => $user
        ]);
    }
}