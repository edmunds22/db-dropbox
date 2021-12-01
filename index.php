<?php

include('./vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;
use Ifsnop\Mysqldump as IMysqldump;

$config = Yaml::parseFile('./schemas.yaml');
if(!$config){
    echo 'failed to parse schemas.yaml'.PHP_EOL;
    die();
}

//dropbox
$authorizationToken = $config['dropbox-key'];
//setup app at: https://www.dropbox.com/developers/apps/create allow: files.content.write and files.metadata.write
//generate a non-expiring auth token under OAuth > Generated access token

//run at 9:15 daily - sudo crontab -e
//15 9 * * * cd /www/dbDropbox && php /www/dbDropbox/index.php >> /www/dbDropbox/cron.log 2>&1

foreach($config['connections'] as $db){
    try {
        $dump = new IMysqldump\Mysqldump(
            'mysql:host='.$db['host'].';dbname='.$db['dbname'], 
            $db['username'], 
            $db['password'],
            [
                'complete-insert' => true,
                'add-drop-table' => true,
                'routines' => true
            ],
            []
        );
        $fname = $db['dbname'].'.sql';
        $dump->start('./dumps/'.$fname);
        createGz($fname);
        unlink('./dumps/'.$fname);

        $client = new Spatie\Dropbox\Client($authorizationToken);
        $dropbox = $client->upload($fname.'.gz', file_get_contents('./dumps/'.$fname.'.gz'), 'overwrite');

        if($dropbox['path_lower']){
            echo 'Added to dropbox';
        }else{
            echo 'Dropbpx error';
        }
        echo PHP_EOL; 

    } catch (\Exception $e) {
        echo 'Connection error error: ' . $e->getMessage();
        echo PHP_EOL; 
    }
}


function createGz($fname)
{
    // Name of the gz file we're creating
    $gzfile = $fname.".gz";

    echo 'Compressing into: '.$gzfile.PHP_EOL;

    // Open the gz file (w9 is the highest compression)
    $fp = gzopen ('./dumps/'.$gzfile, 'w9');

    // Compress the file
    gzwrite ($fp, file_get_contents('./dumps/'.$fname));

    // Close the gz file and we're done
    gzclose($fp);

    return;
}