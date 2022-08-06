[![Build](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml/badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
[![Coverage](https://raw.githubusercontent.com/sroehrl/neoan.io-lenkrad-core/badges/.github/badges/test-badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/1f02189c2a759deecaa3/maintainability)](https://codeclimate.com/github/sroehrl/neoan.io-lenkrad-core/maintainability)
# neoan.io Lenkrad core

This is an experimental core rewrite of neoan and not (yet) meant for production. 
Using modern PHP, we believe it's possible to create easy & static internal APIs without the usual 
problems regarding mocking & injectablility and testability.

## It's modern!

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

Let's compare:
<table>
<tr><th>Eloquent/Laravel</th><th>Lenkrad</th></tr>
<tr><td>Define model:</td><td>Define model:</td></tr>
<tr>
<td>

```php
<?php
...
/**
 * @property $id
 * @property $name
 * @property $email
 * @property $job
 * @property $password
 * @property $createdAt
 * @property $updatedAt
 */
class User extends Model 
{
    protected $primaryKey = 'id';
    protected $attributes = [
        'job' => 'employee'
    ];
}
```
```php
<?php
...
class CreateMyUsersTable extends Migration
{
    public function up()
    {
        Schema::create('Users', 
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('job');
                $table->string('password');
                $table->timestamps();
            });
    }
 
    public function down()
    {
        Schema::dropIfExists('Users');
    }
}
```
</td>
<td>

```php
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

</td>

</tr>
<tr><td>Create & modify entry:</td><td>Create & modify entry:</td></tr>
<tr>
<td>

```php 
$user = User::create([
    'email'=> 'some@mail.com', 
    'name' => 'Someone',
    'password' => '123123'
]);
// reconsider name?
$user->name = 'Adam';
$user->save();

...
// or e.g. when updating a password

$password = $request
                ->input('newPassword');

// I hope you don't forget to hash!
$password = Hash::make($password);

$user = User::where('email', 'some@email.com')
            ->first();
$user->password = $password;
$user->save();
```

</td>
<td>

```php 
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

</td>
</tr>
</table>

As you can see, a lot of overhead can be omitted while maintaining an approachable style.

## Getting Started

`composer require neoan.io/core`

_index.php_
```php
<?php

use Neoan\NeoanApp;
use Neoan\Routing\Route;

require_once 'vendor/autoload.php';
$app = new NeoanApp(__DIR__, __DIR__);

Route::get('/hello-world')
    ->inject(['msg' => 'Hello World']);

$app->run();

```
`php -S localhost:8080 index.php`

## Setup
_soon: working on create-project scripts_

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

Route::get('/', Controller::class)->respond([Response::class,'html']);
// or whatever handler you want:
Route::get('/my-handler', Controller::class)->respond([App\Own\MyResponseHandler::class,'answerMethod'])
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

## Events

## Dynamic Store

## Models

## Migrations

## Testing


## Contribution


