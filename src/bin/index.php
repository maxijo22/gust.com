<?php

// Command-line script

if(php_sapi_name() != 'cli') {
    throw new \Exception("not in cli", 1);
    
}

// Get the command-line arguments
$arguments = $argv;

// Remove the first argument, which is the name of the script itself
array_shift($arguments);

// Check if any arguments were provided
if (!empty($arguments)) {
    $command = array_shift($arguments);

    // Handle "gust create controller" command
    if ($command === 'create' && !empty($arguments) && $arguments[0] === 'controller:') {
        // Extract the controller name
        $controllerName = isset($arguments[1]) ? $arguments[1] : '';

        if ($controllerName !== '') {
            $controllerName = ucfirst($controllerName);
            // Create the controller
            echo "Creating controller: $controllerName\n";
            $dirName = dirname(__DIR__);
            $filePath = $dirName . '\\Http\controllers\\' . $controllerName . '.php';

            if (file_exists($filePath)) {
                exit("controller :" . $conrollerName . "already exits");
            }

            if (file_put_contents($filePath, controllerString($controllerName))) {
                exit($controllerName . "Has been created successfully");
            }


            die('failed');

            //  echo "created successfully";
            // Add your code here to create the controller using the provided name
        } else {
            echo "Error: Please provide a controller name.\n";
        }
        exit;
    }
}


function controllerString($controllerName)
{
    $str = '<?php namespace App\Http\Controllers;
    use Myframework\Http\Response;

    class' . " $controllerName" . '
    {
        public function index(Response $response) {
            $response->withHtml("<h1>Our first command line controller</h1>");
        }

    }';

    return $str;


}

// Display usage instructions if the command is not recognized or arguments are missing
echo "Usage: php cli.php create controller: controllerName\n";