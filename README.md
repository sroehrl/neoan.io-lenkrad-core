[![Build](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml/badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
[![Coverage](https://raw.githubusercontent.com/sroehrl/neoan.io-lenkrad-core/badges/.github/badges/test-badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/1f02189c2a759deecaa3/maintainability)](https://codeclimate.com/github/sroehrl/neoan.io-lenkrad-core/maintainability)
# neoan.io Lenkrad core

This is an experimental core rewrite of neoan and not (yet) meant for production. 
Using modern PHP, we believe it's possible to create easy & static internal APIs without the usual 
problems regarding mocking & injectablility and testability.

## It's modern!

At a glance:

```php
// A MODEL
<?php
...
class User extends Model 
{
    
    #[IsPrimary]
    public readonly $id;
    
    public ?string $name = null;
    
    #[IsUnique]
    public string $email;
    
    public string $job = 'employee';
    
    #[Transform(Hash::class)]
    public string $password;
    
    use TimeStamps;
    use Setter;
}
```


```php 
// A CONTROLLER
<?php
...
$user = new User([
    'email'=> 'some@mail.com', 
    'name' => 'Someone',
    'password' => '123123'
]);
// reconsider name?
$user->name = 'Adam';
$user->store();

...
// or e.g. when updating a password

$user = User::retrieveOne([
            'email' => 'some@email.com'
        ]);

// Don't worry! Hashing for this property 
// is always ensured by the model
[ 
    'newPassword' => $user->password 
] = Request::getInputs();
$user->store();
```


As you can see, a lot of overhead can be omitted while maintaining an approachable style.

You will need PHP 8.1 & composer2 to run this

- [Why yet another framework?](#why)
- [Getting Started](#getting-started)
- [Setup](#setup)
- [Routing](#routing)
- - [Methods](#http-methods)
- - [Endpoint Parameters](#endpoint-parameters)
- - [Routable classes](#routables)
- - [Middleware / Chaining](#chained-routes)
- - [Response Handling](#response-handler)
- - [Injection](#inject)
- - [Views](#views)
- [Inputs & Outputs](#handling-inputs--outputs)
- [Templating](#templating)
- - [HTML skeleton](#skeleton)
- - [Templating basics](#templating-basics)
- [Events](#events)
- [Dynamic Store](#dynamic-store)
- [Models](#models)
- [Migrations](#migrations)
- [Testing](#testing)
- [Contribution](#contribution)

## Why?

PHP has come a long way. Most frameworks focus on backward-compatability to allow
existing code-bases to securely update framework security patches without breaking changes.
However, if you are starting a project, **why would you forgo the power of modern PHP?**

Do you realize what a framework could do for you, if it utilized

- attributes
- enums
- intersection types
- match expressions
- named parameters
- readonly properties
- union types
- ...

Execution time would be way faster, but could it make your life easier?



## Getting Started

`composer require neoan.io/core`

_index.php_

```php
<?php

use Neoan\NeoanApp;use Neoan\src\Routing\Route;

require_once 'vendor/autoload.php';
$app = new NeoanApp(__DIR__, __DIR__);

Route::get('/hello-world')
    ->inject(['msg' => 'Hello World']);

$app->run();

```
`php -S localhost:8080 index.php`

## Setup
_soon: working on create-project scripts_

This readme will guide you to an understanding of your personal needs. For the impatient - and as a cheat sheet,
find a basic setup script:

`composer require neoan.io/core`

`composer require neoan.io/legacy-db-adapter` (optional: You can also use Neoan\Database\SqLiteAdapter while developing)

You are free to chose your folder structure. For now, we will assume the following structure:

```
project
+-- public
|   +-- index.php
+-- src
|    +-- Attributes
|    +-- Cli
|    +-- Config
|    |    +-- Setup.php
|    +-- Controllers
|    +-- Middleware
|    +-- Models
|    +-- Routes
|    |   +-- HtmlRoutes.php
|    +-- Views
|        +-- main.html
|        +-- home.html
+-- vendor
+-- cli
+-- composer.json
```
Utilizing the following PSR namespace definition in our `composer.json`:

```json 
"autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
```
### /cli
```php 
#!/usr/bin/env php
<?php
// the first line is necessary if we don't use the extension ".php"!
// this file load our cli capabilities and is exposed to
// allow advanced users to integrate own commands (based on symfony console)

use App\Config\Config;
use Neoan\Cli\Application;
use Neoan\NeoanApp;


require_once 'vendor/autoload.php';

$app = new NeoanApp(__DIR__, __DIR__);
new Config($app);
$console = new Application($app);
$console->run();
```
### /public/index.php
```php 
use App\Config\Setup;
use App\Routes\HtmlRoutes;
use Neoan\NeoanApp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$srcPath = dirname(__DIR__) . '/src';
$publicPath = __DIR__; // where this very script runs

$app =  new NeoanApp($srcPath, $publicPath);
new Setup();
new HtmlRoutes();
$app->run();

```
### /src/Routes/HtmlRoutes.php
```php 
namespace App\Routes;


class HtmlRoutes {
    function __construct()
    {
        Routes::get('/')->view('home.html');
    }
}

```

### /src/Config/Setup.php
```php 
namespace App\Config;

use Neoan\Database\Database;
use NeoanIo\MarketPlace\DatabaseAdapter;
use Neoan\Helper\Env;
use Neoan\Response\Response;
use Neoan\Render\Renderer;

class Setup {
    function __construct()
    {
        // Database setup
        $dbClient = [
            'host' => Env::get('DB_HOST', 'localhost'),
            'name' => Env::get('DB_NAME', 'neoan_io'),
            'port' => Env::get('DB_PORT', 3306),
            'user' => Env::get('DB_USER', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'casing' => Env::get('DB_CASING', 'camel'),
            'assumes_uuid' => Env::get('DB_UUID', false)
        ];
        Database::connect(new DatabaseAdapter($dbClient));
        
        // Defaults
        Response::setDefaultOutput(ResponseOutput::HTML);
        Renderer::setTemplatePath('src/Views');
    
    }
}

```



## Routing
Registering routes is easy and intuitive:

```php 
use Neoan\Routing\Route;

Route::request(string $httpMethod, string $endpoint, Routable ...$classes);
```

### HTTP-Methods
Simply use the method keyword to register a route for shorthand syntax.

the following methods are currently implemented:
- get
- post
- put
- patch
- delete

example:
```php 
use Neoan\Routing\Route;

Route::get(string $endpoint, Routable ...$classes);
```
### Endpoint-Parameters
Endpoints can handle parameters with the ":"-notation like so:

```php 
use Neoan\Routing\Route;

Route::get('/users/:id')
    ...
```
This will match a call to "/users/12" and expose the value (here "12") to the
Request (see [Handling inputs & outputs](#handling-inputs--outputs))

### Routables
You can chain as many classes as you wish into the route (middleware). Classes **must** implement the Routable Interface
and an invoke function and return one of the following types:
- array
- string
- Neoan\Model\Collection
- Models | Neoan\Model\Model

example:

```php 
namespace App\Controllers;

use Neoan\Routing\Routable;

class Controller implements Routable
{

    public function __invoke(array $provided): array
    {
        return ["msg" => "Hello World"];
    }
}
```
```php 
use Neoan\Routing\Route;

Route::get('/', App\Controllers\Controller::class)
```
### Chained routes
The return value of a previously executed class is exposed to the next classes. 

Let's assume the following middleware:

```php 
namespace App\Middleware;

use Neoan\Errors\Unauthorized;
use Neoan\Routing\Routable;
use Neoan3\Apps\Stateless;

class NeedsAuth implements Routable
{
    public function __invoke(array $provided = []): array
    {
        try{
            return ['auth' => Stateless::validate()];
        } catch (\Exception $e) {
            new Unauthorized();
        }
    }
}
```
With the following route:

```php 
use Neoan\Routing\Route;
use App\Middleware\NeedsAuth;
use App\Controllers\Controller;

Route::get('/', NeedsAuth::class, Controller::class)
```
In this scenario, the returned array gets passed into our controller-class, overwriting potentially previously set values of "auth".
Our controller never gets executed if the authorization wasn't successful, as the error "Unauthorized" terminates execution.
If we are authorized, however, we now have "auth" available to us:

```php 
namespace App\Controllers;

use Neoan\Routing\Routable;

class Controller implements Routable
{

    public function __invoke(array $provided): array
    {
        ['auth' => $auth] = $provided;
        // better not do that?
        return ["token-payload" => $auth];
    }
}
```
### Response handler
By default Routes are resolved as having the built-in JSON response. 
However, you either change the default behavior:

```php 
use Neoan\Response\Response;
use Neoan\Enums\ResponseOutput;
Response::setDefaultOutput(ResponseOutput::HTML)
```
Or use a route-specific output handler:
```php 
use Neoan\Routing\Route;
use Neoan\Response\Response;
use App\Controllers\Controller;

Route::get('/', Controller::class)->response([Response::class,'html']);
// or whatever handler you want:
Route::get('/my-handler', Controller::class)->response([App\Own\MyResponseHandler::class,'answerMethod'])
```
### Inject
To make life easy where it can be, you can directly inject values into a route:

```php 
use Neoan\Routing\Route;
use App\Controllers\Controller;

Route::get('/', Controller::class)->inject(['title'=>'my_app']);
```
This will provide values to all listed classes and can often substitute middleware.

### Views
The default templating engine used is "neoan3-apps/template", a battle-tested solution.
To set up a location for your views, first define the general directory. E.g.

```php 
use Neoan\Render\Renderer;

Renderer::setTemplatePath('src/Views');
```
Then, you can use relative paths when routing:

```php 
use Neoan\Routing\Route;

Route::get('/')
    ->response([Response::class,'html'])
    ->inject(['user' => ['firstName' => 'Sam']])
    ->view('/home.html');
```

```html
<!-- home.html -->
<p>{{user.firstName}}</p>
```

Learn more about [templating in neoan.io lenkrad](#templating) or [the neoan3-apps/template template engine](https://github.com/sroehrl/neoan3-template#neoan3-appstemplate)


## Handling inputs & outputs
Input handling is very intuitive. The "Request" class carries (among others) the following methods for your convenience:

- getInput(string $name): string
- getInputs(): array
- getQuery(string $name): string
- getQueries(): array
- getParameter(string $name): string
- getParameters(): array

```php

Route::get('/api/users/:id', UserShowController::class);
Route::post('/api/user', UserCreateController::class);
```
UserShowController:
```php 
// call: GET:/api/users/1?some=value
...
public function __invoke(array $provided): array
    {
        return [
            'queryValues' => Request::getQueries(), // outputs ['some' => 'value']
            'userId' => Request::getParameter('id') // outputs "1"
        ];
    }
...
```
UserCreateController:
```php 
// call: POST:/api/user payload: {"userName":"Tobi"}
...
public function __invoke(array $provided): array
    {
        ['userName' => $userName] = Request::getInputs();
        return [
            'user' => $userName // outputs "Tobi"
        ];
    }
...
```
## Templating
The default templating engine is attached to the default Renderer. Both can be exchanged,
but for now let's focus on the built-in tooling.

We will only cover basics of the templating engine here, please refer to [the repository of neoan3-apps/template](https://github.com/sroehrl/neoan3-template#neoan3-appstemplate)
for deeper understanding.

To set your default template path, use

```php 
use Neoan\Render\Renderer;

Renderer::setTemplatePath(string $path);
```
Note: By default, the template engine uses project path.
Using "setTemplatePath" overwrites that value. This means you have to declare the path relative to your project-root.
Let's look at a setup example:

```shell
/public_html 
  /index.php    
/src
  /Models
  /Views      <- This is where we want to store our views
  /Controller
/vendor       <- Hint: always define from the "vendor" folder's parent on
...           
```
In the above scenario, setting our template path would be:
```php 
use Neoan\Render\Renderer;

Renderer::setTemplatePath('src/Views');
```
### Skeleton

```php 
use Neoan\Render\Renderer;

Renderer::setHtmlSkeleton(string $templatePath, string $routePlacement, array $renderVariables)
```
To simplify the most common scenario, the Renderer uses a "skeleton" to surround your component specific views.
This skeleton can be seen as a shell or frame and often includes header & footer.

example:

```html
<!-- /src/Views/main.html -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{title}}</title>
</head>
<body>
<header>
    <nav><!--...--></nav>
</header>
{{routePlacement}}
</body>
</html>
```
We can now set this file to be our skeleton:

```php 
use Neoan\Render\Renderer;
use Neoan\Store\Store;

Renderer::setHtmlSkeleton('src/Views/main.html','routePlacement',[
    'title' => Store::dynamic('title'), // 'title' isn't set at this point, so we use the dynamic store
    'webPath' => $app->webPath          // neoan instance relative webPath in case we need it
])
```
To complete the example, we'll create a view & route
```html 
<!-- /src/Views/you.html -->

<p>I am here with <strong>{{you}}</strong></p>
```
```php 
use Neoan\Routing\Route;
use Neoan\Response\Response;
use Neoan\Enums\ResponseOutput;
use App\YouClass;

Response::setDefaultOutput(ResponseOutput::HTML);
Route::get('/test/:you', YouClass::class)->view('/you.html');
```
```php 
use Neoan\Store\Store;
use Neoan\Routing\Routable;
use Neoan\Request\Request;

class YouClass implements Routable{

    public function __invoke($injected = []): array
    {
        Store::write('title','you-route');  // write to dynamic store
        return Request::getParams();        // we know this includes "you"
    }

}
```
The output when visiting **/test/Eve** would be
```html 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>you-route</title>
</head>
<body>
<header>
    <nav><!--...--></nav>
</header>
<p>I am here with <strong>Eve</strong></p>
</body>
</html>
```

### Templating basics
You have already seen the general markup with curly brackets `{{var}}`. 
A few pointers for common tasks, assuming the following PHP output
```php 
 ...
 return [
    'deep' => [
        'key' => 'one'
    ],
    'iterateMe' => [
        ['name' => 'Sam'],
        ['name' => 'Adam']
    ]
 ];
 ...
```

#### Nested variables
<table>
<tr>
<td>

```html 
<p>{{deep.key}}</p>
```
</td>
<td>

```html 
<p>one</p>
```

</td>
</tr>
</table>

#### Iterations
<table>
<tr>
<td>

```html 
<div>
    <p n-for="iterateMe as item">{{item.name}}</p>
</div>
```
</td>
<td>

```html 
<div>
    <p>Sam</p>
    <p>Adam</p>
</div>
```

</td>
</tr>
</table>

#### Conditionals
<table>
<tr>
<td>

```html 
<div>
    <p n-if="deep.key !== 'one'">Show me</p>
</div>
```
</td>
<td>

```html 
<div>
    
</div>
```
</td>
</tr>
</table>

## Events
Events are a useful tool to control and abstract logic and offer a clean way of adding custom functionality.
The core itself uses system events (GenericEvents) for debugging & testing, so listening or dispatching them
does not yield side effects during runtime:

- DATABASE_ADAPTER_CONNECTED
- BEFORE_DATABASE_TRANSACTION
- AFTER_DATABASE_TRANSACTION
- BEFORE_RENDERING
- REQUEST_HANDLER_INITIALIZED
- REQUEST_HEADERS_SET
- REQUEST_INPUT_PARSED
- ROUTE_HANDLER_INITIALIZED
- ROUTE_REGISTERED
- RESPONSE_HANDLER_SET
- BEFORE_RESPONSE
- ROUTE_INJECTION
- BEFORE_ROUTABLE_EXECUTION

We use common terminology for the methods:

```php 
use Neoan\Event\Event;

Event::on('log', function($event){
    $data = [
        'time' => time(),
        'event' => $event
    ];
    file_put_contents(dirname(__DIR__,2) . '/log.txt', json_encode($data), FILE_APPEND);
});

...
// somewhere else
try{
    ...code
} catch(\Exception $e){
    Event::dispatch('log', $e->getMessage());
}

```

In addition to this functionality, you can listen to notifications fired by Routable & Model classes with
`Event::subscribeToClass(string $className, callable $closureOrInvokable)`.

If you want to extend what classes you can listen to, simply implement Neoan\Event\Listenable in this way:

```php 
use Neoan\Event\Event;
use Neoan\Event\Listenable;
use Neoan\Event\EventNotification;

class AnyClass implements Listenable
{
    private EventNotification $notifier;
    function __construct()
    {
        $this->notifier = Event::makeListenable($this);
    }
    function doSomething(string $value)
    {
        ...
        $this->notifier->inform($value);
    }
}
```
This is useful especially when chaining middleware and you want to react to outcomes that haven't happened yet.

## Dynamic Store
The static store object is an integral part of the design decision. 
It functions as a "free-for-all" global memory used by the core itself and is 
fully exposed to user land.
One of it's core competencies is the ability to use not yet initiated values
without the syntactical overhead of event listeners.

```php 
use Neoan\Store\Store;
$totalRuntime = Store::dynamic('totalRuntime');
$start = time();
for($i = 0; $i <2; $i++){
    echo $totalRuntime; // first iteration: null, second iteration: ~ 1
    sleep(1);
    Store::write('totalRuntime', time() - $start);
}
echo $totalRuntime; // ~ 2
```
In practice, this allows us to use variables that will be set eventually to be used in code,
creating a promise-like structure without the requirement for actual asynchronous behavior. 
## Models
Modern MVC frameworks use object-relational mapping (ORM) to interact with data.
While neoan.io lenkrad is no different, the possibilities of PHP have finally grown to the point where
manual mappings of database-structure and runtime-object are no longer required if done correctly. 

### Database setup
This package does not yet ship with a default database adapter. 
For now, mysql & mariadb connectivity is created with the package neoan.io/legacy-db-adapter.

`composer require neoan.io/legacy-db-adapter`

Please refer to [Setup](#setup) in this readme or to [neoan3-apps/db](https://github.com/sroehrl/neoan3-db) for
setup instructions and deeper understanding.

### Model basics
At its core, a model is a single object that inherits the capabilities of the core model class.

example:
```PHP 
namespace App\Models;

use Neoan\Model\Model;
use Neoan\Helper\DateHelper;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Attributes\IsPrimaryKey;
use Neoan\Model\Attributes\IsUnique;
use Neoan\Model\Attributes\Ignore;
use Neoan\Model\Attributes\Type;
use Neoan\Model\Collection;
use Neoan\Model\Traits\TimeStamps;

class MovieModel extends Model {
    // primary keys can either be UUIDS or auto-incremented integers
    // as our database setup refused the assumption of UUIDS, integers it is! 
    // every model needs a primary key, which is indicated by the attribute "IsPrimaryKey"
    
    #[IsPrimaryKey]
    public int $id;
    
    // Can there be two movies with the same name? Let's decide no:
    // The "IsUnique" attribute let's the auto-migration know that we are serious about this decision.
    
    #[IsUnique]
    public string $name;
    
    // Let's go crazy: What if wanted a type that cannot be inferred as it isn't built in?
    // We are going to need to worry about two things: 
    // First, the database type shouldn't default to string (or varchar, in our case), 
    // so we define it using the "Type" attribute
    // Additionally, we would like our model to assume the current date when a model is created,
    // so we initialize a Datehelper instance on creation.
    
    #[
        Type('date',null),
        Initialize(new DateHelper())
    ]
    public string $releaseDate; 
    
    // Just to lighten up the attribute-overload, let's create a regular field
    // Since it has the type string it will default to a (short-)string data type (e.g. varchar(255)
    
    public string $studio;
    
    // What about relations?
    // there is more than one review for a given movie, so we attach ReviewModel instances in a
    // collection (see Collections) to the property $reviews based on the ReviewModel's foreign key
    // "movieId" which points to our primary key "id"
    
    #[HasMany(ReviewModel::class, ['movieId' => 'id'])]
    public Collection $reviews;
    
    // I don't know what I need it for, but the following property is ignored by database transactions
    // and only serves for us to store values.
    
    #[Ignore]
    public string $aProperty = 'new';
    
    // Traits can be useful to fight repetition. This packaged trait delivers us the properties
    // - createdAt (a timestamp filled at creation of the Model)
    // - updatedAt (a timestamp that is filled whenever a Model is stored to the database) and
    // - deletedAt (a timestamp allowing soft deletion)
    use TimeStamps;
}    
```
Just to be complete, this is how our ReviewModel would look like:
```php 
namespace App\Models;

Neoan\Model\Traits\Setter;
Neoan\Model\Model;
use Neoan\Model\Attributes\IsPrimaryKey;
use Neoan\Model\Attributes\IsForeignKey;
use Neoan\Model\Traits\TimeStamps;

class ReviewModel extends Model{
    
    // Young devs in your team?
    // It's probably smart to set the primary key to "readonly" to protect your padawans
    // from stupid ideas. However, this requires the model itself to initialize the 
    // property after database hydration. To automate this process, use the trait "Setter"
    
    use Setter;
    
    #[IsPrimaryKey]
    public readonly int $id;
    
    // Who cares about critics?
    // Let's make this field nullable
    
    public ?string $author = null;
    
    // We are using the attribute "Type" again.
    // this time, we skip the length but nclude a default
    
    #[Type('MEDIUMTEXT', null, 'Awesome')]
    public string $content;
    
    // Remember our model "Movie"? 
    // While we don't need to declare this as foreign key,
    // we might want to speed up database queries once our cinema bursts with visitors
    
    #[IsForeignKey]
    public int $movieId;
    
    use TimeStamps;
    
    // Want to make your despise for critics known to whoever has to write raw
    // queries? Name your table however you like instead of being base on the model name.
    
    const tableName = 'ticks';
    
}
```
We are going to jump ahead here to actually make this example work:

`php cli migrate:mysql App\Model\MovieModel` &
`php cli migrate:mysql App\Model\ReviewModel` 
#### Creation
To create a new record you simply store an instance of a model.
```php 
...
// either initialte with an assoc array
$movie = new MovieModel([
    'name' => 'The Matrix'  
]);

// or set the individual property
$movie->studio = 'Warner Bros.'; 

// If you are ready to store the movie to the database (and rehydrate), run store()
$movie->store();

// This will now include an "id" 
return $movie; 

```
**About security**: The combination of using prepared statements as well as assignment guards makes it
secure (and convenient) to handle user input:

```php 
...
$movie = new MovieModel(Request::getInputs());
try{
    $movie->store();
} catch (\Exception $e) {
    // required field missing || validation failed || etc
}
```
Model classes automatically which modes between existing and new entries. If you run into edge-cases,
you can change the mode manually:
```php 
...
// The following is NOT recommended in our scenario!
// This is only to show you the possibilities

$movie = new MovieModel();

// will return Neoan\Enums\TransactionType::INSERT
$mode = $move->getTransactionMode(); 
$movie->setTransactionType(TransactionType::UPDATE); 
```
#### Retrieve & update
If you want to modify existing records, we want to get the existing record first:
```php 
// sometimes I know the primary id ...
$matrix = MovieModel::get(1); 

// ... but often I lookup based on what I know
$matrix = MovieModel::retrieveOne([
            'name' => 'The Matrix'
          ]); 

// Let's fix the name
$matix->studio = 'Warner Bros. Pictures'

// Then simply store again
$matrix->store();
```

#### Collections
Collections are a useful tool to manage multiple instances at once. Whenever you are retrieving more than one 
record, a Collection will be returned.

Collections are iterable and have the following additional capabilities:
```php 
...
// First, lets retrieve multiple records
// Instead of "retrieveOne" we will use "retrieve"
// Additionally, we account for soft deleted records and 
// want to ignore them by adding a condition to our retrieval 
$allMovies = MovieModel::retrieve(['deletedAt' => null]);

// Collections are iterable
foreach($allMovies as $movie){
    ...
}

// However, it would be a shame if our modern IDE couldn't 
// help us with existing properties. So let's use "each" instead
$allMovies->each(function(MovieModel $movie, int $iteration){
    ...
});

// Did you do something to all the records there?
// Let's save all selected movies at once
$allMovies->store();

// While you can return collections directly, 
// you might need to convert them to an array
$flat = $allMovies->toArray();

// Didn't find what you are looking for?
// Just add to the existing collection
$allMovies->add(new MovieModel(['name' => 'Alien']))
```
## Migrations
You might have noticed that there aren't any files handling migrations.
Instead, the cli compares the existing table with your model definition and makes 
updates accordingly. However, what happens on the database does not have to be invisible to you.
The basic command `migrate:mysql $modelQualifiedName` has additional options:

- with-copy (c)
- output-folder (o) 

example:
```shell 
php cli migrate:mysql App\Models\MovieModel -o migrations -c movie_backup
```
This will output the database operations to a sql-file (in our case /src/migrations)
and create a copy of the table named "movie_backup" before any altering commands are executed.

_NOTE: the output folder must exist under the NeoanApp->appPath_

## Testing
_soon_
## CLI
The cli is based on symfony/console wrapped in a container which makes neoan.io lenkrad available to scripts.
As such, you can add your own symfony console commands to the suggested file `cli` as you normally would:
```php 
#!/usr/bin/env php
<?php
...
$console = new Application($app);
$console->add(new MyOwnCommand($app));
...
```
To see available commands:
```shell 
php cli list
```

## Contribution

For now we are looking for feedback only as marketplace rules and fundamentals aren't written
in stone yet. However, please star, commend issue tickets to help us build out and improve this
lightweight solution.
