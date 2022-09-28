<?php

use Illuminate\Support\Facades\Route;
use phpseclib3\Net\SSH2;
use Illuminate\Http\Request;

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

Route::get('/check-domain/{project}', function ($project) {
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    // echo $ssh->exec('ls -latr');
    // Check if folder exists
    echo $ssh->exec('cd /var/www/html && if [ -d "' . $project . '.g-web.agency" ]; then echo "true"; else echo "false"; fi');
});

Route::post('/install', function (Request $request) {
    // Execute shell script
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    
    $ssh->setTimeout(1000000);
    $ssh->exec('cd /var/www/html/ && git clone ' . $request->repo . ' ' . $request->domain . '.g-web.agency');
    $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn install');
});

Route::post('/build', function (Request $request) {
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    // Set timeout
    $ssh->setTimeout(1000000);
    switch ($request->framework) {
    case 'react':
        $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn build');
        break;
    case 'vue':
        $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn build');
        break;
    case 'nuxt':
        $static = "static";
        $ssh->exec("sed -i '2s/^/target: `'{$static}'`,/' /var/www/html/" . $request->domain . ".g-web.agency/nuxt.config.js");
        $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn build');
        $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn generate');
        return true;
        break;
    }
});

Route::post('/deploy', function (Request $request) {
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    $ssh->setTimeout(1000000);
    switch ($request->framework) {
    case 'react':
        $ssh->exec('cd /var/www/html/ && virtualhost create ' . $request->domain . '.g-web.agency /var/www/html/' . $request->domain . '.g-web.agency/build');
        break;
    case 'vue':
        $ssh->exec('cd /var/www/html/ && virtualhost create ' . $request->domain . '.g-web.agency /var/www/html/' . $request->domain . '.g-web.agency/dist');
        break;
    case 'nuxt':
        $ssh->exec('cd /var/www/html/ && virtualhost create ' . $request->domain . '.g-web.agency /var/www/html/' . $request->domain . '.g-web.agency/dist');
        break;
    }     
    // $ssh->exec('cd /var/www/html/ && certbot --apache -d ' . $request->domain . '.g-web.agency --redirect');
    return '---> <span class="text-pink-400"><a href="http://' . $request->domain . '.g-web.agency">http://' . $request->domain . '.g-web.agency</a></span>';
});