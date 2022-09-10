<?php

use Illuminate\Support\Facades\Route;

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

    // Global path
    // $path = '/var/www/html/' . $project . '.g-web.agency';

    // $test = shell_exec('cd /var/www/html && git add . && git commit -m "Create ' . $project . '" && git push origin');
    $test = shell_exec('cd /var/www/html && git status');
    echo $test;

    // Check if exist
    // if (!file_exists($path)) {
    //     // Create folder
    //     mkdir($path, 0777, true);
    //     // Add, commit and push to git
    // } else {
    //     echo 'Project already exist';
    // }
});
