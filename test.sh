#!/bin/sh

# Everything happens in the tests folder
cd tests

usage() {
  cat 1>&2 << EOF
Usage: test.sh [options]

options:
  -c, --config name : Runs test with the provided config (as in tests/config-name.*)
  -h, --help        : Shows this help message
  -s, --screenshots : Opens the screenshot page after tests ran
EOF
}

do_test() {
  rm screenshots/*.png 2>/dev/null
  casperjs test config-default.* $1 test-*
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

if [ ! -z "$1" ]; then
  case "$1" in
    --help|-h)
      usage
    ;;
    --config|-c)
      do_test config-$2.*
    ;;
    --screenshots|-s)
      do_test
      x_open screenshots/index.html
    ;;
    *) echo "Unknown parameter: $1" ;;
  esac
else
  do_test
fi
