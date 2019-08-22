# --------------------------------------------------
# To run this script (which is a mix of php + bash): 'envoy run local'
# Note: One time only, run:
# composer global require laravel/envoy
# composer global update
# Note: First create your .env file and customize the DB settings in .env:
# cp .env.example .env
# Then, just run command: 'envoy run local'
# (cleans up dummy data from the DB)
#
# --------------------------------------------------
#
@servers(['localhost' => 'localhost'])

########################################################
# SETUP: this (PHP) block is common for all commands
# Note: 'setup' block is always written in PHP
########################################################
@setup
@endsetup

@story('local', ['on' => ['localhost']])
_local_setup
@endstory

@task('_local_setup')
echo "--- Installing on localhost ---"
#php artisan migrate:fresh
#php artisan migrate:reset

composer install
php artisan config:clear
php artisan cache:clear
composer dump-autoload
php artisan view:clear
php artisan route:clear

php artisan migrate
php artisan db:seed
cp .env.testing.example .env.testing
php artisan key:generate
#php artisan passport:install
#php artisan passport:client --personal

#php artisan serve --port=8000
php artisan serve
@endtask
