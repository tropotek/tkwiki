<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */





/**
 * Convert a map array to a stdClass object
 *
 * @param array $map
 * @return stdClass
 */
function arrayToObject($array)
{
    if (!is_array($array)) {
        return $array;
    }
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $name => $value) {
            $name = strtolower(trim($name));
            if (!empty($name)) {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    } else {
        return false;
    }
}





/**
 * Strip tag attributes and their values from html
 * By default the $attrs contains tag events
 *
 * @param string $str
 * @param array $atrs Eg: array('onclick', 'onmouseup', 'onmousedown', ...);
 */
function stripAttrs($str, $attrs = null)
{
    if ($attrs === null)
        $attrs = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
        'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload',
        'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu',
        'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
        'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart',
        'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout',
        'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown',
        'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseup', 'onmousedown', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel',
        'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset',
        'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll',
        'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    
    if (!is_array($attrs))
        $attrs = explode(",", $attrs);
    
    foreach ($attrs as $at) {
        $reg = "/(<.*)( $at=\"([^\".]*)\")(.*>)/i";
        while (preg_match($reg, $str)) {
            $str = preg_replace($reg, '$1$4', $str);
        }
    }
    return $str;
}
    
    
/**
 * Surround a string by quotation marks. Single quote by default
 *
 * @param string $str
 * @param char $quotes
 * @return string
 * @package Tk
 */
function enquote($str, $quote = "'")
{
    return $quote . $str . $quote;
}

/**
 * Return the string with the first character lowercased
 *
 * @param string $str
 * @return string
 * @package Tk
 */
if (!function_exists('lcFirst')) {
    function lcFirst($str)
    {
        return strtolower($str[0]) . substr($str, 1);
    }
}

/**
 * Convert camele case words so "testFunc" would convert to "Test Func"
 * Adds a capital at the first char and ass a space before all other upper case chars
 *
 * @param string $string
 * @return string
 * @package Tk
 */
function ucSplit($str)
{
    return ucfirst(preg_replace('/[A-Z]/', ' $0', $str));
}


/**
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 * "..." is added if result do not reach original string length
 *
 * @param string $str
 * @param integer $length
 * @param string $endStr
 * @param integer $minword
 */
function wordcat($str, $length, $endStr = '', $minword = 3)
{
    if (!$str) {
        return $str;
    }
    $sub = '';
    $len = 0;
    
    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }
    return $sub . (($len < strlen($str)) ? $endStr : '');
}


/**
 * Output a visual dump of an object.
 *
 * EG:<br/>
 * <code>
 * <?php
 *   // var dump usage
 *   vd($arg1, $arg2, $arg3, ...);
 * ?>
 * </code>
 *
 * @param mixed $args Multiple vars retrived using func_get_args()
 */
function vd()
{
    $args = func_get_args();
    
    $method = new ReflectionMethod('Tk', 'debug');
    return $method->invokeArgs(NULL, $args);
}



/**
 * Count the number of bytes of a given string.
 * Input string is expected to be ASCII or UTF-8 encoded.
 * Warning: the function doesn't return the number of chars
 * in the string, but the number of bytes.
 * See http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
 * for information on UTF-8.
 *
 * @param string $str The string to compute number of bytes
 * @return integer The length in bytes of the given string.
 * @package Tk
 * @todo Rename this function to strSize or similar to avoid
 *   confudion with Tk_Type_Path static methods
 */
function str2Bytes($str)
{
    // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
    // Number of characters in string
    $strlen_var = strlen($str);
    
    // string bytes counter
    $d = 0;
    
    /*
     * Iterate over every character in the string,
     * escaping with a slash or encoding to UTF-8 where necessary
     */
    for($c = 0; $c < $strlen_var; ++$c) {
        $ord_var_c = ord($str{$c});
        switch (true) {
            case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)) :
                // characters U-00000000 - U-0000007F (same as ASCII)
                $d++;
                break;
            case (($ord_var_c & 0xE0) == 0xC0) :
                // characters U-00000080 - U-000007FF, mask 110XXXXX
                $d += 2;
                break;
            case (($ord_var_c & 0xF0) == 0xE0) :
                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                $d += 3;
                break;
            case (($ord_var_c & 0xF8) == 0xF0) :
                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                $d += 4;
                break;
            case (($ord_var_c & 0xFC) == 0xF8) :
                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                $d += 5;
                break;
            case (($ord_var_c & 0xFE) == 0xFC) :
                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                $d += 6;
                break;
            default :
                $d++;
        }
        ;
    }
    ;
    return $d;
}




/**
 * Convert html special characters to nemeric entities (eg: &nbsp; to &#160;)
 * Usefull for XML encoding strings
 *
 * @param string $string
 * @return string
 * @package Tk
 */
