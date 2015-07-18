port = casper.options.port || 80

config = module.exports =
  BASE_URL: 'http://local.courseadvisor.ch:'+(port || 80),
  url: (path) -> config.BASE_URL + path;