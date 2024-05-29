<?php

use RedBeanPHP\R;

class UserController extends BaseController
{
    public function register(): void
    {
        if (isset($_POST['username'])) {
            $this->registerPost();
            $this->loginPost();
        }
        view('system/register.twig');
    }

    public function registerPost()
    {
        getconnection();

        switch (true) {
            case empty($_POST['username']):
                error(400, 'Username is required');
                break;
            case empty($_POST['password']):
                error(400, 'Password is required');
                break;
            case R::findOne('user', 'username = ?', [$_POST['username']]):
                error(400, 'Username already exists');
                break;
            case $_POST['password'] !== $_POST['password-verify']:
                error(400, 'Passwords do not match');
                break;
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = R::dispense('user');
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_DEFAULT);

        R::store($user);
    }

    public function loginPost(): void
    {
        getconnection();
        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = R::findOne('user', 'username = ?', [$username]);

        if (!$user) {
            error(404, 'User not found');
        }

        if (!password_verify($password, $user->password)) {
            error(403, 'Password incorrect');
        }

        $_SESSION['user'] = $user;

        header('Location: /index');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: http://localhost/user/login');
        exit;
    }

    public function login(): void
    {
        if (isset($_POST['username'])) {
            $this->loginPost();
        } else {
            view('system/login.twig');
        }
    }

    public function index(): void
    {
        echo "index";
        $users = R::findAll('user');

        view('profiles/index.twig', ['users' => $users]);
    }

    public function edit(): void
    {
        $this->authorizeUser();
        getconnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = R::load('user', $_SESSION['user']->id);

            $this->editPost($user);
        } else {
            $profileController = new ProfileController();
            $profileController->show();
        }
    }

    public function editPost($user): void
    {
        // Process form data (with basic security)
        $this->authorizeUser();
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null; // Passwords require separate handling
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_DEFAULT);

        // Process file upload (if file submitted)
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadDir = 'img/';
            $userId = $_SESSION['user']->id;
            $imageFileType = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $fileName = $userId . '.png';
            $uploadFile = $uploadDir . $fileName;

            // Basic validation
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadFile)) {
                    $user->profilephoto_id = $user->id;
                } else {
                    error(500, 'File upload failed');
                }
            } else {
                error(400, 'Invalid file type');
            }
        }

        R::store($user);

        // Redirect to the profile show page
        header('Location: ?controller=profile&method=show&id=' . $user->id);
        exit;
    }
}
