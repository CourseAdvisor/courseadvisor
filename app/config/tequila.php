<?php

return array(
  'redirect_url'     => action('AuthController@loginRedirect'), 
  'server_url'    => 'https://tequila.epfl.ch/cgi-bin/tequila/', 
  'cookie_name'    => 'tequila_token', 
  'cookie_lifetime'  => 60*24*7 // minutes - 1 week
);