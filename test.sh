#!/bin/sh

server_port="80"
open_screenshots=0

usage() {
  cat 1>&2 << EOF
Usage: test.sh [options]

options:
  -p, --port number : Connects to this server port (default 80)
  -h, --help        : Shows this help message
  -s, --screenshots : Opens the screenshot page after tests ran
EOF
}

do_integration_test() {
  cd tests
  rm screenshots/*.png 2>/dev/null
  cd integration
  casperjs test config-default.* ../../tmp/casper-config.coffee test-*
}

do_test() {
  curdir=`pwd`

  cat > tmp/casper-config.coffee << __EOF
    casper.options.port = $server_port
    casper.test.done()
__EOF

  do_integration_test

  cd "$curdir"

  if [ "$?" -ne 0 ]; then
    echo "Integration test failed" 1>&2
    exit 1
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

