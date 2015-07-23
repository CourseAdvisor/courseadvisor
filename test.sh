#!/bin/sh

server_port="80"
open_screenshots=0
run_test=all

usage() {
  cat 1>&2 << EOF
Usage: test.sh [options]

options:
  -p, --port number : Connects to this server port (default 80)
  -h, --help        : Shows this help message
  -s, --screenshots : Opens the screenshot page after tests ran
  -t, --test name   : Specify which test to run (api or integration), default: all
EOF
}

do_integration_test() {
  cd tests
  rm screenshots/*.png 2>/dev/null
  cd integration
  casperjs test config-default.coffee test-*
}

do_api_test() {
  jasmine-node --coffee tests/api
}

_do_test() {
  curdir=`pwd`
  echo "Running $1 tests"
  "do_$1_test"
  if [ "$?" -ne 0 ]; then
    echo "$1 test failed" 1>&2
    exit 1
  fi
  cd "$curdir" # restore working directory
}

do_test() {
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

# ensures tmp dir
if [ ! -d tmp ]; then
  mkdir tmp
fi

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
    *)
      echo "Unknown parameter: $1"
      exit 1
    ;;
  esac
  shift
done

do_test
if [ "$open_screenshots" -eq 1 ]; then
  x_open tests/screenshots/index.html
fi
