<?php

use Illuminate\Support\Facades\Route;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Get all users
Route::post('/create/{project}', function ($project) {

    // Load RSA private key
    $key = PublicKeyLoader::load(file_get_contents('/var/www/html/.keys/key'));
    // Login to ssh
    $ssh = new SSH2('create.g-web.agency');
    if (!$ssh->login('root', $key)) {
        exit('Login Failed');
    }
    // Git status
    echo $ssh->exec('cd /var/www/html/ && git status && git add .');
    echo $ssh->exec('cd /var/www/html/ && git commit -m "from-g-web-server"');
    echo $ssh->exec('cd /var/www/html/ && git push origin');
});
