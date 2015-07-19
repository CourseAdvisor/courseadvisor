#!/bin/sh


usage() {
  cat 1>&2 << EOF
Usage: test.sh [options]

options:
  -c, --config name : Runs test with the provided config (as in tests/config-name.*)
  -h, --help        : Shows this help message
EOF
}

do_test() {
  cd tests
  casperjs test config-default.* "$1" test-*
}

if [ ! -z "$1" ]; then
  case "$1" in
    --help|-h)
      usage
    ;;
    --config|-c)
      do_test config-$2.*
    ;;
    *) echo "Unknown parameter: $1" ;;
  esac
else
  do_test
fi