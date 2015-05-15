CourseAdvisor
=============

## Setup

This section describes how to setup a development environment.

### Requirements
- WAMP/LAMP
- nodejs + npm
- gulp (`npm install -g gulp`)

### Step-by-step

1. clone the repository
2. **In the repository folder** download [composer.phar](https://getcomposer.org/download/) `curl -sS https://getcomposer.org/installer | php`
3. run `npm install`
4. publish the assets using `gulp publish`
5. configure your database (see below)

### Database configuration

`cp app/config/database.php app/config/production/database.php`
Enter your database specific configuration in app/config/production/database.php


## Documentation

### Laravel
Documentation for the entire framework can be found on the [Laravel website](http://laravel.com/docs).
**All issues and pull requests should be filed on the [laravel/framework](http://github.com/laravel/framework) repository.**
The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


### Gulp tasks
All front-end related files go into assets/. This file **is not served over http**. Instead, gulp tasks take care to compile/minify/bake/whatever your files and put them in the public/ folder.

#### `gulp watch` (default)
This task will watch for changes in the assets/ dir and update the public/ dir accordingly

#### `gulp publish`
Publishes everything into the public/ folder. Assets that must be compiled are compiled by the way.

#### `gulp clean`
Cleans the public/ directory. This selectively removes generated files from public/ (It is not equivalent to `rm -rf public/` and this command should not be run).


### artisan commands

#### `php artisan ide-helper:generate`
Generates a file for IDE autocompletion and stuff. Works well with phpstorm for instance.

