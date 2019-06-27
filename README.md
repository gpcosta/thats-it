# ThatsIt
## A simple framework in PHP for personal projects. Easy to use and... That's It!

Imagine that you love create things and you also love using PHP... What do you do nowadays to start your projects?
Or you start just with vanilla PHP or you use one of the many frameworks that although they are great, they are heavy and most of the times they have many features that are overkill.

So, because I have a lot of free time (just kidding), I started my own framework. The first (maybe?) VC framework in the world, because I only need controllers and views. Why not?

Requirements
--
- You must have at least PHP 7.2
- Composer installed
- if you want to use a DB, MySQL is the only one accepted (currently there are no support for other DBs)

If you want to use it, download it from this repository and then, to download all the dependencies (fastRoute, whoops and monolog), in a shell, in the project root folder, write

```bash
composer install
```

After that, in the shell, to verify/build the structure of your project just write

```bash
php thatsIt.php verify
```

This command will create all files and folders that are needed to start your project.
At this point, you can go to src/Public where you will see an index.php.
This file is the application entry point.

Now, you can start a PHP server with

```bash
php -S localhost:8000
```

Your app is running and if you go to your browser and type localhost:8000, you see a simple 404 page. So what to do now?

Structure of folders and files
--
For you, it just matters config and src folder.

In config folder, you will see 3 files:

- config.php (where you will say the path to server and the current environment)
- database.php (where you put all the information to access to the database)
- router.php (where you will tell the app how your routes will be)

These files are created by the framework and they are already set for you to use.

In src folder, you will find:

- Controller folder (where you will write your controllers)
- Public folder (where your entry point is and where your accessible files from the outside should be as .js, .css and images)
- View folder (where you will put your views and your templates)

Just Views and Controllers
--
In this framework, a controller is the component that will receive the request, do all the "heavy lifting" (like requests to DB and so) and
a view is the component that will receive the data and will show it to the user.

If you are like me, you don't like the "dark magic" (aka "lazy loading") that ORMs do so you can forget them and also models in this framework.

In case you want to have business logic separated of the controller, you can just create a folder to put that.
Don't forget to add this folder and its namespace in the composer.json of the project.

Working Example
--
#### Routes

Imagine that you have this config/router.php file

```php
<?php

return [
    "home" => [
        "path" => "/{name}",
        "httpMethods" => ["GET", "POST"], // accept "GET", "POST", "PUT" and "DELETE"
        "controller" => "App\\Controller\\HomeController",
        "function" => "homeFunction",
        "parameters" => [
            "name" => [
                "default" => "Generic User"
            ],
            "age" => [
                "default" => 18
            ]
        ]
    ]    
];
```

This means that for the path "/{name}", you will have a route with the name "home"
that will answer to GET and POST requests. The controller and function that will take care of these requests is
"App\\Controller\\HomeController" and "homeFunction", respectively.

This function will receive as parameters $name and $age.

#### Controller

Now, the controller should be like:

```php
<?php

namespace App\Controller;

use ThatsIt\Controller\AbstractController;
use ThatsIt\Response\HttpResponse;
use ThatsIt\Response\View;

class HomeController extends AbstractController
{
    function homeFunction(string $name, int $age): HttpResponse
    {
        // create View that will show the test view
        $view = new View("test");
        // add $name to the view that will be available as $userName in the view
        $view->addVariable("userName", $name);
        // add $age to the view that will be available as $age in the view
        $view->addVariable("age", $age);
        
        return $view;
    }
}
```

In this code, you can see that you receive $name and $age variables in the function of the controller you provided in config/router.php.
Inside the function, it's created a View that will show the view with the name of "teste" and it's added to that View two variables.
In the end, an HttpResponse has to be returned. View and JsonResponse are currently the only classes that inherit from HttpResponse.
You can also create more if you want.

You should place this file in src/Controller as HomeController.php.

In this code, HomeController extends AbstractController, but it doesn't need that.
But it will be good all your controllers inherit from AbstractController because it will provide:

- access to database (with $this->getPDO() - see https://www.php.net/manual/en/book.pdo.php for more informations about PDO)
- access to the HttpRequest (with $this->getRequest())
- access to all the routes (with $this->getRoutes())
- access to a logger (with $this->getLogger())

#### View

In your view, you will write HTML, CSS, JS and also PHP. For example:

```php
<html>
    <head>
        <style>
            /* css code */
        </style>
    </head>
    <body>
        <div>
            <b>Home page</b>
        </div>
        <div>
            Hello To ThatsIt, <?php echo $userName; ?>
        </div>
        <div>
            You're <?php echo $age; ?> years old
        </div>
        
        <script>
            // js code
        </script>
    </body>
</html>
```

I think the code is self explanatory. You can write your view with HTML and print your variables with PHP.
Of course that you will be able to use PHP at its full potential.

You have to save this file as test.php in src/View.
If you want to save it with another name, you have to change the name in controller, in the parameter passed to the View constructor.

You can also save it inside of src/View/Other_folder.
If you do, you also have to change in the parameter passed to the View constructor (like "Other_folder/test").

## That's It?
Now, if you start your server in src/Public with

```php
php -S localhost:8000
```

You can go to localhost:8000/{put your name here} in your browser and see the result of your code.
As you can see, your name is displayed on the screen. If you want to change the "age" that appears
in the screen, you can go to

```
localhost:8000/{put your name here}?age={put your age here}
```

Your name is still there and now your age is also there.

**And That's It.**

## Why did I do ThatsIt framework?

- First of all, I like to control my data. So I prefer to make my own queries, rather trust in ORMs and all the lazy loading they usually do.
- Second, I always thought that PHP is already awesome as a "solo actor".
I just don't like having to always be checking whether variables exist or not or what type they have (regarding $_GET and $_POST variables)
- Third, I like the division between the data processing and display part. So all I need is controllers and views
- Fourth, this is a good exercise to know what are the fundamentals behind a framework (a real simple one)

**Note**: I want to thank [Patrick Louys](https://github.com/PatrickLouys) for the [HTTP library](https://github.com/PatrickLouys/http) he made available on GitHub,
which I heavily used to make my own library with few corrections and adaptations, and also for the [No Framework Tutorial](https://github.com/PatrickLouys/no-framework-tutorial).
Although, ThatsIt framework is not based in this tutorial, it was a good resource to learn great tools already available and to see good principles.
I recommend it to anyone that wants to understand the "behind the scenes" of a framework.