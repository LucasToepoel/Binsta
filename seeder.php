<?php

require __DIR__ . '/vendor/autoload.php';
use RedBeanPHP\R;

// Check if a database connection has already been established

getconnection();
destroyDatabase();
createUsers();
createPosts();
createComments();
createProfiles();
closeConnection();

function createProfiles()
{
    $profiles = [
        [
            'user_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'JohnDoe@gmail.com',
            'bio' => 'I am a software developer',
            'location' => 'Nairobi, Kenya',
            'age' => '25',
        ],
        [
            'user_id' => 2,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'JaneDoegmail.com',
            'bio' => 'I am a hardware developer',
            'location' => 'Amsterdam, Netherlands',
            'age' => '18',
        ],
    ];

    $profileBeans = [];
    foreach ($profiles as $profile) {
        $profileBean = R::dispense('profile');
        $profileBean->user_id = $profile['user_id'];
        $profileBean->first_name = $profile['first_name'];
        $profileBean->last_name = $profile['last_name'];
        $profileBean->email = $profile['email'];
        $profileBean->bio = $profile['bio'];
        $profileBean->location = $profile['location'];
        $profileBean->age = $profile['age'];

        $id = R::store($profileBean);
        $profileBeans[$id] = $profileBean;
    }
    echo 'Profiles inserted: ' . count($profileBeans) . PHP_EOL;
}
function createUsers()
{
    $users = [
        [
            'user_id'       => 1,
            'username' => 'user1',
            'password' => 'password',
            'profilephoto_id' => '1',

        ],
        [
            'user_id'       => 2,
            'username' => 'user2',
            'password' => 'password',
            'profilephoto_id' => 0,

        ],
    ];

    $userbeans = [];
    foreach ($users as $user) {
        $userBean = R::dispense('user');
        $userBean->username = $user['username'];
        $userBean->password = password_hash($user['password'], PASSWORD_DEFAULT);
        $userBean->profilephoto_id = $user['profilephoto_id'];

        $id = R::store($userBean);
        $userbeans[$id] = $userBean;
    }
    echo 'Users inserted: ' . count($userbeans) . PHP_EOL;
}

function createPosts()
{


            $posts = [
                [
                    'user_id' => 1,
                    'code'    => 'foobar {
                        print("Hello, World!");
                    }',
                    'content' => 'First Post',
                    'likes'      => 0,
                    'liked_by' => '[]',
                    'created_at' => '2021-01-01 00:00:00',
                    'is_fork'    => false,
                    'language'   => 'javascript',
                ],
                [
                    'user_id' => 2,
                    'code'    => 'foobar {
                        print("Goodbye, World!");
                    }',
                    'content' => 'Second Post',
                    'likes'      => 5,
                    'liked_by' => '[]',
                    'created_at' => '2021-01-02 12:00:00',
                    'is_fork'    => false,
                    'language'   => 'javascript',
                ],
                [
                    'user_id' => 1,
                    'code'    => 'foobar {
                        print("Hola, Mundo!");
                    }',
                    'content' => 'Third Post',
                    'likes'      => 10,
                    'liked_by' => '[]',
                    'created_at' => '2021-01-03 09:30:00',
                    'is_fork'    => false,
                    'language'   => 'javascript',
                ],
                [
                    'user_id' => 2,
                    'code'    => 'foobar {
                        print("Bonjour, le Monde!");
                    }',
                    'content' => 'Fourth Post',
                    'likes'      => 3,
                    'liked_by' => '[]',
                    'created_at' => '2021-01-04 15:45:00',
                    'is_fork'    => false,
                    'language'   => 'javascript',
                ],

            ];

            $postBeans = [];

            foreach ($posts as $post) {
                $postBean = R::dispense('post');
                $postBean->user_id = $post['user_id'];
                $postBean->code = $post['code'];
                $postBean->content = $post['content'];
                $postBean->likes = $post['likes'];
                $postBean->liked_by = $post['liked_by'];
                $postBean->created_at = $post['created_at'];
                $postBean->is_fork = $post['is_fork'];
                $postBean->language = $post['language'];

                $id = R::store($postBean);
                $postBeans[$id] = $postBean;
            }
            echo 'Posts inserted: ' . count($postBeans) . PHP_EOL;
}
function createComments()
{
    $comments = [
        [
            'user_id' => 1,
            'post_id' => 1,
            'content' => 'First Comment',
            'likes'   => 0,
            'liked_by' => '[]',
            'created_at' => '2021-01-01 00:00:00',
        ],
        [
            'user_id' => 2,
            'post_id' => 1,
            'content' => 'Second Comment',
            'likes'   => 5,
            'liked_by' => '[]',
            'created_at' => '2021-01-01 00:00:00',
        ],
        [
            'user_id' => 1,
            'post_id' => 2,
            'content' => 'Third Comment',
            'likes'   => 10,
            'liked_by' => '[]',
            'created_at' => '2021-01-01 00:00:00',
        ],
        [
            'user_id' => 2,
            'post_id' => 2,
            'content' => 'Fourth Comment',
            'likes'   => 3,
            'liked_by' => '[]',
            'created_at' => '2021-01-01 00:00:00',
        ],
    ];

    $commentBeans = [];
    foreach ($comments as $comment) {
        $commentBean = R::dispense('comment');
        $commentBean->user_id = $comment['user_id'];
        $commentBean->post_id = $comment['post_id'];
        $commentBean->content = $comment['content'];
        $commentBean->likes = $comment['likes'];
        $commentBean->liked_by = $comment['liked_by'];

        $id = R::store($commentBean);
        $commentBeans[$id] = $commentBean;
    }
    echo 'Comments inserted: ' . count($commentBeans) . PHP_EOL;
}


function destroyDatabase()
{
    R::nuke();
    echo 'Database nuked' . PHP_EOL;
}

function closeConnection()
{
    R::close();
    echo 'Database connection closed' . PHP_EOL;
}
