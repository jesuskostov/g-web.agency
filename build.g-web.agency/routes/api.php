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
    if ($request->framework !== 'static') {
        $ssh->exec('cd /var/www/html/' . $request->domain . '.g-web.agency && yarn install');
    }
    // TODO: Add if state for static or dynamic
});

Route::post('/build', function (Request $request) {
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    // Set timeout
    $ssh->setTimeout(1000000);
    switch ($request->framework) {
    case 'static':
        break;
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
    case 'static':
        $ssh->exec('cd /var/www/html/ && virtualhost create ' . $request->domain . '.g-web.agency /var/www/html/' . $request->domain . '.g-web.agency/');
        break;
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
    // Split domain
    $repo = explode('/', explode('.', $request->repo)[1])[1];
    $res = '---> <span class="text-pink-400"><a href="http://' . $request->domain . '.g-web.agency">http://' . $request->domain . '.g-web.agency</a></span><br> ---> Add this <span class="text-blue-300">https://build.g-web.agency/api/hook/' . $request->domain . '/' . $request->framework . '</span> <br> ---> to your webhooks <span class="text-pink-400 pb-6"><a href="https://github.com/' . $request->username . '/' .$repo. '/settings/hooks" target="_blank">Here</a></span>';
    return $res;
});

Route::post('/hook/{domain}/{framework}', function ($domain, $framework) {
    $ssh = new SSH2('build.g-web.agency');
    if (!$ssh->login('root', 'jesus@zueka333')) {
        exit('Login Failed');
    }
    $ssh->setTimeout(1000000);
    switch ($framework) {
    case 'static':
        $ssh->exec('cd /var/www/html/' . $domain . '.g-web.agency && git pull');
        $ssh->exec('echo "success" > /var/www/html/test.txt');
        break;
    case 'react':
        $ssh->exec('cd /var/www/html/' . $domain . '.g-web.agency && git pull && yarn install && yarn build');
        $ssh->exec('echo "success" > /var/www/html/test.txt');
        break;
    case 'vue':
        $ssh->exec('cd /var/www/html/' . $domain . '.g-web.agency && git pull && yarn install && yarn build');
        $ssh->exec('echo "success" > /var/www/html/test.txt');
        break;
    case 'nuxt':
        $ssh->exec('cd /var/www/html/' . $domain . '.g-web.agency && git pull && yarn install && yarn build && yarn generate');
        $ssh->exec('echo "success" > /var/www/html/test.txt');
        break;
    }    

});