[![Build](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml/badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
[![Coverage](https://raw.githubusercontent.com/sroehrl/neoan.io-lenkrad-core/badges/.github/badges/test-badge.svg)](https://github.com/sroehrl/neoan.io-lenkrad-core/actions/workflows/php.yml)
# neoan.io Lenkrad core

This is an experimental core rewrite of neoan and not (yet) meant for production. 
Using modern PHP, we believe it's possible to create easy & static internal APIs without the usual 
problems regarding mocking & injectablility and testability.

## It's modern!

You will need PHP 8.1 & composer2 to run this

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

?

Let's compare:
<table>
<tr><th>Eloquent</th><th>Lenkrad</th></tr>
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
        Schema::create('Users', function (Blueprint $table) {
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

$password = $request->input('newPassword');

// I hope you don't forget to hash!
$password = Hash::make($password);

$user = User::where('email', 'some@email.com')->first();
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

$user = User::retrieveOne(['email' => 'some@email.com']);

// Don't worry! Hashing for this property is always ensured by the model
[ 'newPassword' => $user->password ] = Request::getInputs();
$user->store();
```

</td>
</tr>
</table>

- auto migration
- easy scaffolding

## At a glance

### Hello world example:

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

## Getting started

## Setup

## Routing

## Handling inputs & outputs

## Events

## Dynamic Store

## Models

## Migrations

## Testing


## Contribution


