<?php

use RedBeanPHP\R;

if (!R::testConnection()) {
    getconnection();
}

class ProfileController extends BaseController
{
    public function show(): void
    {
        if (isset($_SESSION['user'])) {
            $users = R::load('user', $_SESSION['user']->id);
        }

        $currentuserprofile = R::load('user', $_GET['id']);
        $posts = R::findAll('post', 'user_id = ?', [$currentuserprofile->id]);
        $profile = R::findOne('profile', 'user_id = ?', [$currentuserprofile->id]);
        $currentUrl = $_SERVER['REQUEST_URI'];

        // Load usernames for each post
        foreach ($posts as $post) {
            $users = R::load('user', $post->user_id);
            $post->username = $users->username;
            $post->profilephoto = $users->profilephoto_id;

            // Load comments for each post
            $comments = R::findAll('comment', 'post_id = ?', [$post->id]);
            foreach ($comments as $comment) {
                $user = R::load('user', $comment->user_id);
                $comment->username = $user->username;
            }

            $post->comments = $comments;
        }

        // Order by date
        usort($posts, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        view('profiles/show.twig', ['user' => $_SESSION['user'] ?? null, 'users' => $users, 'posts' => $posts, 'profile' => $profile, 'currentUrl' => $currentUrl]);
    }

    public function index(): void
    {
        $search = ""; // Initialize $search

        if (isset($_POST['search'])) {
            $search = $_POST['search'];
            $users = R::findAll('user', 'username LIKE ?', ["%$search%"]);
        } else {
            $users = R::findAll('user');
        }

        view('profiles/index.twig', ['users' => $users, 'search' => $search]);
    }

    public function delete(): void
    {
        $this->authorizeUser();
        $user = R::load('user', $_GET['id']);
        R::trash($user);
        header('Location: ?controller=profile&method=index');
        exit;
    }

    public function edit(): void
    {
        $this->authorizeUser();
        $user = R::load('user', $_GET['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->editPost($user);
        } else {
            // Only show the form if it's a GET request
            $this->show();
        }
    }

    public function editPost($user): void
    {
        $this->authorizeUser();
        $location = sanitizeInput($_POST['location'] ?? '');
        $bio = sanitizeInput($_POST['bio'] ?? '');
        $age = sanitizeInput($_POST['age'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');

        // Fetch profile using RedBeanPHP
        $profile = R::findOne('profile', 'user_id = ?', [$user->id]);

        // Save the new profile data to the database (basic security)
        $profile->user_id = $user->id;
        $profile->bio = $bio;
        $profile->location = $location;
        $profile->age = $age;
        $profile->email = $email;
        $profile->first_name = $first_name;
        $profile->last_name = $last_name;

        // Save changes using RedBeanPHP
        R::store($profile);

        // Redirect to the profile show page
        header('Location: ?controller=profile&method=show&id=' . $user->id);
        exit;
    }
}