function numericEntities($xml)
{
    $list = get_html_translation_table(\HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8');
        $mapping = array();
        foreach ($list as $char => $entity) {
            $mapping[strtolower($entity)] = '&#' . tkOrd($char) . ';';
        }
        $xml = str_replace(array_keys($mapping), $mapping, $xml);
        return $xml;
}


    /**
     * Since PHP's ord() function is not compatible with UTF-8
     * Here is a workaround.... GGRRR!!!!
     *
     * @param string $ch
     * @return integer
     */
    function tkOrd($ch)
    {
        $k = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

/**
 * Test if a string is UTF-8 encoded
 *
 * @param string $string
 * @todo: Test this is working correctly
 */
function isUtf8($string) { // v1.01
    $_is_utf8_split = 5000;
    if (strlen($string) > $_is_utf8_split) {
        // Based on: http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
        for ($i=0,$s=$_is_utf8_split,$j=ceil(strlen($string)/$_is_utf8_split);$i < $j;$i++,$s+=$_is_utf8_split) {
            if (isUtf8(substr($string,$s,$_is_utf8_split)))
                return true;
        }
        return false;
    }
    // From http://w3.org/International/questions/qa-forms-utf-8.html
    return preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string);
    
}

/**
 * Get the mime type of a file based on its extension
 *
 * @param string $filename
 * @return string
 * @package Tk
 */
function getFileMimeType($filename)
{
    $mime_types = array('txt' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html', 'php' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'xml' => 'application/xml', 'swf' => 'application/x-shockwave-flash', 'flv' => 'video/x-flv',

    // images
    'png' => 'image/png', 'jpe' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'ico' => 'image/vnd.microsoft.icon', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',

    // archives
    'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed', 'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload', 'cab' => 'application/vnd.ms-cab-compressed',

    // audio/video
    'mp3' => 'audio/mpeg', 'qt' => 'video/quicktime', 'mov' => 'video/quicktime',

    // adobe
    'pdf' => 'application/pdf', 'psd' => 'image/vnd.adobe.photoshop', 'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript',

    // ms office
    'doc' => 'application/msword', 'rtf' => 'application/rtf', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',

    // open office
    'odt' => 'application/vnd.oasis.opendocument.text', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet');
    $extArr = explode('.', $filename);
    $ext = strtolower(array_pop($extArr));
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}
/*
 * PHP Override to get the MIME type of a file
 *   if the function mime_content_type does not exsit
 */
if (!function_exists('mime_content_type')) {
    function mime_content_type($filename)
    {
        return getFileMimeType($filename);
    }
}


/*
 * Add a method get_called_class for PHP < 5.3
 */
if (!function_exists('get_called_class')) {
    
    function get_called_class($bt = false, $l = 1)
    {
        if (!$bt) {
            $bt = debug_backtrace();
        }
        if (!isset($bt[$l])) {
            throw new Exception("Cannot find called class -> stack level too deep.");
        }
        if (!isset($bt[$l]['type'])) {
            throw new Exception('type not set');
        } else {
            switch ($bt[$l]['type']) {
                case '::' :
                    $lines = file($bt[$l]['file']);
                    $i = 0;
                    $callerLine = '';
                    do {
                        $i++;
                        $callerLine = $lines[$bt[$l]['line'] - $i] . $callerLine;
                    } while (stripos($callerLine, $bt[$l]['function']) === false);
                    preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $callerLine, $matches);
                    if (!isset($matches[1])) {
                        // must be an edge case.
                        throw new Exception("Could not find caller class: originating method call is obscured.");
                    }
                    switch ($matches[1]) {
                        case 'self' :
                        case 'parent' :
                            return get_called_class($bt, $l + 1);
                        default :
                            return $matches[1];
                    }
                // won't get here.
                case '->' :
                    switch ($bt[$l]['function']) {
                        case '__get' :
                            // edge case -> get class of calling object
                            if (!is_object($bt[$l]['object']))
                                throw new Exception("Edge case fail. __get called on non object.");
                            return get_class($bt[$l]['object']);
                        default :
                            return $bt[$l]['class'];
                    }
                default :
                    throw new Exception("Unknown backtrace method type");
            }
        }
    }
    
}

// This is a little fix for IIS with no $_SERVER['REQUEST_URI'] value
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; }
}



/*
 * dissable magic_quotes_gpc if enabled
 */
if (get_magic_quotes_gpc()) {
    /**
     * Dissable magic quotes if enabled on the server
     */
    function magicQuotesGpc()
    {
        function traverse(&$arr)
        {
            if (!is_array($arr)) {
                return;
            }
            foreach ($arr as $key => $val) {
                is_array($arr[$key]) ? traverse($arr[$key]) : ($arr[$key] = stripslashes($arr[$key]));
            }
        }
        $gpc = array(&$_COOKIE, &$_REQUEST, &$_GET, &$_POST);
        traverse($gpc);
    }
    magicQuotesGpc();
}

/*
 * Fix IE submit key name
 * When a form contains an image submit. IE uses 'submit_x' and 'submit_y'
 * as the $_REQUEST key names. Here we add the value 'submit' to the request to fix this
 * issue.
 */
//if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
//if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
    foreach ($_REQUEST as $key => $value) {
        if (substr($key, -2) == '_x' && !array_key_exists(substr($key, 0, -2), $_REQUEST)) {
            $newKey = substr($key, 0, -2);
            $_REQUEST[$newKey] = $value;
        }
    }
//}

