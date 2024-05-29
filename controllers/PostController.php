<?php

use RedBeanPHP\R;

if (!R::testConnection()) {
    getconnection();
}

class PostController extends BaseController
{
    public function create()
    {
        $this->authorizeUser();
        view('posts/create.twig');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createPost();
        }
    }

    public function createComment()
    {
        $this->authorizeUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createCommentPost();
        }
    }

    private function createCommentPost()
    {
        $this->authorizeUser();
        $content = $_POST['content'] ?? '';
        $post_id = $_POST['post_id'] ?? '';
        $user_id = $_POST['user_id'] ?? '';
        $post = R::load('post', $post_id);

        if (empty($content) || empty($post_id) || empty($user_id)) {
            error(400, 'Invalid request: Please fill in all fields');
        }

        $comment = R::dispense('comment');
        $comment->user_id = $_SESSION['user']->id;
        $comment->post_id = $post->id;
        $comment->content = $content;
        $comment->created_at = date('Y-m-d H:i:s');
        R::store($comment);
        //go back to index

        header('Location: /');
    }

    private function createPost(): void
    {
        $this->authorizeUser();
        $code = $_POST['code'] ?? '';
        $content = $_POST['content'] ?? '';
        $is_fork = $_POST['is_fork'] ?? 0;
        $language = $_POST['language'] ?? 'javascript';

        // Validate the form data
        $this->validateFormData($code, $content);

        $post = R::dispense('post');
        $post->user_id = $_SESSION['user']->id ?? 1;
        $post->code = $code;
        $post->content = $content;
        $post->created_at = date('Y-m-d H:i:s');
        $post->is_fork = $is_fork;
        $post->language = $language;
        $post->likes = 0;
        $post->liked_by = json_encode([]);
        $id = R::store($post);

        header('Location: ?controller=post&method=show&id=' . $post->id);
        exit;
    }

    public function edit(): object
    {
        $this->authorizeUser();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            error(400, 'Invalid request: ID not provided');
        }
        $post = R::load('post', $id);

        if (!$post->id) {
            error(404, 'post with ID ' . $id . ' not found');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->editPost($post);
        }
        $this->index();


        return $post;
    }

    public function editPost($post): void
    {
        $this->authorizeUser();
        $content = $_POST['content'] ?? '';
        // Validate the form data
        $this->validateFormData($content);
        $post =  $this->getEntryById('post', 'id') ?? null;
        // Save the new post to the database
        $post->content = $content;
        R::store($post);
        // Redirect to the show page for the newly created post
        header('Location: /post');
        exit;
    }

    public function index(): void
    {
        $posts = R::findAll('post');
        $currentUrl = $_SERVER['REQUEST_URI'];

        // Load usernames for each post
        foreach ($posts as $post) {
            $user = R::load('user', $post->user_id);
            $post->username = $user->username;
            $post->profilephoto = $user->profilephoto_id;


            // Load comments for each post
            $comments = R::findAll('comment', 'post_id = ?', [$post->id]);
            foreach ($comments as $comment) {
                $user = R::load('user', $comment->user_id);
                $comment->username = $user->username;
            }

            $post->comments = $comments;
        }
        //order by date
        usort(
            $posts,
            function ($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            }
        );

        view('posts/index.twig', ['posts' => $posts, 'user' => $_SESSION['user'] ?? null, 'edit', 'currentUrl' => $currentUrl]);
    }

    public function show(): void
    {
        echo 'show';
        $id = $_GET['id'] ?? null;
        if (!$id) {
            error(400, 'Invalid request: ID not provided');
        }

        $post = R::load('post', $id);
        if (!$post->id) {
            error(404, 'post with ID ' . $id . ' not found');
        }

        view('posts/show.twig', ['post' => $post]);
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            error(400, 'Invalid request: ID not provided');
        }

        $post = R::load('post', $id);
        if (!$post->id) {
            error(404, 'post with ID ' . $id . ' not found');
        }

        R::trash($post);
        header('Location: /');
        exit;
    }

    public function like(): void
    {
        $this->authorizeUser();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            error(400, 'Invalid request: ID not provided');
        }

        $post = R::load('post', $id);
        if (!$post->id) {
            error(404, 'post with ID ' . $id . ' not found');
        }

        if (!isset($_SESSION['user'])) {
            error(400, 'Invalid request: You must be logged in to like a post');
        }

        if ($post->user_id === $_SESSION['user']->id) {
            error(400, 'Invalid request: You cannot like your own post');
        }

        if (in_array($_SESSION['user']->id, json_decode($post->liked_by, true))) {
            $this->unlikePost($post);
        } else {
            $this->likePost($post);
        }
    }

    private function likePost($post): void
    {
        $post->likes++;
        $post->liked_by = json_encode(array_merge(json_decode($post->liked_by, true), [$_SESSION['user']->id]));
        R::store($post);
        returnPage();
        exit;
    }

    private function unlikePost($post): void
    {
        $post->likes--;
        $post->liked_by = json_encode(array_diff(json_decode($post->liked_by, true), [$_SESSION['user']->id]));
        R::store($post);
        returnPage();
        exit;
    }

    private function validateFormData(...$fields): void
    {
        foreach ($fields as $field) {
            if (empty($field)) {
                error(400, 'Invalid request: Please fill in all fields');
            }
        }
    }
}
