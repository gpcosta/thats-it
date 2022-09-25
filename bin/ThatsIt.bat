@echo off

if not exist "config" (
    mkdir config
    echo Created config/
)

if not exist "config/config.php" (
    echo ^<^?php> config/config.php
    echo.>> config/config.php
    echo return [>> config/config.php
    echo    'environment' =^> 'development', // or 'production' when you're ready for it :^)>> config/config.php
    echo    'locationServer' =^> '%cd%\src\Public', // where you put index.php>> config/config.php
    echo    'host' =^> 'your_domain.example.com', // 'http://localhost'>> config/config.php
    echo    'datacenterId' =^> 0, // min of 0 and max of 31 =^> 0 ^<= datacenterId ^<= 31>> config/config.php
    echo    'workerId' =^> 0 // min of 0 and max of 31 =^> 0 ^<= workerId ^<= 31>> config/config.php
    echo ];>> config/config.php

    echo Created config/config.php
)

if not exist "config/database.php" (
    echo ^<^?php> config/database.php
    echo.>> config/database.php
    echo return [>> config/database.php
    echo    "host" =^> "localhost",>> config/database.php
    echo    "port" =^> 3306,>> config/database.php
    echo    "dbName" =^> "any_name",>> config/database.php
    echo    "user" =^> "any_user",>> config/database.php
    echo    "password" =^> "any_password">> config/database.php
    echo ];>> config/database.php

    echo Created config/database.php
)

if not exist "config/router.php" (
    echo ^<^?php> config/router.php
    echo.>> config/router.php
    echo return [>> config/router.php
    echo    "home" =^> [>> config/router.php
    echo        "path" =^> "/{name}",>> config/router.php
    echo        "httpMethods" =^> ["GET", "POST"], // accept "GET", "POST", "PUT" and "DELETE">> config/router.php
    echo        "controller" =^> "App\\Controller\\HomeController",>> config/router.php
    echo        "function" =^> "homeFunction",>> config/router.php
    echo        "parameters" =^> [>> config/router.php
    echo            "name" =^> [], // it means that is a required parameter>> config/router.php
    echo            "age" =^> [>> config/router.php
    echo                "default" =^> 18>> config/router.php
    echo            ]>> config/router.php
    echo        ]>> config/router.php
    echo    ]>> config/router.php
    echo ];>> config/router.php

    echo Created config/router.php
)

if not exist "src" (
    mkdir src
    echo Created src/
)

if not exist "src/Controller" (
    mkdir src\Controller
    echo Created src/Controller/
)

if not exist "src/Public" (
    mkdir src\Public
    echo Created src/Public/
)

if not exist "src/Public/index.php" (
    echo ^<^?php> src/Public/index.php
    echo.>> src/Public/index.php
    echo require_once '../../vendor/autoload.php';>> src/Public/index.php
    echo.>> src/Public/index.php
    echo ThatsIt\EntryPoint\Door::openDoor^(__DIR__^);>> src/Public/index.php

    echo Created src/Public/index.php
)

if not exist "src/View" (
    mkdir src\View
    echo Created src/View/
)

if not exist "src/View/Error" (
    mkdir src\View\Error
    echo Created src/View/Error/
)

if not exist "src/View/Error/error.php" (
    echo ^<^?php> src/View/Error/error.php
    echo.>> src/View/Error/error.php
    echo // this page represents the 'any non treated error' errors>> src/View/Error/error.php
    echo // ^(when something unexpected happen^)>> src/View/Error/error.php
    echo.>> src/View/Error/error.php
    echo // in this page you will have the variable $error available>> src/View/Error/error.php
    echo // this variable will have the message of the error>> src/View/Error/error.php
    echo // this will have the last message that you passed to an exception>> src/View/Error/error.php
    echo // in this page it still exists $statusCode available>> src/View/Error/error.php
    echo.>> src/View/Error/error.php
    echo ^?^>>> src/View/Error/error.php
    echo.>> src/View/Error/error.php
    echo ^<html^>>> src/View/Error/error.php
    echo    ^<div^>>> src/View/Error/error.php
    echo        Error <?php echo $statusCode; ?>!^<br^>>> src/View/Error/error.php
    echo        ^<^?php echo $error; ^?^>>> src/View/Error/error.php
    echo    ^</div^>>> src/View/Error/error.php
    echo ^</html^>>> src/View/Error/error.php

    echo Created src/View/Error/error.php
)