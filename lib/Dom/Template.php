<?php
/*
 * @author Michael Mifsud
 * @author Darryl Ross
 * @link http://www.domtemplate.com/
 * @license Copyright 2007
 */


/**
 * A PHP5 DOM Template Library
 *
 * NOTE: `var` names should begin with '__' because they are
 *   considered reserved for the template system's internal functions.
 *
 *
 *
 * Caching: After long disscussions and a number of tests reguarding
 *   the caching of templates, it has been decided to not implement
 *   caching at this level. Developers can implement their own method
 *   of caching in their projects. This has been decided because the
 *   template system has been optimized for speed and there is a
 *   feeling that caching will introduce unrequired overhead.
 *
 * @package Dom
 */
class Dom_Template
{

    /**
     * Customised array of node names or attribute names to collect the nodes for.
     * For example:
     *   Node Name = 'module': All DOMElements with the name <module></module> will be captured
     *   Attr Name = '@attr-name': All DOMElements containing the attr name 'attr-name' will be captured
     *
     * This can be set statticly <b>after</b> the session is set.
     *
     * @var array
     */
    static $capture = array();

    /**
     * An array of all custom captured DOMElement objects
     * @var array
     */
    protected $captureList = array();

    /**
     * An array of var DOMElement objects
     * @var array
     */
    protected $var = array();

    /**
     * An array of choice DOMElement objects
     * @var array
     */
    protected $choice = array();

    /**
     * An array of repeat DOMElement objects
     * @var array
     */
    protected $repeat = array();

    /**
     * An array of form DOMElement objects
     * @var DOMElement
     */
    protected $form = array();

    /**
     * An array of formElement DOMElement objects
     * @var array
     */
    protected $formElement = array();

    /**
     * The head tag of a html page
     * @var DOMElement
     */
    protected $head = null;

    /**
     * The body tag of a html page
     * @var DOMElement
     */
    protected $body = null;

    /**
     * The head tag of a html page
     * @var DOMElement
     */
    protected $title = null;

    /**
     * @var array
     */
    protected $idList = array();

    /**
     * @var DOMDocument
     */
    protected $original = null;

    /**
     * @var DOMDocument
     */
    protected $document = null;

    /**
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * Header elements to be added
     * @var array
     */
    protected $headers = array();

    /**
     * Comment tags to be removed
     * @var array
     */
    protected $comments = array();

    /**
     * Set to true if this template has been parsed
     * @var boolean
     */
    protected $parsed = false;

    /**
     * Set to true if this template uses HTML5
     * @var boolean
     */
    protected $isHtml5 = false;

    /**
     * An internal list of nodes to delete after init()
     * @var array
     */
    private $delete = array();



    private $cdataRemove = true;





    /**
     * The constructor
     *
     * @param DOMDocument $doc
     * @param string $encoding
     */
    public function __construct($doc, $encoding = 'utf-8')
    {
        $this->init($doc, $encoding);
    }

    /**
     * Make a template from a file
     *
     * @param string $filename
     * @param string $encoding
     * @return Dom_Template
     */
    static function loadFile($filename, $encoding = 'utf-8')
    {
        if (!is_file($filename)) {
            throw new RuntimeException('Cannot locate XML/XHTML file: ' . $filename);
        }
        $html = file_get_contents($filename);
        $obj = self::load($html, $encoding);
        $obj->document->documentURI = $filename;
        return $obj;
    }

    /**
     * Make a template from a string
     *
     * @param string $html
     * @param string $encoding
     * @return Dom_Template
     */
    static function load($html, $encoding = 'utf-8')
    {
        $html = trim($html);
        if ($html == '' || $html[0] != '<') {
            throw new RuntimeException('Please supply a valid XHTML/XML string to create the DOMDocument.');
        }
        $isHtml5 = false;
        if ('<!doctype html>' == strtolower(substr($html, 0, 15))) {
            $isHtml5 = true;
            $html = substr($html, 16);
        }
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $r = $doc->loadXML(self::cleanXml($html, $encoding));
        if (!$r) {
            $str = '';
            foreach (libxml_get_errors() as $error) {
                $str .= sprintf("\n[%s:%s] %s", $error->line, $error->column, trim($error->message));
            }
            libxml_clear_errors();
            throw new RuntimeException($str);
        }
        $obj = new self($doc, $encoding);
        $obj->isHtml5 = $isHtml5;
        return $obj;
    }


