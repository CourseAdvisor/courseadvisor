
// TO MOVE



/********************************************************
          DO NOT EDIT UNDER THIS LINE
********************************************************//*
function GetConfigOption($sOption, $sDefault = '') {
  $aConfig = array (
	          'sServer' => 'https://tequila.epfl.ch',
	          'iTimeout' => 86400,
			  'logoutUrl' => "https://localhost/tequila/logout.php",
	);
  if (!array_key_exists ($sOption, $aConfig))
    return ($sDefault);
  else
    return ($aConfig [$sOption]);
}

// Constants declarations
define('SESSION_INVALIDKEY',                8);
define('SESSION_READ',                      9);
define('SESSION_TIMEOUT',                   7);

define('ERROR_AUTH_METHOD_UNKNOWN',       129);
define('ERROR_CREATE_FILE',               134);
define('ERROR_CREATE_SESSION_DIR',        137);
define('ERROR_CREATE_SESSION_FILE',       145);
define('ERROR_NO_DATA',                   133);
define('ERROR_NO_KEY',                    131);
define('ERROR_NO_MESSAGE',                139);
define('ERROR_NO_SERVER_DEFINED',         143);
define('ERROR_NO_SERVER_KEY',             140);
define('ERROR_NO_SESSION_DIR',            130);
define('ERROR_NO_SIGNATURE',              138);
define('ERROR_NO_VALID_PUBLIC_KEY',       141);
define('ERROR_NOT_READABLE',              135);
define('ERROR_SESSION_DIR_NOT_WRITEABLE', 148);
define('ERROR_SESSION_FILE_EXISTS',       144);
define('ERROR_SESSION_FILE_FORMAT',       146);
define('ERROR_SESSION_TIMEOUT',           136);
define("ERROR_UNKNOWN_ERROR",             127);
define('ERROR_UNSUPPORTED_METHOD',        132);
define('ERROR_CURL_NOT_LOADED',           149);

define('LNG_DEUTSCH', 2);
define('LNG_ENGLISH', 1);
define('LNG_FRENCH',  0);

define('COOKIE_LIFE', 86400);
define('COOKIE_NAME', 'TequilaPHP');
define('MIN_SESSION_TIMEOUT', 600);

class Tequila {
  var $aLanguages = array (
			   LNG_ENGLISH => 'english',
			    LNG_FRENCH => 'francais',
			   );
  var $aErrors = array(
	ERROR_UNKNOWN_ERROR => array(
       		LNG_ENGLISH => 'An unknown error has occured.',
       		 LNG_FRENCH => 'Une erreur inconnue est survenue.',
       	),
       	ERROR_SESSION_DIR_NOT_WRITEABLE => array(
       		LNG_ENGLISH => 'Error: the given sessions directory is not writeable.',
       		 LNG_FRENCH => 'Erreur: le répertoire de sessions indiqué ne peut pas être écrit.',
       	),
       	ERROR_SESSION_FILE_FORMAT => array(
       		LNG_ENGLISH => 'Error: invalid session file format.',
       		 LNG_FRENCH => 'Erreur: format de fichier de session non valide.',
       	),
       	ERROR_CREATE_SESSION_FILE => array(
       		LNG_ENGLISH => 'Error: session file creation failed.',
       		 LNG_FRENCH => 'Erreur: échec lors de la création du fichier de session.',
       	),
       	ERROR_NO_DATA => array(
       		LNG_ENGLISH => 'Error: no session data.',
       		 LNG_FRENCH => 'Erreur: aucune donnée de session.',
       	),
       	ERROR_NO_SESSION_DIR => array(
       		LNG_ENGLISH => 'Error: inexistant or unspecified sessions directory.',
       		 LNG_FRENCH => 'Erreur: dossier de sessions inexistant ou non spécifié.',
       	),
       	ERROR_NO_SERVER_DEFINED => array(
       		LNG_ENGLISH => 'Error: no authentication server available.',
       		 LNG_FRENCH => 'Erreur: aucun serveur d\'authentification disponible.',
       	),
       	ERROR_UNSUPPORTED_METHOD => array(
       		LNG_ENGLISH => 'Error: unsupported request method.',
       		 LNG_FRENCH => 'Erreur: méthode de transmission inconnue.',
       	),
       	ERROR_NOT_READABLE => array(
       		LNG_ENGLISH => 'Error: unable to read session file.',
       		 LNG_FRENCH => 'Erreur: fichier de session non lisible.',
       	),
       	ERROR_CREATE_FILE => array(
       		LNG_ENGLISH => 'Error: unable to create session file.',
       		 LNG_FRENCH => 'Erreur: impossible de créer le fichier de sessions.',
       	),
       	ERROR_SESSION_TIMEOUT => array(
       		LNG_ENGLISH => 'Error: session timed out.',
       		 LNG_FRENCH => 'Erreur: la session a expiré.',
       	),
       	ERROR_CREATE_SESSION_DIR => array(
       		LNG_ENGLISH => 'Error: unable to create sessions directory.',
       		 LNG_FRENCH => 'Erreur: impossible de créer le dossier de sessions défini.',
       	),
       	ERROR_NO_MESSAGE => array(
       		LNG_ENGLISH => 'Error: no message to authenticate.',
       		 LNG_FRENCH => 'Erreur: pas de message à vérifier.',
       	),
       	ERROR_NO_SERVER_KEY => array(
       		LNG_ENGLISH => 'Error: no public key defined.',
		 LNG_FRENCH => 'Erreur: la clé publique du serveur d\'authentification n\'est pas définie ou disponible.',
		),
       	ERROR_NO_VALID_PUBLIC_KEY => array(
       		LNG_ENGLISH => 'Error: invalid public key.',
       		 LNG_FRENCH => 'Erreur: la clé publique fournie n\'est pas valide.',
       	),
       	ERROR_NO_SIGNATURE => array(
       		LNG_ENGLISH => 'Error: no signature for mesage authentication.',
       		 LNG_FRENCH => 'Erreur: pas de signature pour la vérification du mesage.',
       	),
       	ERROR_NO_KEY => array (
       		LNG_ENGLISH => 'Error: no session key.',
       		 LNG_FRENCH => 'Erreur: pas de clé de session.',
       	),
       	ERROR_SESSION_FILE_EXISTS => array (
       		LNG_ENGLISH => 'Error: session already created.',
		 LNG_FRENCH => 'Erreur: session déjà créée.',
	),
       	ERROR_CURL_NOT_LOADED => array (
       		LNG_ENGLISH => 'Error: CURL Extension is not loaded.',
       		 LNG_FRENCH => 'Erreur: L\'extension CURL n\'est pas présente.',
       	),
  );
  var      $aWantedRights = array ();
  var       $aWantedRoles = array ();
  var  $aWantedAttributes = array ();
  var  $aWishedAttributes = array ();
  var      $aWantedGroups = array ();
  var       $aCustomAttrs = array ();
  var      $sCustomFilter = '';
  var      $sAllowsFilter = '';
  var          $iLanguage = LNG_FRENCH;
  var    $sApplicationURL = '';
  var   $sApplicationName = '';
  var          $sResource = '';
  var               $sKey = '';
  var           $sMessage = '';
  var        $aAttributes = array();
  var           $iTimeout;
  var            $sServer = '';
  var         $sServerUrl = '';
  var            $sCAFile = '';
  var          $sCertFile = '';
  var           $sKeyFile = '';
  var      $bReportErrors = TRUE;
  var $stderr;
  
  var $logoutUrl;

  var $requestInfos = array();


  /*====================== Constructor
    GOAL : Class constructor   
    NOTE : All parameters are optional. They are present in the config
           file tequila_config.inc.php
     IN  : $sServerURL          -> (optional) Tequila server address (ie : https://tequila.epfl.ch/cgi-bin/tequila)
     IN  : $sSessionsDirectory  -> (optional) The directory where to save sessions files
     IN  : $iTimeout            -> (optional) Session timeout
   
  function __construct ($app, $sServer = '', $iTimeout = NULL) {
    $this->stderr = fopen ('php://stderr', 'w');

    if (!extension_loaded ('curl')) {
      return $this->Error (ERROR_CURL_NOT_LOADED);
    }

    
    if (empty ($sServer)) $sServer    = GetConfigOption ('sServer');
    if (empty ($sServer)) $sServerUrl = GetConfigOption ('sServerUrl');
    
    $aEtcConfig = $this->LoadEtcConfig ();

    if (empty ($sServer))    $sServer    = $aEtcConfig ['sServer'];
    if (empty ($sServerUrl)) $sServerUrl = $aEtcConfig ['sServerUrl'];    

    if (empty ($sServerUrl) && !empty ($sServer))
      $sServerUrl = $sServer . '/cgi-bin/tequila';
    if (empty ($iTimeout))  $iTimeout  = GetConfigOption ('iTimeout', 86400);
    if (empty ($logoutUrl)) $logoutUrl = GetConfigOption ('logoutUrl');

    $this->sServer     = $sServer;
    $this->sServerUrl  = $sServerUrl;
    $this->iTimeout    = $iTimeout;
    $this->logoutUrl   = $logoutUrl;
    $this->iCookieLife = COOKIE_LIFE;
    $this->sCookieName = COOKIE_NAME;
  }


  function Error ($iError) {

    if (!$this->bReportErrors) return ($iError);

    $iCurrentLanguage = $this->GetLanguage ();
    if (empty ($iCurrentLanguage))
      $iCurrentLanguage = LNG_FRENCH;

    if (array_key_exists ($iError, $this->aErrors))

      echo "\n<br /><font color='red' size='5'>" .
	   $this->aErrors[$iError][$iCurrentLanguage] .
	   "</font><br />\n";
    else
      echo "\n<br /><font color='red' size='5'>" .
	   $this->aErrors [ERROR_UNKNOWN_ERROR][$iCurrentLanguage] .
	   "</font><br />\n";
    return ($iError);
  }

  function SetReportErrors ($bReportErrors) {
    $this->bReportErrors = $bReportErrors;
  }
  function GetReportErrors () {
    return ($this->bReportErrors);
  }

  function LoadEtcConfig () {
    $sFile = '/etc/tequila.conf';
    if (!file_exists ($sFile)) return false;
    if (!is_readable ($sFile)) return false;
    
    $aConfig = array ();
    $sConfig = trim (file_get_contents ($sFile));
    $aLine = explode ("\n", $sConfig);
    foreach ($aLine as $sLine) {
      if (preg_match  ('/^TequilaServer:\s*(.*)$/i', $sLine, $match))
	$aConfig ['sServer'] = $match [1];

      if (preg_match  ('/^TequilaServerUrl:\s*(.*)$/i', $sLine, $match))
	$aConfig ['sServerUrl'] = $match [1];
    }
    return $aConfig;
  }
  
  /*====================== Custom parameters
    GOAL : Set the custom parameters
     IN  : $customParameters -> an array containing the parameters. The
            array key is the name of the parameter and the value is the value.
  */
  function SetCustomParamaters ($customParamaters) {
    foreach ($customParamaters as $key => $val) {
      $this->requestInfos [$key] = $val;
    }
  }

  /* GOAL : Returns the custom parameters */
  function GetCustomParamaters () {
    return $this->requestInfos;
  }
	
  /*********************** WANTED RIGHTS ***************************	
   ====================== Required rights ("wantright" parameter)
   GOAL : set the wanted rights
    IN  : $aWantedRights -> an array with the rights
  */
  function SetWantedRights ($aWantedRights) {
    $this->aWantedRights = $aWantedRights;
  }

  /*
    GOAL : Add a wanted right. The wanted right must be an array. It
	         will be merged we the array containing the wanted rights.	   
    IN   : $aRightsToAdd   -> an array containing the wanted rights to add
  */	
  function AddWantedRights ($aWantedRights) {
    $this->aWantedRights = array_merge ($this->aWantedRights, $aWantedRights);
  }

  /*
    GOAL : Remove some wanted rights
     IN  : $aRightsToRemove -> an array with the wanted rights to remove
  */
  function RemoveWantedRights ($aWantedRights) {
    foreach ($this->aWantedRights as $sWantedRight)
      if (in_array($sWantedRight, $aWantedRights))
	unset($this->aWantedRights[array_search($sWantedRight, $this->aWantedRights)]);
  }

  /*  GOAL : Returns the wanted rights array. */
  function GetWantedRights () {
    return ($this->aWantedRights);
  }

  /************************ WANTED ROLES ***************************   
  ====================== Required roles ("wantrole" parameter)
  GOAL : Set the wanted Roles
    IN  : $aWantedRoles  -> an array with the wanted roles
  */
  function SetWantedRoles ($aWantedRoles) {
    $this->aWantedRoles = $aWantedRoles;
  }

  /*
   GOAL : Add some wanted roles to the current roles
     IN  : $aRolesToAdd   -> an array with the roles to add.
  */	
  function AddWantedRoles ($aWantedRoles) {
    $this->aWantedRoles = array_merge ($this->aWantedRoles, $aWantedRoles);
  }

  /*
    GOAL : Remove some wanted roles from the list      
     IN  : $aRolesToRemove   -> an array with the roles to remove
  */
  function RemoveWantedRoles ($aWantedRoles) {
    foreach ($this->aWantedRoles as $sWantedRole)
      if (in_array ($sWantedRole, $aWantedRoles))
	unset ($this->aWantedRoles [array_search ($sWantedRole, $this->aWantedRoles)]);
  }

  /* GOAL : Returns the array containing the wanted roles */
  function GetWantedRoles () {
    return ($this->aWantedRoles);
  }

  /********************* REQUIRED ATTRIBUTES ***********************
    ====================== Required attributes ("request" parameter)
    GOAL : Set the wanted attributes 
     IN  : $aWantedAttributes   -> an array containing the wanted attributes
  */
  function SetWantedAttributes ($aWantedAttributes) {
    $this->aWantedAttributes = $aWantedAttributes;
  }

  /*
    GOAL : Add some wanted attributes to the list      
     IN  : $aAttributesToAdd -> an array with the attributes to add
  */  
  function AddWantedAttributes ($aWantedAttributes) {
    $this->aWantedAttributes = array_merge ($this->aWantedAttributes,
					    $aWantedAttributes);
  }

  /*
    GOAL : Remove some wanted attributes from the list
     IN  : $aAttributesToRemove -> an array containing the attributes to remove
  */  
  function RemoveWantedAttributes ($aWantedAttributes) {
    foreach ($this->aWantedAttributes as $sWantedAttribute)
      if (in_array($sWantedAttribute, $aWantedAttributes))
	unset ($this->aWantedAttributes [array_search($sWantedAttribute,
	  $this->aWantedAttributes)]);
  }

  /* GOAL : Returns the array containing the wanted attributes   */
  function GetWantedAttributes () {
    return ($this->aWantedAttributes);
  }
  
  /********************** WISHED ATTRIBUTES ************************
   ====================== Desired attributes ("wish" parameter)
   GOAL : Set the wished attributes
    IN  : $aWishedAttributes   -> an array containing the wished attributes
  */
  function SetWishedAttributes ($aWishedAttributes) {
    $this->aWishedAttributes = $aWishedAttributes;
  }

  /*
    GOAL : Add some wished attributes to the list
      IN : $aAttributesToAdd  -> an array containing the attributes to add
  */   
  function AddWishedAttributes ($aWishedAttributes) {
    $this->aWishedAttributes = array_merge ($this->aWishedAttributes,
					    $aWishedAttributes);
  }

  /*
    GOAL : Remove some wished attributes fromme the list
     IN  : $aAttributesToRemove -> an array with the attributes to remove
  */  
  function RemoveWishedAttributes ($aWishedAttributes) {
    foreach ($this->aWishedAttributes as $aWishedAttribute)
      if (in_array($aWishedAttribute, $aWishedAttributes))
	unset ($this->aWishedAttributes[array_search($aWishedAttribute,
	  $this->aWishedAttributes)]);
  }

  /* GOAL : Returns the array containing the wished attributes */  
  function GetWishedAttributes () {
    return ($this->aWishedAttributes);
  }
  
  /************************ WANTED GROUPS **************************
    ====================== Required groups ("belongs" parameter)
    GOAL : Set the wanted groups 
     IN  : $aWantedGroups -> an array containing the groups
  */
  function SetWantedGroups ($aWantedGroups) {
    $this->aWantedGroups = $aWantedGroups;
  }

  /*
    GOAL : Add some wanted groups to the list
     IN  : $aGroupsToAdd  -> an array containing the groups to add
  */  
  function AddWantedGroups ($aWantedGroups) {
    $this->aWantedGroups = array_merge($this->aWantedGroups, $aWantedGroups);
  }

  /*
    GOAL : Remove some wanted groups from the list
     IN  : $aGroupsToRemove  -> an array containing the groups to remove
  */  
  function RemoveWantedGroups ($aWantedGroups) {
    foreach ($this->aWantedGroups as $aWantedGroup)
      if (in_array($aWantedGroup, $aWantedGroups))
	unset($this->aWantedGroups[array_search($aWantedGroup,
	  $this->aWantedGroups)]);
  }

  /* GOAL : Returns the array containing the wanted groups */  
  function GetWantedGroups () {
    return ($this->aWantedGroups);
  }
  
  /************************* CUSTOM FILTER **************************   
    ====================== Own filter ("require" parameter)
    GOAL : Set the custom filter.
     IN  : $sCustomFilter -> a string containing the custom filter
  */
  function SetCustomFilter ($sCustomFilter) {
    $this->sCustomFilter = $sCustomFilter;
  }

  /* GOAL : Returns the string containing the custom filter  */  
  function GetCustomFilter () {
    return ($this->sCustomFilter);
  }
  
  /************************ ALLOWS FILTER **************************
    ====================== Allows filter ("allows" parameter)
    GOAL : Sets the allow filter
     IN  : $sAllowsFilter -> a string containing the allow filter
  */
  function SetAllowsFilter ($sAllowsFilter) {
    $this->sAllowsFilter = $sAllowsFilter;
  }

  /* GOAL : Returns the string containing the allows filter */
  function GetAllowsFilter () {
    return ($this->sAllowsFilter);
  }
  
  /********************* LANGUAGE INTERFACE *************************
    ====================== Interface language ("language" parameter)
    GOAL : Sets the current language
     IN  : $sLanguage  -> the language : 'english' | 'francais'                           
  */
  function SetLanguage ($sLanguage) {
    $this->iLanguage = $sLanguage;
  }

  /* GOAL : Returns the current language */  
  function GetLanguage () {
    return ($this->iLanguage);
  }
  
  /*********************** APPLICATION URL **************************
    ====================== Application URL ("urlaccess" parameter)
    GOAL : Sets the application URL. This is the URL where to redirect
           when the authentication has been done
     IN  : $sApplicationURL  -> the url
  */
  function SetApplicationURL ($sApplicationURL) {
    $this->sApplicationURL = $sApplicationURL;
  }

  /* GOAL : Returns the application URL */  
  function GetApplicationURL () {
    return ($this->sApplicationURL);
  }
  
  /********************** APPLICATION NAME *************************
    ====================== Application name ("service" parameter)
    GOAL : Set the application name. This will be displayed on the
           Tequila login window.
     IN  : $sApplicationName -> string containing the application name
  */
  function SetApplicationName ($sApplicationName) {
    $this->sApplicationName = $sApplicationName;
  }

  /* GOAL : returns the application name */  
  function GetApplicationName () {
    return ($this->sApplicationName);
  }
  
  /*********************** RESOURCE NAME ****************************
    GOAL : Set the resource name
     IN  : $sResource -> string with the resource name   
  */
  function SetResource ($sResource) {
    $this->sResource = $sResource;
  }

  /* GOAL : Returns the resource name */  
  function GetResource () {
    return ($this->sResource);
  }
  
  /*********************** SESSION KEY ******************************
    GOAL : Set the session key
     IN  : $sKey -> string with the session key   
  */
  function SetKey ($sKey) {
    $this->sKey = $sKey;
  }

  /* GOAL : Returns the session key */  
  function GetKey () {
    return ($this->sKey);
  }
  
  /*********************** SESSION MESSAGE **************************
    GOAL : Set the session message
     IN  : $sMessage -> string with the session message   
  */
  function SetMessage ($sMessage) {
    $this->sMessage = $sMessage;
  }

  /* GOAL : Returns the session message */    
  function GetMessage () {
    return ($this->sMessage);
  }
  
  /************************ TEQUILA SERVER **************************
    ====================== server name
    GOAL : Set tequila server name (i.e https://tequila.epfl.ch)
     IN  : $sServer -> the name
  */
  function SetServer ($sServer) {
    $this->sServer = $sServer;
  }

  /* GOAL : Returns Tequila server's name */  
  function GetServer () {
    return ($this->sServer);
  }
  
  /*====================== server URL
    GOAL : Set tequila server URL (ie https://tequila.epfl.ch/cgi-bin/tequila)
     IN  : $sURL -> the url
  */
  function SetServerURL ($sURL) {
    $this->sServerUrl = $sURL;
  }

  /* GOAL : Returns Tequila server's url */    
  function GetServerURL () {
    return ($this->sServerUrl);
  }
  
  //====================== Session manager parameters
  function SetTimeout ($iTimeout) {
    $this->iTimeout = $iTimeout;
  }
  
  function GetTimeout () {
    return ($this->iTimeout);
  }
  
  /************************ COOKIE PARAMETERS *********************
    GOAL : Set the cookie parameters. Very useful if you have page on your
           website that have different access rights than the other pages.
           Use this function to set the cookie name for thoses pages.
  */
  /*====================== Cookie Life parameters
      IN : $iCookieLife -> life of the cookie.
  */
  function SetCookieLife ($iTimeout) { // Obsolete
    $this->iCookieLife = $iTimeout;
  }

  /*====================== Cookie Name parameters
      IN : $sCookieName -> name of the cookie.
  */
  function SetCookieName ($sCookieName) {
    $this->sCookieName = $sCookieName;
  }

  /************************ CREATE PHP SESSION *******************
    GOAL : Create a PHP session with the Tequila attributes
     IN  : $attributes  -> an array containing the attributes returned
           by the tequila server.
  */
  function CreateSession ($attributes) {  
    if (!$attributes) return (FALSE);
    foreach ($attributes as $key => $val) {
      $this->aAttributes [$key] = $val;
      //$_SESSION [$key] = $val;	
      Session::put($key, $val);
    }
    //$_SESSION ['creation'] = time ();
    Session::put('creation', time());
    return (TRUE);
  }

  /* GOAL : Load or update a PHP session */	
  function LoadSession () {
    if (!Session::has('user')) return (FALSE);

    /****
      Check if all the wanted attributes are present in the $_SESSION.
      If at least one of the attribute is missing, we can consider that information
      is missing in $_SESSION. In this case, we return false to "force" to create a new
      session with the wanted attributes. This can happen when several website are
      running on the same web server and all are using the PHP Tequila Client.
    ****/

    foreach ($this->aWantedAttributes as $wantedAttribute) {
      //if (!array_key_exists ($wantedAttribute, $_SESSION)) return false;
    	if(!Session::has($wantedAttribute)) return false;
    }

    foreach ($this->aWishedAttributes as $wishedAttribute) {
      //if (!array_key_exists ($wishedAttribute, $_SESSION)) return false;
    	if(!Session::has($wishedAttribute));
    }

    $sesstime = time () - Session::get('creation');
    if ($sesstime > $this->iTimeout) return (FALSE);
    $this->sKey = Session::get('key');
    return (TRUE);
  }

  /************************* USER ATTRIBUTES ***********************
    GOAL : Returns an array containing user's attributes names as indexes
           and attributes values as values
    @out : Array containing attributes names as indexes and
           attributes values as values
  */
  function GetAttributes() {
    return ($this->aAttributes);
  }
  
  /* GOAL : To know if the user's attributes are present or not.
     @in  :	Array containing wanted attributes as keys
     @out :	The same array with TRUE or FALSE as value for the
     corresponding attribute
  */
  function HasAttributes (&$aAttributes) {
    foreach ($aAttributes as $sAttribute => $sHasIt)
      if (array_key_exists($sAttribute, $this->aAttributes))
	$aAttributes [$sAttribute] = TRUE;
      else
	$aAttributes [$sAttribute] = FALSE;
  }

  /* GOAL : Launch the user authentication */  
  function Authenticate () {
    if ($this->LoadSession ()) return (TRUE);
    //if (isset ($_COOKIE [$this->sCookieName]) && !empty ($_COOKIE [$this->sCookieName])) {
    $cookieValue = Cookie::get($this->sCookieName);

    if($cookieValue !== false && !empty($cookieValue)) {
      $this->sKey = $cookieValue;
      $attributes = $this->fetchAttributes ($this->sKey);
      if ($attributes) {
        $this->CreateSession ($attributes);
        return (TRUE);
      }
    }
    $this->createRequest ();
    setcookie ($this->sCookieName, $this->sKey);	  
    //Cookie::queue($this->sCookieName, $this->sKey);
    $url = $this->getAuthenticationUrl ();
    header ('Location: ' . $url);
    exit;
  }
  
  /*
      GOAL : Sends an authentication request to Tequila
  */
  function createRequest () {
    $urlaccess = $this->sApplicationURL;

    /* If application URL not initialized,
       we try to generate it automatically */
    if (empty ($urlaccess)) {
      $urlaccess = ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on'))
        ? 'https://' : 'http://')
	. $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];
      if (isset($_SERVER['PATH_INFO'])) {
        $urlaccess .= $_SERVER['PATH_INFO'];
      }
      if (isset($_SERVER['QUERY_STRING'])) {
        $urlaccess .= '?' . $_SERVER['QUERY_STRING'];
      }
    }
    
    /* Request creation */
    $this->requestInfos ['urlaccess'] = $urlaccess;

    if (!empty ($this->sApplicationName))
      $this->requestInfos ['service'] = $this->sApplicationName;
    if (!empty ($this->aWantedRights))
      $this->requestInfos ['wantright'] = implode($this->aWantedRights, '+');
    if (!empty ($this->aWantedRoles))
      $this->requestInfos ['wantrole'] =  implode($this->aWantedRoles, '+');
    if (!empty ($this->aWantedAttributes)) 
      $this->requestInfos ['request'] = implode ($this->aWantedAttributes, '+');
    if (!empty ($this->aWishedAttributes))
      $this->requestInfos ['wish'] = implode ($this->aWishedAttributes, '+');
    if (!empty ($this->aWantedGroups))
      $this->requestInfos ['belongs'] = implode($this->aWantedGroups, '+');
    if (!empty ($this->sCustomFilter))
      $this->requestInfos ['require'] = $this->sCustomFilter;
    if (!empty ($this->sAllowsFilter))
      $this->requestInfos ['allows'] = $this->sAllowsFilter;
    if (!empty ($this->iLanguage))
      $this->requestInfos ['language'] = $this->aLanguages [$this->iLanguage];
	  
    $this->requestInfos ['dontappendkey'] = "1"; 

	
    /* Asking tequila */
    $response = $this->askTequila ('createrequest', $this->requestInfos);
    $this->sKey = substr (trim ($response), 4); // 4 = strlen ('key=')
  }

  /* GOAL : Returns current URL.
            @return  string
  */
  function getCurrentUrl () {
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  }

  /* GOAL : Checks that user has correctly authenticated and retrieves its data.
            @return mixed
  */
  function fetchAttributes ($sessionkey) {
    $fields = array ('key' => $sessionkey);
    $response = $this->askTequila ('fetchattributes', $fields);
    if (!$response) return false;

    $result = array ();
    $attributes = explode ("\n", $response);
    
    /* Saving returned attributes */
    foreach ($attributes as $attribute) {
      $attribute = trim ($attribute);
      if (!$attribute)  continue;	  
      list ($key, $val) = explode ('=', $attribute,2);
      $result [$key] = $val;
    }
    return $result;
  }

  /**
  * Returns the value of $key.
  * $key is a Tequila attribute.
  * @return string
  */
  function getValue ($key = ''){
    //if (isset ($_SESSION [$key])) return $_SESSION [$key];
    if(Session::has($key)) return Session::get($key);
  }
  
  /*GOAL : Gets tequila server config infos */
  function getConfig () {
    return $this->askTequila ('config');
  }

  /*GOAL : Returns the Tequila authentication form URL.
           @return string
  */
  function getAuthenticationUrl () {
	//return sprintf('%s/requestauth?requestkey=%s',
	//	   $this->sServerUrl,
	//	   $this->sKey);    
	return sprintf('%s/requestauth?requestkey=%s',
		$this->sServerUrl,
		$this->sKey);    	   
  }

  /*
    GOAL : Returns the logout URL
      IN : $redirectUrl -> (optional) the url to redirect to when logout is done
  */  
  function getLogoutUrl ($redirectUrl = '') {
    $url = sprintf('%s/logout', $this->sServerUrl);
    if (!empty($redirectUrl)) {
      $url .= "?urlaccess=" . urlencode ($redirectUrl);
    }
    return $url;
  }

  /*
    GOAL : Destroy the session file
  */
  function KillSessionFile() {
  	$values = Session::all();
    if(!empty($values)){
      Session::flush();
    }
  }

  /*
    GOAL : Destroy session cookie 
  */
  function KillSessionCookie() {
    // Delete cookie by setting expiration time in the past with root path
    //Cookie::queue($this->sCookieName, '', time()-3600);
    setcookie($this->sCookieName, '', time()-3600, '/');
  }

  /*
    GOAL : terminate a session 
  */
  function KillSession() {
    $this->KillSessionFile();
    $this->KillSessionCookie();
  }

  /*
   GOAL : Logout from tequila
  */
  function Logout ($redirectUrl = '') {
    // Kill session cookie and session file
    $this->KillSession();
    // Redirect the user to the tequila server logout url
    header("Location: " . $this->getLogoutUrl($redirectUrl));
  }

  /*
    GOAL : contact tequila
     IN  : $type    -> the type of contact to have with tequila
      N  : $fields  -> an array with the information for the request
                       to Tequila server
  */  
  function askTequila ($type, $fields = array()) {
  //Use the CURL object in order to communicate with tequila.epfl.ch
    $ch = curl_init ();
    
    curl_setopt ($ch, CURLOPT_HEADER,         false);
    curl_setopt ($ch, CURLOPT_POST,           true);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($this->sCAFile)   curl_setopt ($ch, CURLOPT_CAINFO,  $this->sCAFile);
    if ($this->sCertFile) curl_setopt ($ch, CURLOPT_SSLCERT, $this->sCertFile);
    if ($this->sKeyFile)  curl_setopt ($ch, CURLOPT_SSLKEY,  $this->sKeyFile);

    $url = $this->sServerUrl;
    switch ($type) {
      case 'createrequest':
	$url .= '/createrequest';
	break;
	
      case 'fetchattributes':
	$url .= '/fetchattributes';
	break;
	
      case 'config':
	$url .= '/getconfig';
	break;
	
      case 'logout':
	$url .= '/logout';
	break;
	
      default:
	return;
    }
    // $url contains the tequila server with the parameters to execute 
    curl_setopt ($ch, CURLOPT_URL, $url);

    /* If fields where passed as parameters, */
    if (is_array ($fields) && count ($fields)) {
      $pFields = array ();
      foreach ($fields as $key => $val) {
	$pFields[] = sprintf('%s=%s', $key, $val);
      }
      $query = implode("\n", $pFields) . "\n";
      curl_setopt ($ch, CURLOPT_POSTFIELDS, $query);
    }    
    $response = curl_exec ($ch);
    // If connexion failed (HTTP code 200 <=> OK)
    if (curl_getinfo ($ch, CURLINFO_HTTP_CODE) != '200') {
      $response = false;
    }
    curl_close ($ch);
    return $response;
  }
}

?>
