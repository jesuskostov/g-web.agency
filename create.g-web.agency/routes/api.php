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

    // Create project
    $ssh->exec('cd /var/www/html && mkdir ' . $project . '.g-web.agency');
    // Create index file inside
    $ssh->exec('cd /var/www/html/' . $project . '.g-web.agency && echo "<?php echo \'Hello World!\'; ?>" > index.php');
    // Git status
    echo $ssh->exec('cd /var/www/html/ && git status && git add .');
    echo $ssh->exec('cd /var/www/html/ && git commit -m "from-g-web-server"');
    echo $ssh->exec('cd /var/www/html/ && git push origin');

    // Create subdomain virtualhost and install SSL
    $ssh->exec('cd /var/www/html && cp subdomain.conf /etc/apache2/sites-available/' . $project . '.g-web.agency.conf');
    $ssh->exec('cd /var/www/html && sed -i "s/subdomain/' . $project . '/g" /etc/apache2/sites-available/' . $project . '.g-web.agency.conf');
    $ssh->exec('cd /var/www/html && a2ensite ' . $project . '.g-web.agency.conf');
    $ssh->exec('cd /var/www/html && certbot --apache -d ' . $project . '.g-web.agency');
});