    /**
     * Get the xml/html and return the cleaned string
     * A good place to clean any nasty html entities and other non valid XML/XHTML elements
     *
     * @param string $xml
     * @param string $encoding
     * @return string
     */
    static function cleanXml($xml, $encoding = 'UTF-8')
    {
	$xml = utf8_encode($xml);
        $list = get_html_translation_table(\HTML_ENTITIES, ENT_NOQUOTES, $encoding);
        $mapping = array();
        foreach ($list as $char => $entity) {
            $mapping[strtolower($entity)] = '&#' . self::ord($char) . ';';
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
    static private function ord($ch)
    {
        $k = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }





    /**
     * Reset the template to its unedited state
     *
     *
     */
    public function reset()
    {
        $this->init($this->original, $this->encoding);
    }

    /**
     * Reset and prepare the template object.
     * Mainly used for the Repeat objects
     * but could be usefull for your own methods.
     *
     * @param DOMDocument $doc
     * @param string $encoding
     */
    public function init($doc, $encoding = 'utf-8')
    {
        $this->var = array();
        $this->choice = array();
        $this->repeat = array();
        $this->form = array();
        $this->formElement = array();
        $this->idList = array();
        $this->headers = array();
        $this->comments = array();
        $this->head = $this->body = $this->title = null;
        $this->parsed = false;
        $this->delete = array();

        $this->original = clone $doc;
        $this->document = $doc;
        $this->encoding = $encoding;

        $this->prepareDoc($this->document->documentElement);

        foreach ($this->delete as $node) {
            $node->parentNode->removeChild($node);
        }
        $this->delete = array();
    }


    /**
     * A private method to initalise the template.
     *
     * @param DOMElement $node
     * @param string $form
     */
    private function prepareDoc($node, $form = '')
    {
        if ($this->isParsed()) {
            return;
        }
        if ($node->nodeType == XML_ELEMENT_NODE) {

            if (count(self::$capture)) {
                foreach (self::$capture as $name) {
                    if ($name[0] == '@') {
                        if ($node->hasAttribute(substr($name, 1))) {
                            $this->captureList[$name][] = $node;
                        }
                    } else {
                        if ($node->nodeName == $name) {
                            $this->captureList[$name][] = $node;
                        }
                    }
                }
            }

            // Store all Id nodes.
            if ($node->hasAttribute('id')) {
                $this->idList[$node->getAttribute('id')] = $node;
            }

            // Store all repeat regions
            if ($node->hasAttribute('repeat')) {
                //$this->repeat[$node->getAttribute('repeat')] = $node;
                $repeatName = $node->getAttribute('repeat');
                $node->removeAttribute('repeat');
                $this->repeat[$repeatName] = new Dom_Repeat($node, $this);
                return;
            }

            // Store all var nodes
            if ($node->hasAttribute('var')) {
                $varStr = $node->getAttribute('var');
                $arr = preg_split('/ /', $varStr);
                foreach ($arr as $var) {
                    if (!array_key_exists($var, $this->var)) {
                        $this->var[$var] = array();
                    }
                    $this->var[$var][] = $node;
                    $node->removeAttribute('var');
                }
            }

            // Store all choice nodes
            if ($node->hasAttribute('choice')) {
                if (!array_key_exists($node->getAttribute('choice'), $this->choice)) {
                    $this->choice[$node->getAttribute('choice')] = array();
                    $this->choice[$node->getAttribute('choice')]['node'] = array();
                    $this->choice[$node->getAttribute('choice')]['set'] = false;
                }
                $this->choice[$node->getAttribute('choice')]['node'][] = $node;
                $node->removeAttribute('choice');
            }

            // Store all Form nodes
            if ($node->nodeName == 'form') {
                $form = $node->getAttribute('id');
                if ($form == null) {
                    $form = $node->getAttribute('name');
                }
                $this->formElement[$form] = array();
                $this->form[$form] = $node;
            }

            // Store all FormElement nodes
            if ($node->nodeName == 'input' || $node->nodeName == 'textarea' || $node->nodeName == 'select') {
                $id = $node->getAttribute('name');
                if ($id == null) {
                    $id = $node->getAttribute('id');
                }
                if (!isset($this->formElement[$form][$id])) {
                    $this->formElement[$form][$id] = array();
                }
                $this->formElement[$form][$id][] = $node;
            }

            if ($node->nodeName == 'head') {
                $this->head = $node;
            }
            if ($node->nodeName == 'title' && $this->head) {
                $this->title = $node;
            }
            if ($node->nodeName == 'body') {
                $this->body = $node;
            }
            if (!$this->head) {
                if ($node->nodeName == 'script' || $node->nodeName == 'style' || $node->nodeName == 'link' || $node->nodeName == 'meta') {
                    $attrs = array();
                    foreach ($node->attributes as $k => $v) {
                        if ($k == 'var' || $k == 'choice' || $k == 'repeat') continue;
                        $attrs[$k] = $v->nodeValue;
                    }
                    $this->appendHeadElement($node->nodeName, $attrs, $node->textContent);
                    $this->delete[] = $node;
                    return;
                }
            }
            // iterate through the elements
            $children = $node->childNodes;
            foreach ($children as $child) {
                if ($child->nodeType == XML_COMMENT_NODE) {
                    $this->comments[] = $child;
                }
                $this->prepareDoc($child, $form);
            }
            $form = '';
        }
    }

    /**
     * Get the list of captured DOMElement nodes
     *
     * @return array
     */
    public function getCaptureList()
    {
        return $this->captureList;
    }

    /**
     * Get the current DOMDocument character encoding
     *
     * return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }


    /**
     * This is the output flag for the DomDocument
     * if self::OUTPUT_XML the toString() will use the saveXML() method of the DOMDocument
     * otherwise it will use the saveHTML().
     *
     * Notice: if using HTML5 the saveHTML() will be used always.
     *
     * @param type $mode
     * @return Dom_Template
     */
    public function setOutputMode($mode)
    {
        $this->output = $mode;
        return $this;
    }

    /**
     * Return the document file path if one exists.
     * For non file based tempaltes this value will be the same as dirname($_SERVER['PHP_SELF'])
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->document->documentURI;
    }

    /**
     * Replace the text of one or more var nodes
     *
     * @param string $var The var's name.
     * @param string $value The vars value inside the tags.
     * @return Dom_Template
     */
    public function insertText($var, $value)
    {
        if (!$this->isWritable('var', $var))
            return;

        $nodes = $this->findVar($var);

        foreach ($nodes as $node) {
            $this->removeChildren($node);
            if (is_object($value)) {
                $newNode = $this->document->createTextNode(self::objectToString($value));
            } else {
                $newNode = $this->document->createTextNode($value);
            }
            $node->appendChild($newNode);
        }
        return $this;
    }

    /**
     * Append the text of one or more var nodes
     *
     * @param string $var The var's name.
     * @param string $value The vars value inside the tags.
     * @return Dom_Template
     */
    public function appendText($var, $value)
    {
        if (!$this->isWritable('var', $var))
            return;

        $nodes = $this->findVar($var);
        foreach ($nodes as $node) {
            if (is_object($value)) {
                $newNode = $this->document->createTextNode(self::objectToString($value));
            } else {
                $newNode = $this->document->createTextNode($value);
            }
            $node->appendChild($newNode);
        }
        return $this;
    }

    /**
     * Get the text inside a var node.
     *
     * @param string $var
     * @return string
     */
    public function getText($var)
    {
        if (!$this->isWritable('var', $var))
            return '';
        $nodes = $this->findVar($var);
        return $nodes[0]->nodeValue;
    }

    /**
     * Add the class if it does not exist
     *
     * @param string $var
     * @param string $class
     * @return Dom_Template
     */
    public function addClass($var, $class)
    {
        $class = preg_replace('/\s?/', '', trim($class));
        $list = explode(' ', $this->getAttr($var, 'class'));
        foreach ($list as $c) {
            if ($c == $class)
                return;
        }
        $this->setAttr($var, 'class', trim($this->getAttr($var, 'class') . ' ' . $class));
        return $this;
    }

    /**
     * remove the class if it exists
     *
     * @param string $var
     * @param string $class
     * @return Dom_Template
     */
    public function removeClass($var, $class)
    {
        $class = preg_replace('/\s?/', '', trim($class));
        $str = $this->getAttr($var, 'class');
        $str = preg_replace('/(' . $class . ')\s?/', '', trim($str));
        $this->setAttr($var, 'class', $str);
        return $this;
    }

    /**
     * Retreive the text contained within an attribute of a node.
     *
     * @param string $var
     * @param string $attr
     * @return string
     */
    public function getAttr($var, $attr)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        if (count($nodes)) {
            return $nodes[0]->getAttribute($attr);
        }
        return '';
    }

    /**
     * Replace an attribute value.
     *
     * @param string $var
     * @param string $attr
     * @param string $value
     * @return Dom_Template
     */
    public function setAttr($var, $attr, $value)
    {
        if (!$this->isWritable('var', $var))
            return $this;
        $nodes = $this->findVar($var);

        foreach ($nodes as $node) {
            if ($value === null) {
                $node->removeAttribute($attr);
            } else {
                $node->setAttribute($attr, self::objectToString($value));
            }
        }
        return $this;
    }

    /**
     * Set a choice node to become visible in a document.
     *
     * @param string $choice The name of the choice
     * @return Dom_Template
     */
    public function setChoice($choice)
    {
        if (!$this->isWritable('choice', $choice))
            return;
        $this->choice[$choice]['set'] = true;
        return $this;
    }

    /**
     * Set a choice node to become invisible in a document.
     *
     * @param string $choice The name of the choice
     * @return Dom_Template
     */
    public function unsetChoice($choice)
    {
        if (!$this->keyExists('choice', $choice)) {
            $this->choice[$choice]['set'] = false;
        }
        return $this;
    }

    /**
     * Return a form object from the document.
     *
     * @param string $id
     * @return Dom_Form
     */
    public function getForm($id = '')
    {
        if (!$this->isWritable())
            return;
        $form = null;
        if (isset($this->form[$id])) {
            $form = $this->form[$id];
        }
        return new Dom_Form($form, $this->formElement[$id], $this);
    }

    /**
     * Get a repeating region from a document.
     *
     * @param string $repeat
     * @return Dom_Repeat
     */
    public function getRepeat($repeat)
    {
        if ($this->keyExists('repeat', $repeat)) {
            $obj = $this->repeat[$repeat];
            return clone $obj;
        }
    }

    /**
     * Get a var element node from the document.
     *
     * @param string $var
     * @return DOMElement
     */
    public function getVarElement($var)
    {
        $nodes = $this->findVar($var);
        if (is_array($nodes) && count($nodes)) {
            return $nodes[0];
        }
        return $nodes;
    }

    /**
     * Get the repeat node list
     *
     * @return array
     */
    public function getRepeatList()
    {
        return $this->repeat;
    }

    /**
     * Get the choice node list
     *
     * @return array
     */
    public function getChoiceList()
    {
        return $this->choice;
    }

    /**
     * Get a var element node from the document.
     * If no var name is provided the entire var array is returned.
     *
     * @param string $var
     * @return DOMNode[]
     */
    public function getVarList($var = '')
    {
        $nodes = $this->findVar($var);
        if (is_array($nodes)) {
            return $nodes;
        }
        return $this->var;
    }

    /**
     * Internal method to enable var to be a DOMElement or array of DOMElements....
     *
     * @param mixed $var
     */
    protected function findVar($var)
    {
        if (is_array($var)) {
            if (count($var) && current($var) instanceof DOMElement) {
                return $var;
            }
        }
        if ($var instanceof DOMElement) {
            return array($var);
        }
        if ($this->keyExists('var', $var)) {
            return $this->var[$var];
        }
    }

    /**
     * Get a DOMElement from the document based on its unique ID
     * ID attributes should be unique for XHTML documents, multiple names
     * are ignored and only the first node found is returned.
     *
     * @param string $id
     * @return DOMElement Returns null if not found
     */
    public function getElementById($id)
    {
        return $this->idList[$id];
    }

    /**
     * Return the head node if it exists.
     *
     * @return DOMElement
     */
    public function getHeadElement()
    {
        return $this->head;
    }

    /**
     * Return the current list of header nodes
     *
     * @return array
     */
    public function getHeaderList()
    {
        return $this->headers;
    }

    /**
     * Set the current list of header nodes
     *
     * @param array
     * @return Dom_Template
     */
    public function setHeaderList($arr)
    {
        $this->headers = $arr;
        return $this;
    }

    /**
     * Return the body node.
     *
     * @return DOMElement
     */
    public function getBodyElement()
    {
        return $this->body;
    }

    /**
     * Gets the page title text.
     *
     * @return string The title.
     */
    public function getTitleText()
    {
        return $this->title->nodeValue;
    }

    /**
     * Sets the document title text if available.
     *
     * @param string The title.
     * @return Dom_Template
     */
    public function setTitleText($value)
    {
        if (!$this->isWritable())
            return;
        if ($this->title == null) {
            throw new Exception('This document has no title node.');
        }
        $this->removeChildren($this->title);
        $this->title->nodeValue = self::objectToString($value);
        return $this;
    }

    /**
     * If a title tag exists it will be returned.
     *
     * @return DOMNode
     */
    public function getTitleElement()
    {
        return $this->title;
    }


    /**
     * Appends an element to the widgets of the HTML head element.
     *
     * In the form of:
     *  <$elementName $attributes[$key]="$attributes[$key].$value">$value</$elementName>
     *
     * NOTE: Only allows unique headers. An md5 hash is refrenced from all input parameters.
     *  Any duplicate headers are discarded.
     *
     * @param string $elementName
     * @param array $attributes An associative array of (attr, value) pairs.
     * @param string $value The element value.
     * @param boolean $overwrite Default false
     * @return Dom_Template
     */
    public function appendHeadElement($elementName, $attributes, $value = '', $overwrite = false)
    {
        if (!$this->isWritable())
            return;
        $preKey = $elementName . $value;
        foreach ($attributes as $k => $v) {
            $preKey .= $k . $v;
        }
        $hash = md5($preKey);
        if (!array_key_exists($hash, $this->headers) || $overwrite) {
            $this->headers[$hash]['elementName'] = $elementName;
            $this->headers[$hash]['attributes'] = $attributes;
            $this->headers[$hash]['value'] = self::objectToString($value);
        }
        return $this;
    }

    /**
     * Use this to add meta tags
     *
     * @param string $name
     * @param string $content
     * @param boolean $overwrite Default false
     * @return Dom_Template
     */
    public function appendMetaTag($name, $content, $overwrite = false)
    {
        return $this->appendHeadElement('meta', array('name' => $name, 'content' => $content), $overwrite);
    }

    /**
     * Append a CSS file to the template header
     *
     * @param string $urlString
     * @param string $media
     */
    public function appendCssUrl($urlString, $media = '')
    {
        if (!$this->isWritable())
            return;
        $attrs = array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $urlString);
        if ($media) {
            $attrs['media'] = $media;
        }
        $this->appendHeadElement('link', $attrs);
        return $this;
    }

