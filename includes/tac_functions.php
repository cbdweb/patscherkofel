<?php
/*
* CLASS: functions
* LATEST UPDATE 3.7.2014
* COPYRIGHT SONOAHEALTH.COM 2014
*/

class tac_functions
{

    /* INITIALISE:
	 * $SITE_FUNC = new functions();
	 */
    public function __construct()
    {

    }

    /* EG:
	 * $SITE_FUNC->echoPre(ARRAY_TO_PARSE);
	 */
    public function echoPre($array)
    {
        echo '<pre class="debugger" style="position:fixed;bottom:0px;right:10px;height:800px;width:600px;overflow-y:scroll;z-index:50000;background:#f8f8f8;padding:10px;border-radius:5px;border:1px solid #ccc;">';
        print_r($array);
        echo '</pre>';

        echo "  <script>
                    jQuery('body').find('.debugger').each( function(i, v){
                        //console.log(i);
                    });
                </script>
            ";
    }

    /* EG:
	 * $SITE_FUNC->phpAlert( _STRING_ );
	 */
    public function phpAlert($message)
    {
        print '<script type="text/javascript">';
        print 'alert("'.$message.'")';
        print '</script>';
    }

    /* EG:
	 * $SITE_FUNC->generate_user_permalink('this is a test');
	 */
    public function generate_user_permalink($str)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $plink = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $plink = str_replace("&amp;", "and", $plink);
        $plink = str_replace("&", "and", $plink);
        $plink = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $plink);
        $plink = strtolower(trim($plink, '-'));
        $plink = preg_replace("/[_| -]+/", '-', $plink);
        $plinkOUT = preg_replace('/-+/', ' ${1}', $plink);
        $plinkOUT = str_replace(" ", "-", $plinkOUT);

      return trim($plinkOUT);
    }

    /* EG:
	 * $SITE_FUNC->remove_perma_link('this-is-a-test');
	 */
    public function remove_perma_link($str)
    {
      $str = str_replace( array("-", "_"), " ", $str);
      //$str = str_replace("and", "&amp;", $str);
      return $str;
    }

    /* EG:
	 * $SITE_FUNC->currentPageURL();
	 */
    public function currentPageURL()
    {
         $pageURL = 'http';
        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    /* EG:
	 * $SITE_FUNC->firstToUpper( _STRING_ );
	 */
    public function firstToUpper($text)
    {
        $split=explode(" ", $text);
        foreach ($split as $sentence) {
            $sentencegood=ucfirst($sentence);
            $text=str_replace($sentence, $sentencegood, $text);
        }

        return $text;
    }

    /* EG:
	 * $SITE_FUNC->get_class_properties( _CLASS_OBJ_ );
	 */
    public function get_class_properties($class)
    {

        $reflector    = new ReflectionClass( $class );

        $properties    = $reflector->getProperties();

        return $properties;
    }

    /*
	 * $SITE_FUNC->date_convert( _DATE_TIME_ );
	 *
	 * EG : PARSE 14-10-2014 8:59 (NOT UNIX TIME)
	 * @RETURN : STRING
	 */
    public function date_convert($stamp)
    {
        if ($stamp == '') {
            return;
        }

        $stamp = str_replace('/', '-', $stamp);

        $days            = '';
        $today            = date('d-m-Y H:i');
        $created_date    = date($stamp);

        $date1            = new \DateTime( $created_date );
        $date2            = new \DateTime( $today );

        $days_between    = $date2->diff($date1)->format("%a");

        if ($days_between <= 0) {

            $hours_diff = $date2->diff($date1);

            if ($hours_diff->h > 0) {
                $hours = $hours_diff->h;
                $hours = $hours + ($hours_diff->days*24);
                $p = ( $hours <= 1 ) ? '' : 's';
                $p = ' hour'.$p.' ago';
            } else {

                $hours = $hours_diff->i;
                $p = ( $hours <= 1 ) ? '' : 's';
                $p = ' minute'.$p.' ago';

                if ($hours <= 0) {
                    $p = 'Just Now';
                }

            }

            $hours = ( $hours <= 0 ) ? '' : $hours;

            return $hours.$p;
        }

        if ($days_between == 1) {
           $exp_stamp = explode(' ', $stamp);

            return 'Yesterday at ' . end($exp_stamp);
        }

        switch ($days_between) {

            case $days_between <= 0 :

                $days = ' today';
                break;

            case $days_between <= 6 && $days_between > 0 :

                $days = $days_between.' days ago';
                break;

            case $days_between >= 7 && $days_between <= 13:

                $days = 'over a week ago';
                break;

            case $days_between >= 14 && $days_between <= 20:

                $days = 'over 2 weeks ago';
                break;

            case $days_between >= 21 && $days_between <= 30:

                $days = 'over 3 weeks ago';
                break;

            case $days_between >= 31 && $days_between <= 60:

                $days = 'over a month ago';
                break;

            case $days_between >= 61 && $days_between <= 364 :

                $days = 'over 2 months';
                break;

            case $days_between >= 365 :

                $days = 'over a year ago';
                break;

            default : $days = 'Not set';
        }

        return $days;
    }

    /* EG:
	 * $SITE_FUNC->baseToImg( _base_64_str_ );
	 */
     public function baseStringToImg($base_64_str)
     {

         preg_match('/data:([^;]*);base64,/', $base_64_str, $mime);

         $base_64_str = preg_replace('/data:([^;]*);base64,/', '', $base_64_str);

         //print_r( $base_64_str );

         $mime = $mime[1];

         $img = base64_decode( $base_64_str );

         $img = imagecreatefromstring( $img );

         if ($mime == 'image/jpeg' || $mime == 'image/jpg') {
             //header('Content-type: image/jpeg');
             imagejpeg( $img );
         }

         imagedestroy( $img );

         return $img;
     }
	 
	
	/* EG:
	 * $SITE_FUNC->splitCamelCase( 'someCamelCasedString' );
	 */ 
	public function splitCamelCase( $str )
	{
		$f 		= '/(?<=[a-z])(?=[A-Z])/x';
		$a 		= preg_split($f, $str);
		
		return join($a, " " );
	}

    /* EG:
	 * $SITE_FUNC->read_dir( _DIR_ );
	 */
    public function read_dir($dir, $excludeFolders = array() )
    {
        $array = array();

        if (is_dir($dir)) {

            $d = dir($dir);

            while (false !== ($entry = $d->read())) {

                if ($entry != '.' && $entry != '..' && $entry != 'thumbs' && $entry != '_notes') {

                    if (strpos($entry, '.') !== (int) 0) {

                        $dirFix = (substr($dir, -1) == "/") ? $dir : $dir . '/';
                        $entry  = $dirFix . $entry;

                        if (is_dir($entry)) {
                            $array[] = $entry;
                            $array   = array_merge($array, $this->read_dir($entry));
                        } else {
                            $array[] = $entry;
                        }

                    }

                }
            }
            $d->close();
        }

        if ( !empty($excludeFolders) ) {

            $remove_folders = array();

            foreach ($excludeFolders as $img_dir) {
                $remove_folders[] = $dir.$img_dir;
            }

            $result = array_diff($array, $remove_folders);

            sort($result);

            return $result;

        } else {
            return $array;
        }

    }

    /* EG:
	 * $SITE_FUNC->imageFolderArray( _DIR_ );
	 */
    public function imageFolderArray($dir)
    {
        $folder_array = array();
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && $entry != 'thumbs' && $entry != '_notes') {
                    if (strpos($entry, '.') !== (int) 0) {

                        $folder_array[] = $entry;

                    }
                }
            }
            closedir($handle);
        }

        if (isset($folder_array)) {
            return $folder_array;
        }

        return false;
    }

    public function obfuscate_key_secret()
    {
        (string) $str = '';
        for ($i = 1; $i <= 10; $i++) {
            $str .= '5x'.$i.'='.(string) ($i*5);
        }

        return str_replace('x', '-', $str);
    }

    public function obfuscate_encrypt($_encoded)
    {
        $uu_encoded = strtr(base64_encode(addslashes(gzcompress(serialize($_encoded),9))), '+/=', '-_,');

        return $uu_encoded;
    }

    public function obfuscate_decrypt()
    {
        $file = file_get_contents(siteDomain.'static/test.omdkey.key');
        $uu_decoded = unserialize(gzuncompress(stripslashes(base64_decode(strtr($file, '-_,', '+/=' )))));

        return $uu_decoded;
    }

    /* EG:
	 * @RETURN GET SERVER IP
	 * $SITE_FUNC->get_IP_address();
	 */
    public function get_IP_address()
    {
        foreach (array('HTTP_CLIENT_IP',
                       'HTTP_X_FORWARDED_FOR',
                       'HTTP_X_FORWARDED',
                       'HTTP_X_CLUSTER_CLIENT_IP',
                       'HTTP_FORWARDED_FOR',
                       'HTTP_FORWARDED',
                       'REMOTE_ADDR') as $key){

            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $IPaddress) {
                    $IPaddress = trim($IPaddress); // Just to be safe

                    if (filter_var($IPaddress,
                                   FILTER_VALIDATE_IP,
                                   FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                        !== false) {
                        return $IPaddress;
                    }
                }
            }
        }

        return $IPaddress;
    }

    public function phpInfo()
    {
        phpinfo();
    }

    public function compareHistory($oldHistory, $newHistory)
    {
        $returnArray = array();
        foreach ($newHistory as $key => $value) {
            if ($oldHistory[$key] != $value) {
                $returnArray[$key] = $oldHistory[$key];
            }
        }

        return $returnArray;
    }

    public function filterPuncuation($str)
    {
        $_OUT =
        str_replace( array("!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", ",", ".", "/", "{", "}", "[", "}", "|", ">", "<", "~", "`", "?", ":", ";"),
                     array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""),
                     $str );

        return $_OUT;
    }

    public function specialCharsList()
    {
        return array("!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", ",", ".", "/", "{", "}", "[", "}", "|", ">", "<", "`", "?", ":", ";");
    }

    /* EG:
	 * REMOVES ARRAY DIFFERENTCES
	 * $SITE_FUNC->remove_array_deny( array_original, array_remove_diff );
	 * $org_array = array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4');
	 * deny_array = array('2', '4')
	 * returns array('1'=>'1', '3'=>'3');
	 */
    public function remove_array_deny($org_array, $deny_array)
    {
        foreach ($org_array as $i=>$array) {
            foreach ($array as $key=>$value) {
                if (in_array($key, $deny_array)) {
                    unset($org_array[$i][$key]);
                }
            }
        }

        return $org_array;
    }

    /* EG:
	 * DEFINE THE BODY CLASS
	 *
	 */
    public function get_body_class()
    {

        $NAV_TITLE    = ( isset($_GET['controller']) ) ? explode('-', $_GET['controller']) : '';
        $ID_JOIN    = '';

        if ( isset($_GET['controller']) && $_GET['controller'] == '' ) {

            $BODY_ID = 'homeSection';

        } else {
            if (is_array($NAV_TITLE)) {
                foreach ($NAV_TITLE as $i => $split_title) {
                    if ($i < 1) {
                        $ID_JOIN .= strtolower($split_title);
                    }

                    if ($i > 0 && $split_title != 'a') {
                        $ID_JOIN .= $this->firstToUpper($split_title);
                    }

                    $BODY_ID = $ID_JOIN;

                    if ($BODY_ID == '' || !isset($BODY_ID) || empty($BODY_ID)) {
                        $BODY_ID == 'home';
                    }

                }
            } else {
                $BODY_ID = 'homeSection';
            }
        }

        return $BODY_ID;
    }

    /* EG: $functions->stripslashes_array( __array__ );
	 * STRIP ALL SLASHES AS ARRAY
	 *
	 */
    public function stripslashes_array(&$arr)
    {
        array_walk_recursive($arr, function (&$val) {
            $val = htmlentities($val, ENT_QUOTES);
        });

        return $val;
    }

    /*
	 * EG:	$functions->get_breadcrumbs();
	 * 		$functions->get_breadcrumbs( 'controller-to-go-to' );
	 */
    public function get_breadcrumbs($customController = null)
    {

        $breadcrumb    = array();

        $_label            = '';

        if ( isset($_GET['controller']) && $_GET['controller'] == '' ) {

            $breadcrumb_label = '';

        } else {

            if ( is_null($customController) ) {
                $breadcrumb    = ( isset($_GET['controller']) ) ? explode('-', $_GET['controller']) : '';
            } else {
                $breadcrumb    = explode('-', $customController);
            }

            foreach ($breadcrumb as $label) {
                $_label     .= $label.' ';
            }

            $_label = trim($_label);

            $breadcrumb_label = array( 'label' => $this->firstToUpper($_label), 'url' => siteDomain.$_GET['countryCode'].'/'.$this->generate_user_permalink( $_label ) );
        }

        return $breadcrumb_label;
    }

    public function generateSingleBreadcrumbWithCaps()
    {
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], siteDomain) !== false) {
            $returnArray['url'] = $_SERVER['HTTP_REFERER'];
            $urlArray = explode('/', $_SERVER['HTTP_REFERER']);
            if (is_numeric(end($urlArray))) {
                array_pop($urlArray);
            }
            $function = end($urlArray);
            $controller = prev($urlArray);
            $functionLabel = $this->processSingleBreadcrumbWithCapsString($function);
            $controllerLabel = $this->processSingleBreadcrumbWithCapsString($controller);
            $returnArray['label'] = $controllerLabel.' / '.$functionLabel;
        } else {
            $returnArray['url'] = null;
            $returnArray['label'] = null;
        }
        return $returnArray;
    }

    private function processSingleBreadcrumbWithCapsString($string)
    {
        $returnString = $string;
        preg_match_all('/[A-Z]/', $string, $stringMatches, PREG_OFFSET_CAPTURE);
        if (count($stringMatches[0]) > 0) {
            $matches = array_reverse($stringMatches[0]);
            foreach ($matches as $matchArray) {
                $returnString = substr_replace($returnString, ' '.$matchArray[0], $matchArray[1], 1);
            }
        }
        return ucfirst($returnString);
    }

    /* EG:  _call $functions->trim_post();
	 *
	 *
	 */
    public function trim_post($content_array, $max_length = 48)
    {

        $str_trim        = '';

        $str_trim        = strip_tags($content_array);

        if (strlen($str_trim) > $max_length) {
            $offset    = ($max_length - 3) - strlen($str_trim);
            $str_trim    = substr($str_trim, 0, strrpos($str_trim, ' ', $offset)) . '...';
        }

        return $str_trim;
    }

    /*
	 * REMOVE ALL MICROSOFTS IN-COMPATABLE CHARS SETS
	 * @RETURN : STRING : A CLEAN VERSION OR THE ORIGINAL CONTENT
	 */
    public function remove_microsoft_word_puncuation($str)
    {

        $str = str_replace(
                                array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6", "\xCC\xB6 \xC2\xA0", "\xCC\xB6", "\xE2\x81\xB0C"),
                                    array("'", "'", '"', '"', '-', '--', '...', '-', '-', '-'),
                                $str);

        $str = str_replace(
                                array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
                                    array("'", "'", '"', '"', '-', '--', '...'),
                                $str);

        $str = str_replace(
                                array(';̶', 'ñ', ' ñ', '  ñ', '†', '�'),
                                array(';', '', '', '', '', ''),
                                $str);

        $str = str_replace(
                                array('&dragger;', '&#x02020;', '&#8224;'),
                                array('', '', ''),
                                $str);

        preg_replace('/[\x00-\x1f]/', '?', $str);

        //$str = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str);

        $str = $this->strip_hidden_chars($str);

        //$this->phpAlert( $this->AsciiToInt('†') );
        //$this->phpAlert( $this->unichr('†') );
        return $str;
    }

    /*
	 * STRIP ALL HIDDEN CHARS AND COMPRESS THE OUT PUT
	 * @RETURN : STRING : CLEAN AND COMPRESSED STRING
	 */
    public function strip_hidden_chars($str)
    {

        $chars = array("\r\n", "\n", "\r", "\t", "\0", "\x0B");

        $str = str_replace($chars," ",$str);

        $str = preg_replace("/[[:cntrl:]]/", "", $str);

        $clean = preg_replace('/[^\PC\s]/u', '', $str);

         for ($i = 0; $i < 32; $i++) {
             $clean = str_replace( chr($i), "", $clean);
         }

        $clean = str_replace(chr(127), "", $clean);

        return preg_replace('/\s+/',' ',$clean);
    }

    // CHARACTOR UNICODE TRANSFORM
    public function unichr($u)
    {
        return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    // CHARACTOR ASCII TRANSFORM
    public function AsciiToInt($char)
    {
        $success = "";
        if (strlen($char) == 1) {
            return "char(" . ord($char) . ")";
        } else {
            for ($i = 0; $i < strlen($char); $i++) {
                if ($i == strlen($char) - 1) {
                    $success = $success . ord($char[$i]);
                } else {
                    $success = $success . ord($char[$i]) . ",";
                }
            }

            return "char(".$success.")";
        }
    }

    /*
	 * GET ANY DATA TRANFERED THROUGH ($_GET['secure'])
	 * QUESTION ID's AND PAGINATION's ARE SENT AS
	 *
	 * EG : /qid,20/ or '/page,2/' or '/sort,replied/'
	 *
	 * @RETURN: 	IF HASH HAS BEEN SENT THROUGH "secure"
	 *				EXPLODE IT INTO AN ARRAY ELSE RETURN IT
	 *				AS A REGULAR $_GET[] VARIABLE
	 *
	 * 				IF REVERSE_SECURE_URL IS SET
	 * @RETURN: 	URL STRING NOT ARRAY /sort,waiting,page,2/
	 *
	 */

     public function get_secure_URL($REVERSE_SECURE_URL = null, $USE_SESSIONS = null)
     {
        // IF REVERSE_SECURE_URL IS SET
        // @RETURN: URL STRING NOT ARRAY /sort,waiting,page,2/
        if ( !is_null($REVERSE_SECURE_URL) && $REVERSE_SECURE_URL != 'bypass' ) {

            $s_url = '';

            foreach ($REVERSE_SECURE_URL as $keys => $sURL) {
                $sURL = ( !empty($sURL) ) ? $sURL.',' : '' ;
                $s_url .= $keys.','.$sURL;
            }

            return $s_url;
        }

        $extract_secure_url = array('');

        if ( isset($_GET['secure']) && !empty($_GET['secure']) ) {

          // EXIT TO PUBLIC VIEW IF NO SESSIONS ARE SET
          if ( is_null($USE_SESSIONS) && !isset($_SESSION['sonoa_user_map_id'])) {
              $this->redirect(str_replace('/' . $_GET['secure'], '', $this->currentPageURL()));
          }

          // ELSE CONTINUE
          $extract_secure_url = explode(',', $this->Sanitize($_GET['secure'], true) );

          if ($extract_secure_url[0] == 'cdata') {
                $extract_secure_url = array();

                if ( preg_match('/[^cdata,].*?/iU', $_GET['secure'], $cacheKeyData) ) {

                    $extract_secure_url['cdata'] = $cacheKeyData[0];
                }

                return $extract_secure_url;
          }

          if ( !isset($extract_secure_url[1]) ) {
              return $_GET['secure'];
          }

            $extract_secure_array    = array();
            $extract_secure_array2    = array();

            for ($i = 0; $i < (count($extract_secure_url)); $i++) {
                if ( ($i+1) % 2 == 0 ) {
                    $extract_secure_array = [ $extract_secure_url[$i-1] => $extract_secure_url[$i] ];
                    array_push($extract_secure_array2, $extract_secure_array );
                }
            }

            unset($extract_secure_array);
            $extract_secure_array = array();

            foreach ($extract_secure_array2 as $i => $v) {
                foreach ($v as $n => $o) {
                    $a = array($n=>$o);
                    $extract_secure_array[$n] = $o;
                }
            }

            $extract_secure_url = $extract_secure_array;

        } else {
              return '';
        }

        return $extract_secure_url;
    }

    /*
	 * GET ANY DATA TRANFERED AS FAKE OR RAW POST/GET DATA
	 *
	 * EG : &testID=123&testSend=send-data&testFunc=testFunction
	 *
	 * @RETURN : ARRAY : [testID] => 123, [testSend] => send-data, [testFunc] => testFunction
	*/
	public function getPostVariables( $postArr = null )
	{
		$postBack         	= array();
		
		$postBack_array		= array();
		
		if( is_null($postArr) )
		{
			$postArr = file_get_contents("php://input");		
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'GET' ){
			return $_GET;
		}
				
				
		if( empty($postArr) ){
			return $postBack_array;
		}
				
		$postArr = urldecode($postArr);
				
		$postArr = array_values(array_filter(str_replace('"', '', explode('&', $postArr))));
		
		
		foreach ($postArr as $i => $post)
		{	
		
			//$post = urldecode($post);
			
			preg_match('/\b(?<=)\w+/si', $post, $keyMatch);
		
			preg_match('/\b(?>=)\w+.*/si', $post, $valueMatch);
			
			//print_r( $keyMatch );
		
			$keyMatch		= ( !empty($keyMatch) ) ? $keyMatch[0] : '';
		
			$valueMatch	= ( !empty($valueMatch) ) ? $valueMatch[0] : '';
					
			$postBack[]	= [ $this->Sanitize($keyMatch) => urldecode( $this->Sanitize($valueMatch)) ];
			
			
		}
		
		foreach ($postBack as $i => $v)
		{
			foreach ($v as $n => $o) {
				$postBack_array[$n]    = str_replace('=', '', $o);
			}
		}
		
		return $postBack_array;
	}

    /*
	 * INJECTION SANITIZER
	 * @RETURN: SANITIZED STRING
	 */
    public function Sanitize($str, $remove_nl=true)
    {
         if ( ($str == '') ) {
             return '';
         }

        $str = stripslashes($str);

        if ($remove_nl) {

            $injections = array(
				  '/(\n+)/i',
                '/(\r+)/i',
                '/(\t+)/i',
                '/(%0A+)/i',
                '/(%0D+)/i',
                '/(%08+)/i',
                '/(%09+)/i'
                );
            $str = preg_replace($injections,'',$str);
        }

        return $str;
    }

    /*
	 * A FORCED REDIRECT BY JAVASCRIPT
	 * $this->functions->redirect($url);
	 */
    public function redirect($url = '', $FLAG = null)
    {
        if ( !is_null($FLAG) ) {
            if ($FLAG == 'RELOAD') {
                echo "<script>window.location.reload();	</script>";

                return;
            }

            if ($FLAG == 'HOME') {
                echo "<script>window.location = '".siteDomain."'</script>";

                return;
            }

            if ($FLAG == 'LOGIN_REDIRECT') {
                echo "<script>window.location = '".siteDomain.$_GET['countryCode'].'/sonoahealth/pop-login'."'</script>";

                return;
            }

        }

        echo "<script>window.location = '".$url."'</script>";
    }

    public function addButton($controller, $functionName, $extraVars)
    {
        $data['controller'] = $this->generate_user_permalink($controller);
        $data['functionName'] = $this->generate_user_permalink($functionName);
        $data['extraVars'] = $extraVars;
        $this->loadTemplate('tableTemplate/button', $data);
    }

    public function addButtonCustom($controller, $functionName, $extraVars, $class = 'btn-success')
    {
        $data['controller'] = $controller;
        $data['functionName'] = $functionName;
        $data['extraVars'] = $extraVars;
        $data['class']    = $class;
        $this->loadTemplate('tableTemplate/buttonCustom', $data);
    }

    public function addBackButton($label, $class = 'btn-info', $callback_url = null)
    {
        $back_url = ( !is_null($callback_url) ) ? $callback_url : $_SERVER['HTTP_REFERER'];

        return '<a class = "btn '.$class.'" href = "'.$back_url.'">'.$label.'</a>';
    }

    public function addNoHrefButton($extraVars)
    {
        $data['extraVars'] = $extraVars;
        $this->loadTemplate('tableTemplate/buttonNoHref', $data);
    }

    public function openElement($elementType, $extraVars = null)
    {
        $data['elementType'] = $elementType;
        $data['extraVars'] = $extraVars;
        $this->loadTemplate('generalHTMLTemplate/openElement', $data);
    }

    public function closeElement($elementType, $extraVars = null)
    {
        $data['elementType'] = $elementType;
        $data['extraVars'] = $extraVars;
        $this->loadTemplate('generalHTMLTemplate/closeElement', $data);
    }

    //  ==============
    //
    //  FORMATTERS
    //
    //  ===============
    public function calcAge($dob)
    {
        $t = time();
        $age = ($dob < 0) ? ($t + ($dob * -1)) : $t - $dob;

        return floor($age / 31536000);
    }

    public function calcHeight($height, $units)
    {
        switch ($units) {
            // only formats in inches so far
            case "inches":
                default:

                $inches = round($height * 0.393701);
                $formattedHeight =  intval($inches / 12) . '&#8217; ' . ($inches % 12) . '&#8221;';

            break;
        }

        return $formattedHeight;
    }

    public function calcBMI($height, $weight)
    {
        if (intval($height) == 0 || $height == "") {
           $bmi = 0;
        } else {
            $bmi = ($weight * (100 * 100)) / ($height * $height);
        }

        return ceil($bmi);
    }

    public function calcDateJoined($dateString)
    {
        return date("jS M Y", strtotime($dateString));
    }

    /**
     * @todo Description of function checkPasswordStrength
     * @param
     * @return
     */
    public function checkPasswordStrength($password)
    {
        $policyArray = array(
            "min_length" => HA_PASSWORD_MIN_LENGTH,
            "max_length" => HA_PASSWORD_MAX_LENGTH,
            "min_lowercase_chars" => HA_PASSWORD_MIN_LOWERCASE_CHARS,
            "max_lowercase_chars" => HA_PASSWORD_MAX_LOWERCASE_CHARS,
            "min_uppercase_chars" => HA_PASSWORD_MIN_UPPERCASE_CHARS,
            "max_uppercase_chars" => HA_PASSWORD_MAX_UPPERCASE_CHARS,
            "disallow_numeric_chars" => HA_PASSWORD_DISALLOW_NUMERIC_CHARS,
            "disallow_numeric_first" => HA_PASSWORD_DISALLOW_NUMERIC_FIRST,
            "disallow_numeric_last" => HA_PASSWORD_DISALLOW_NUMERIC_LAST,
            "min_numeric_chars" => HA_PASSWORD_MIN_NUMERIC_CHARS,
            "max_numeric_chars" => HA_PASSWORD_MAX_NUMERIC_CHARS,
            "disallow_nonalphanumeric_chars" => HA_PASSWORD_DISALLOW_NONALPHANUMERIC_CHARS,
            "disallow_nonalphanumeric_first" => HA_PASSWORD_DISALLOW_NONALPHANUMERIC_FIRST,
            "disallow_nonalphanumeric_last" => HA_PASSWORD_DISALLOW_NONALPHANUMERIC_LAST,
            "min_nonalphanumeric_chars" => HA_PASSWORD_MIN_NONALPHANUMERIC_CHARS,
            "max_nonalphanumeric_chars" => HA_PASSWORD_MAX_NONALPHANUMERIC_CHARS
        );
        $policy = new Policy($policyArray);
        $return['status']   = $policy->validate($password);
        $return['strength'] = $policy->getPasswordStrength();
        $return['error']    = $policy->get_errors();

        return $return;
    }

     /**
     * @todo Description of function month_select_box
     * @param  $field_name[optional]		default value : 'month'
     * @param  $value[optional]		default value : NULL
     * @return
     */
    public function generic_select_box($field_name, $optionsArray, $field_value = null)
    {
        $count = sizeof($optionsArray);
        $options = "<option>--select--</option>";
        $selected = "";
        foreach ($optionsArray as $key => $value) {
            if ($field_value == $value) {
                $selected = "selected";
            }
            $options.= '<option value="' . $key . '" ' . $selected . ' >' . $value . '</option>';
        }

        return '<select name="' . $field_name . '" class="dropdown birthdate">' . $options . '</select>';
    }

    /**
	* @todo Description of function month_select_box
	* @param  $field_name[optional]		default value : 'month'
	* @param  $value[optional]		default value : NULL
	* @return
	*/
    public function month_select_box($field_name = 'month', $value = null)
    {
        $month_options = '<option value="">Month</option>';
        for ($i = 1;$i <= 12;$i++) {
            $selected = "";
            $month_num = str_pad($i, 2, 0, STR_PAD_LEFT);
            $month_name = date('F', mktime(0, 0, 0, $i + 1, 0, 0));
            if ($value == $month_num) {
                $selected = 'selected="selected"';
            }
            $month_options.= '<option value="' . $month_num . '" ' . $selected . ' >' . $month_name . '</option>';
        }

        return '<select name="' . $field_name . '" id='.$field_name.'>' . $month_options . '</select>';
    }

    /**
	* @todo Description of function day_select_box
	* @param  $field_name[optional]		default value : 'day'
	* @param  $value[optional]		default value : NULL
	* @return
	*/
    public function day_select_box($field_name = 'day', $value = null)
    {
    $days = range(1, 31);
    $day_options = '<option value="">Day</option>';
    foreach ($days as $d) {
        $selected = "";
        $day_num = str_pad($d, 2, 0, STR_PAD_LEFT);
        if ($value == $day_num) {
            $selected = 'selected="selected"';
        }
        $day_options.= '<option value="' . $day_num . '" ' . $selected . '>' . $day_num . '</option>';
    }

    return '<select name="' . $field_name . '" id='.$field_name.'>' . $day_options . '</select>';
    }

    /**
	* @todo Description of function year_select_box
	* @param  $field_name[optional]		default value : 'year'
	* @param  $value[optional]		default value : NULL
	* @return
	*/
    public function year_select_box($field_name = 'year', $value = null)
    {
        $years = range(1900, date('Y'));
        krsort($years);
        $year_options = '<option value="">Year</option>';
        foreach ($years as $y) {
            $selected = "";
            if ($value == $y) {
                $selected = 'selected="selected"';
            }
            $year_options.= '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
        }

        return '<select name="' . $field_name . '" id='.$field_name.'>' . $year_options . '</select>';
    }
    
}
