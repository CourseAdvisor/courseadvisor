#!/bin/sh

server_port="80"
open_screenshots=0
run_test=all
file=0
integ_ext="coffee"
force=0

usage() {
  cat 1>&2 << EOF
Usage: test.sh [options]

options:
  -p, --port number : Connects to this server port (default 80)
  -h, --help        : Shows this help message
  -s, --screenshots : Opens the screenshot page after tests ran
  -t, --test name   : Specify which test to run (api or integration), default: all
  -m, --start-margarita: Starts the margarita server. See --setup-margarita
  -f, --file name   : Runs this test file only (use with -t, works only with integration tests).
                      Loaded file is "integration/test-{name}.coffee"
  --force           : Run test even if sanity checks failed
  --seed [prompt]   : Prepares the database with fresh test data (prompts for password if prompt parameter present)
  --precompile      : Precompiles coffee integration test files (try this if first test hangs)
  --setup-margarita : Downloads the margarita server, installs the profile and dependencies and exits
EOF
}

do_integration_test() {
  cd tests
  rm screenshots/*.png 2>/dev/null
  cd integration
  others="test-*.$integ_ext"
  if [ $file != 0 ]; then
    others="test-$file.$integ_ext"
  fi
  casperjs test config-default.$integ_ext $others
}

do_api_test() {
  jasmine-node --coffee tests/api
}

setup_margarita() {
  git clone https://github.com/CourseAdvisor/margarita.git
  cd margarita
  npm install
}

stop_margarita() {
  margarita_pid=`cat .margarita.pid 2>/dev/null`
  if [ $? -eq 0 ] && [ ! $margarita_pid -eq "0" ]; then
    kill $margarita_pid
  fi
  rm .margarita.pid
}

start_margarita() {
  if [ -e .margarita.pid ]; then
    stop_margarita
  fi
  cd margarita
  node bin/www &
  margarita_pid=$!
  echo "$margarita_pid" > ../.margarita.pid
  echo "margarita process started with pid $margarita_pid"
  sleep 2
  cd ..
}

cleanup() {
  stop_margarita
}

_do_test() {
  curdir=`pwd`
  echo "Running $1 tests"
  "do_$1_test"
  if [ "$?" -ne 0 ]; then
    echo "$1 test failed" 1>&2
    cleanup
    exit 1
  fi
  cd "$curdir" # restore working directory
}

do_test() {
  # ensures app is not in debug mode
  os=$(expr substr $(uname -s) 1 10)
  if [ "$os" = "MINGW32_NT" ] || [ "$os" = "MINGW64_NT" ]; then #window
    grep "['\"]debug[\"']" app/config/production/app.php | grep -iqw "0\|false"
    status=$?
  else
    echo 'Config::get("app.debug");' | php artisan tinker | grep -q false
    status=$?
  fi

  if [ $status -ne 0 ]; then
    echo "ERR: Tests must run with debug mode off. Please set app.debug to false in your app config file or use --force if you know what you are doing" 1>&2
    if [ $force -ne 1 ]; then
      exit 1
    fi
  fi

  # ensures profiles are up to date
  cp tests/profiles.json margarita/

  # ensures tmp dir
  if [ ! -d tmp ]; then
    mkdir tmp
  fi

  cat > tmp/tests-config.coffee << __EOF
    module.exports =
      port: $server_port
__EOF

  if [ "$run_test" = "all" ]; then
    _do_test integration
    _do_test api
  else
    _do_test "$run_test"
  fi
}

# cross platform open
x_open() {
  if which xdg-open &> /dev/null; then
    xdg-open "$1" # linux
  elif which open &> /dev/null; then
    open "$1"    # mac
  else
    start "$1"  # windows
  fi
}

# clean compiled files before running tests
rm -f tests/integration/*.js

while [ $# -ne 0 ]; do
  case "$1" in
    --help|-h)
      usage
      exit 0
    ;;
    --port|-p)
      shift
      server_port="$1"
    ;;
    --screenshots|-s)
      open_screenshots=1
    ;;
    --test|-t)
      shift
      run_test="$1"
    ;;
    --force)
      force=1
    ;;
    --setup-margarita)
      setup_margarita
      exit $?
    ;;
    --start_margarita|-m)
      start_margarita
    ;;
    --precompile)
      coffee -c tests/integration/*.coffee
      integ_ext="js"
    ;;
    --file|-f)
      shift
      file="$1"
    ;;
    --seed)
      mysql_args=""
      if [ "$2" = "prompt" ]; then
        shift
        mysql_args="-p"
      fi
      echo "Seeding database. Requires to enter sql root password"
      echo "drop database IF EXISTS courseadvisor; create database courseadvisor;" |
        cat - ./app/database/database.sql ./app/database/seeds/testing.sql ./app/database/migrations/*.sql | mysql -uroot $mysql_args

    ;;
    *)
      echo "Unknown parameter: $1"
      cleanup
      exit 1
    ;;
  esac
  shift
done

do_test
if [ "$open_screenshots" -eq 1 ]; then
  x_open tests/screenshots/index.html
fi

cleanup