    /**
     * Append some CSS text to the template header
     *
     * @param string $cssText
     * @param string $media
     * @return Dom_Template
     */
    public function appendCss($css, $media = '')
    {
        if (!$this->isWritable())
            return;
        $attrs = array('type' => 'text/css');
        if ($media) {
            $attrs['media'] = $media;
        }
        $this->appendHeadElement('style', $attrs, "\n" . $css . "\n");
        return $this;
    }

    /**
     * Append a Javascript file to the template header
     *
     * @param string $urlString
     * @param string $media
     * @return Dom_Template
     */
    public function appendJsUrl($urlString)
    {
        if ($this->isWritable())
            $this->appendHeadElement('script', array('type' => 'text/javascript', 'src' => $urlString));
        return $this;
    }

    /**
     * Append some CSS to the template header
     *
     * @param string $js
     * @param string $media
     * @return Dom_Template
     */
    public function appendJs($js)
    {
        if ($this->isWritable())
            $this->appendHeadElement('script', array('type' => 'text/javascript'), $js);
        return $this;
    }

    /**
     * Return the HTML/XML contents of a var node.
     * If there are more than one node with the same var name
     * the first one is selected by default.
     * Use the $idx if there is more than one var block
     *
     * @param string $var
     * @param integer $idx
     * @return string
     */
    public function innerHtml($var, $idx = 0)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        $html = $this->getHtml($var);
        $tag = $nodes[$idx]->nodeName;
        return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
    }

    /**
     * Return the html including the node contents
     *
     * @param string $var
     * @return string
     */
    public function getHtml($var)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($nodes[0], TRUE));
        $html = trim($doc->saveHTML());
        return $html;
    }

    /**
     * Insert HTML formatted text into a var element.
     *
     * @param string $var
     * @param string $html
     * @return Dom_Template
     * @warn bug exists where after insertion the template loses
     *   reference to the node in repeat regions. The fix (for now)
     *   is to just do all operations on that var node before this call.
     */
    public function insertHtml($var, $html)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $i => $node) {
            self::insertHtmlDom($node, $html, $this->encoding);
        }
        return $this;
    }

    /**
     * Static
     * Insert HTML formatted text into a dom element.
     *
     * @param DOMElement $element
     * @param string $html
     * @param string $encoding
     * @return DOMElement
     */
    static function insertHtmlDom($element, $html, $encoding = 'UTF-8')
    {
        if ($html == null) {
            return;
        }
        $id = "_c_o_n__";
        $elementDoc = $element->ownerDocument;
        while ($element->hasChildNodes()) {
            $element->removeChild($element->childNodes->item(0));
        }
        //$html = mb_convert_encoding($html, $encoding, 'UTF-8');
        $html = sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=%s"/></head><body><div xml:id="%s">%s</div></body></html>', $encoding, $id, $html);
        $doc = new DOMDocument();
        $doc->loadHTML(self::cleanXml($html, $encoding));
        $contentNode = $doc->getElementById($id);
        foreach ($contentNode->childNodes as $child) {
            $node = $elementDoc->importNode($child, true);
            $element->appendChild($node);
        }
        return $contentNode;
    }

    /**
     * Insert a DOMDocument into a var element
     * The var tag will not be replaced only its contents
     *
     * @param string $var
     * @param DOMDocument $doc
     * @return Dom_Template
     */
    public function insertDoc($var, DOMDocument $doc)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $node) {
            $this->removeChildren($node);
            if (!$doc->documentElement) continue;
            $newChild = $this->document->importNode($doc->documentElement, true);
            $node->appendChild($newChild);
        }
        return $this;
    }

    /**
     * Parse and Insert a template into a var element
     * The var tag will not be replaced only its contents
     *
     * This will also grab any headers in the supplied template.
     *
     * @param string $var
     * @param Dom_Template $template
     * @param boolean $parse Set to false to disable template parsing
     * @return Dom_Template
     */
    public function insertTemplate($var, Dom_Template $template, $parse = true)
    {
        if (!$this->isWritable('var', $var))
            return;
        $this->setHeaderList(array_merge($this->getHeaderList(), $template->getHeaderList()));
        return $this->insertDoc($var, $template->getDocument($parse));
    }

    /**
     * Replace HTML formatted text into a var element.
     *
     * @param string $var
     * @param string $html
     * @param boolean $preserveAttr Set to false to ignore copying of existing Attributes
     * @return Dom_Template
     */
    public function replaceHtml($var, $html, $preserveAttr = true)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $i => $node) {
            $newNode = self::replaceHtmlDom($node, $html, $this->encoding, $preserveAttr);
            if ($newNode) {
                $this->var[$var][$i] = $newNode;
            }
        }
        return $this;
    }

    /**
     * Replace a node with HTML formatted text.
     *
     * @param DOMElement $element
     * @param string $html
     * @param string $encoding
     * @param boolean $preserveAttr Set to false to ignore copying of existing Attributes
     * @return DOMElement
     * @todo There is entity issues here for some reason, need to test and fix....
     */
    static function replaceHtmlDom($element, $html, $encoding = 'UTF-8', $preserveAttr = true)
    {
        if ($html == null) {
            return;
        }
        $elementDoc = $element->ownerDocument;
        $id = "_c_o_n__";
        $html = sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=%s"/></head><body><div xml:id="%s">%s</div></body></html>', $encoding, $id, $html);
        $doc = new DOMDocument();
        $doc->loadHTML(self::cleanXml($html, $encoding));
        $contentNode = $doc->getElementById($id);
        $contentNode = $contentNode->firstChild;
        $contentNode = $elementDoc->importNode($contentNode, true);
        if ($element->hasAttributes() && $preserveAttr) {
            foreach ($element->attributes as $attr) {
                $contentNode->setAttribute($attr->nodeName, $attr->nodeValue);
            }
        }
        $element->parentNode->replaceChild($contentNode, $element);
        return $contentNode;
    }

    /**
     * Replace a node with the supplied DOMDocument
     * The DOMDocument's topmost node will be used to replace the destination node
     *
     * @param string $var
     * @param DOMDocument $doc
     * @param boolean $preserveAttr Set to false to ignore copying of existing Attributes
     * @return Dom_Template
     */
    public function replaceDoc($var, DOMDocument $doc, $preserveAttr = true)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);

        if (!$doc->documentElement) {
            return $this;
        }
        foreach ($nodes as $i => $node) {
            $newNode = $this->document->importNode($doc->documentElement, true);
            $node->parentNode->replaceChild($newNode, $node);
            if (is_string($var)) {
                $this->var[$var][$i] = $newNode;
            }
        }
        return $this;
    }

    /**
     * Replace a var node with the supplied Dom_Template
     * The DOMDocument's topmost node will be used to replace the destination node
     *
     * This will also copy any headers in the supplied template.
     *
     * @param string $var
     * @param Dom_Template $template
     * @param boolean $preserveAttr Set to false to ignore copying of existing Attributes
     * @return Dom_Template
     */
    public function replaceTemplate($var, Dom_Template $template, $preserveAttr = true)
    {
        if (!$this->isWritable('var', $var))
            return;
        if (!$template instanceof Dom_Template) {
            throw new Exception('Invalid Template Object');
        }
        $this->setHeaderList(array_merge($this->getHeaderList(), $template->getHeaderList()));
        return $this->replaceDoc($var, $template->getDocument(), $preserveAttr);
    }

    /**
     * Append HTML formatted text into a var element.
     *
     * @param string $var
     * @param string $html
     * @return Dom_Template
     */
    public function appendHtml($var, $html)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $i => $node) {
            self::appendHtmlDom($node, $html, $this->encoding);
        }
        return $this;
    }

    /**
     * Append HTML text into a dom node.
     *
     * @param DOMElement $element
     * @param string $html
     * @param string $encoding
     * @return boolean Returns true on success
     */
    static function appendHtmlDom($element, $html, $encoding = 'UTF-8')
    {
        if ($html == null) {
            return;
        }
        $id = "_c_o_n__";
        $elementDoc = $element->ownerDocument;
        $html = sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=%s"/></head><body><div xml:id="%s">%s</div></body></html>', $encoding, $id, $html);
        $doc = new DOMDocument();
        $doc->loadHTML(self::cleanXml($html, $encoding));

        $contentNode = $doc->getElementById($id);
        foreach ($contentNode->childNodes as $child) {
            $node = $elementDoc->importNode($child, true);
            $element->appendChild($node);
        }
        return $contentNode;
    }

    /**
     * Append documents to the var node
     *
     * @param string $var
     * @param DOMDocument $doc
     * @return Dom_Template
     */
    public function appendDoc($var, DOMDocument $doc)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $el) {
            $node = $this->document->importNode($doc->documentElement, true);
            $el->appendChild($node);
        }
        return $this;
    }

    /**
     * Append a template to a var element, it will parse the template before appending it
     * This will also copy any headers in the $template.
     *
     * @param string $var
     * @param Dom_Template $template
     * @return Dom_Template
     */
    public function appendTemplate($var, Dom_Template $template)
    {
        if (!$this->isWritable('var', $var))
            return;
        $this->setHeaderList(array_merge($this->getHeaderList(), $template->getHeaderList()));
        return $this->appendDoc($var, $template->getDocument());
    }

    /**
     * Prepend a template to a var element, it will parse the template before appending it
     * This will also copy any headers in the $template.
     *
     * @param string $var
     * @param Dom_Template $template
     * @return Dom_Template
     */
    public function prependTemplate($var, Dom_Template $template)
    {
        if (!$this->isWritable('var', $var))
            return;
        $this->setHeaderList(array_merge($this->getHeaderList(), $template->getHeaderList()));
        return $this->prependDoc($var, $template->getDocument());
    }

    /**
     * Prepend documents to the var node
     *
     * @param string $var
     * @param DOMDocument $doc
     * @return Dom_Template
     */
    public function prependDoc($var, DOMDocument $doc)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $el) {
            $node = $this->document->importNode($doc->documentElement, true);
            if ($el->firstChild) {
                $el->insertBefore($node, $el->firstChild);
            } else {
                $el->appendChild($node);
            }
        }
        return $this;
    }

    /**
     * Append HTML formatted text into a var element.
     *
     * @param string $var
     * @param string $html
     * @return Dom_Template
     */
    public function prependHtml($var, $html)
    {
        if (!$this->isWritable('var', $var))
            return;
        $nodes = $this->findVar($var);
        foreach ($nodes as $i => $node) {
            self::prependHtmlDom($node, $html, $this->encoding);
        }
        return $this;
    }

    /**
     * Append HTML text into a dom node.
     *
     * @param DOMElement $element
     * @param string $html
     * @param string $encoding
     * @return boolean Returns true on success
     */
    static function prependHtmlDom($element, $html, $encoding = 'UTF-8')
    {
        if ($html == null) {
            return;
        }
        $id = "_c_o_n__";
        $elementDoc = $element->ownerDocument;
        $html = sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=%s"/></head><body><div xml:id="%s">%s</div></body></html>', $encoding, $id, $html);
        $doc = new DOMDocument();
        $doc->loadHTML(self::cleanXml($html, $encoding));

        $contentNode = $doc->getElementById($id);
        foreach ($contentNode->childNodes as $child) {
            $node = $elementDoc->importNode($child, true);
            if ($element->firstChild) {
                $element->insertBefore($node, $element->firstChild);
            } else {
                $element->appendChild($node);
            }
        }
        return $contentNode;
    }

    /**
     * Get the parsed state of the template.
     * If true then no more changes can be made to the template
     *
     * @return boolean
     */
    public function isParsed()
    {
        return $this->parsed;
    }

    /**
     * Return a parsed Dom document.
     * After using this call you can no longer use the template render functions
     * as no changes will be made to the template unless you use DOM functions
     *
     * @param boolean $parse Set to false to avoid parsing and return DOMDocument in its current state
     * @return DOMDocument
     */
    public function getDocument($parse = true)
    {
        if (!$this->isParsed() && $parse) {
            foreach ($this->comments as $node) {
                // Keep the IE comment control statements
                if (preg_match('/^\[if /', $node->nodeValue)) {
                    continue;
                }
                if ($node->ownerDocument != null && $node != null && $node->parentNode != null &&
                            $node->parentNode->nodeName != 'script' && $node->parentNode->nodeName != 'style') {
                    $node->parentNode->removeChild($node);
                }
            }
            foreach ($this->repeat as $name => $repeat) {
                $node = $repeat->getRepeatNode();
                $node->parentNode->removeChild($node);
                unset($this->repeat[$name]);
            }
            foreach ($this->choice as $name => $nodes) {
                if (!$nodes['set']) {
                    foreach ($nodes['node'] as $node) {
                        if ($node != null && $node->parentNode != null) {
                            $node->parentNode->removeChild($node);
                        }
                    }
                }
                unset($this->choice[$name]);
            }

            if ($this->head) {
                $hookNode = null;
                if ($this->title != null && $this->title->nextSibling != null) {
                    $hookNode = $this->title->nextSibling;
                }
                if ($hookNode == null && $this->head->firstChild != null) {
                    $hookNode = $this->head->firstChild;
                }
                $ordered = array();
                $meta = array();
                $other = array();
                $js = '';
                $css = '';
                foreach ($this->headers as $i => $header) {
                    if ($header['elementName'] == 'meta') {
                        $meta[] = $header;
                    } else if ($header['elementName'] == 'script' && !isset($header['href']) && trim($header['value'])) {
                        $js .= $header['value'] ."\n";
                    } else if ($header['elementName'] == 'style' && !isset($header['src']) && trim($header['value'])) {
                        $css .= $header['value'] ."\n";
                    } else {
                        $other[] = $header;
                    }
                }
                // Place JS and CSS in one header tag each not multiple tags

                if ($js) {
                    $other[] = array('elementName' => 'script', 'value' => $js, 'attributes' => array('type' => 'text/javascript'));
                }
                if ($css) {
                    $other[] = array('elementName' => 'style', 'value' => $css, 'attributes' => array('type' => 'text/css'));
                }
                $ordered = array_merge($meta, $other);
                foreach ($ordered as $header) {
                    // Insert into template
                    $node = $this->document->createElement($header['elementName']);
                    if ($header['value'] != null) {
                        $ct = $this->document->createCDATASection("\n" . trim($header['value']) . "\n" );
                        $node->appendChild($ct);
                        //$node->nodeValue = "\n" . trim($header['value']) . "\n";
                    }
                    foreach ($header['attributes'] as $k => $v) {
                        $node->setAttribute($k, self::objectToString($v));
                    }
                    if ($hookNode) {
                        $this->head->insertBefore($node, $hookNode);
                    }else {
                        $this->head->appendChild($node);
                    }
                    $nl = $this->document->createTextNode("\n");
                    $node->parentNode->insertBefore($nl, $node);
                }
            }

            $this->parsed = true;





        }
        return $this->document;
    }

    /**
     * Removes all children from a node.
     *
     * @param DOMNode $node
     */
    protected function removeChildren($node)
    {
        while ($node->hasChildNodes()) {
            $node->removeChild($node->childNodes->item(0));
        }
    }

    /**
     * Check if a repeat,choice,var,form (template property) Exists.
     *
     * @param string $property
     * @param string $key
     */
    public function keyExists($property, $key)
    {
        if (!array_key_exists($key, $this->$property)) {
            return false;
        }
        return true;
    }

    /**
     * Check if a repeat,choice,var,form (template property) exist,
     * and if the document has ben parsed.
     *
     *
     * @param string $property
     * @param string $key
     */
    public function isWritable($property = '', $key = '')
    {
        if ($this->isParsed())
            return false;
        if ($property && $key && is_string($key)) {
            if (!$this->keyExists($property, $key))
                return false;
        }
        return true;
    }

    /**
     * Return a string from an object.
     *
     * @param mixed $obj
     */
    static function objectToString($obj)
    {
        if (is_object($obj) && method_exists($obj, 'toString')) {
            return $obj->toString();
        } else if (is_object($obj) && method_exists($obj, '__toString')) {
            return $obj->__toString();
        } else {
            return $obj;
        }
    }

    /**
     * Recive the document in the format of 'xml' or 'html'.
     *
     * @param boolean $parse parse the document
     * @return string
     */
    public function toString($parse = true)
    {

        $doc = $this->getDocument($parse);
        $str = $doc->saveXML($doc->documentElement);
        // Cleanup Document
        if (substr($str, 0, 5) == '<?xml') {    // Remove xml declaration
            $str = substr($str, strpos($str, "\n") + 1);
        }
        if ($this->isHtml5 && strtolower(substr($str, 0, 15)) != '<!doctype html>') {
            $str = "<!DOCTYPE html>\n" . $str;
        }
        // fix allowable non closeable tags
        $str = preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', 
          function ($m) {
            $xhtml_tags = array("br", "hr", "input", "frame", "img", "area", "link", "col", "base", "basefont", "param", "meta");
            return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />" : "<$m[1]$m[2]></$m[1]>";
          }, $str );
        
        if ($this->cdataRemove)
            $str = str_replace(array('><![CDATA[', ']]><'), array('>', '<'), $str);
        if ($this->newlineReplace)
            $str = preg_replace ('/\s+$/m', "\n", $str);
        
        return $str;
    }

    /**
     * Return a string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

}

/**
 * A repeat region is a sub template of a parent templates nodes.
 *
 * @package Dom
 */
class Dom_Repeat extends Dom_Template
{

    /**
     * @var DOMElement
     */
    protected $repeatNode = null;

    /**
     * @var string
     */
    protected $repeatName = '';

    /**
     * @var Dom_Template
     */
    protected $repeatParent = null;



    /**
     * __construct
     *
     * @param DOMElement $node
     * @param Dom_Template $parent
     *
     * @todo It would be good to send the repeat node as a parameter
     *       but we need access to the node name, ideas??
     */
    public function __construct($node, Dom_Template $parent)
    {
        $this->repeatNode = $node;
        $this->repeatName = $node->getAttribute('repeat');
        $this->repeatParent = $parent;

        $repeatDoc = new DOMDocument();
        $tplNode = $repeatDoc->importNode($node, true);
        $repeatDoc->appendChild($tplNode);

        parent::__construct($repeatDoc, $parent->getEncoding());
    }

    /**
     * Re init the template when clone is called
     */
    public function __clone()
    {
        $this->init(clone $this->original, $this->encoding);
    }

    /**
     * Append a repeating region to the document.
     * Repeating regions are appended to the supplied var.
     * If the var is null or '' then the repeating region is appended
     * to is original location in the parent template.
     *
     * @param string $var
     * @return DOMElement The inserted node
     */
    public function appendRepeat($var = '')
    {
        if (!$this->isWritable())
            return;

        $this->repeatParent->setHeaderList(array_merge($this->repeatParent->getHeaderList(), $this->getHeaderList()));

        $appendNode = $this->repeatNode;
        if ($var && $this->repeatParent) {
            $appendNode = $this->repeatParent->getVarElement($var);
        }
        $parentDoc = $appendNode->ownerDocument;
        $insertNode = $parentDoc->importNode($this->getDocument()->documentElement, true);

        if ($appendNode->parentNode) {
            if (!$var) {
                $appendNode->parentNode->insertBefore($insertNode, $appendNode);
                return $insertNode;
            }
        }

        $appendNode->appendChild($insertNode);
        return $insertNode;
    }

    /**
     * Alias to appendRepeat()
     * This method will be remove in future versions
     *
     * @param type $var
     * @deprecated Please use Dom_Template::appendRepeat($var)
     */
    public function append($var='')
    {
        $this->appendRepeat($var);
    }

    /**
     * Return a repeat node...
     *
     * @return DOMElement
     */
    public function getRepeatNode()
    {
        return $this->repeatNode;
    }

    /**
     * get teh parent template this repeat belongs to.
     *
     * @return Dom_Template
     */
    public function getTemplate()
    {
        return $this->repeatParent;
    }

}
