<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * A base component object.
 *
 * @package Com
 */
class Com_Web_Component extends Com_Web_Renderer implements Tk_Util_ControllerInterface
{
    
    /**
     * @var array
     * @TODO: Look into attaching events similar to the form events
     * @deprecated
     */
    private $events = array();
    
    /**
     * @var boolean
     */
    private $secure = false;
    
    /**
     * @var string/DOMNode
     */
    private $insertVar = '';
    
    /**
     * @var array
     */
    private $children = array();
    
    /**
     * @var Com_Web_Component
     */
    private $parent = null;
    
    /**
     * @var Com_Web_Component
     */
    private $page = null;
    
    /**
     * If this is false the execute/render methods will exit and not run
     * @var boolean
     */
    private $enabled = true;
    
    
    /**
     * @var Form
     * @deprecated
     */
    private $form = null;
    
    
    
    /**
     * __construct
     *
     *
     */
    function __construct()
    {
        $this->id = self::createId();
        $this->page = $this;
        $this->insertVar = get_class($this);
    }
    
    /**
     * This will return the {Name} part of the class if named acording to the TkLib convention
     * EG:
     * Ext_Modules_{Name}_Manager, Ext_Modules_{Name}_Edit, Ext_Modules_{Name}_View, Ext_Modules_{Name}_List, etc
     *
     * @return mixed
     * @deprecated Move to a more common class (Tk)?
     */
    function getName()
    {
        $arr = explode('_', get_class($this));
        return $arr[count($arr)-2];
    }
    
    
    /**
     * If enabled is set to false then the widget does not execute/render
     *
     * @param boolean $b
     */
    function setEnabled($b)
    {
        $this->enabled = ($b == true);
    }
    
    /**
     * Get the enabled status of this component
     *
     * @return boolean
     * @deprecated
     */
    function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * An alias for $this->setEnabled(false); to exit a widget
     *
     * <code>
     *   function init()
     *   {
     *     if (true) {
     *       return $this->disable();
     *     }
     *     ...
     *   }
     * </code>
     *
     * @return boolean
     * @deprecated
     */
    function disable()
    {
        $this->setEnabled(false);
        return false;
    }
    
    /**
     * The Component Event Engine Lies HERE!
     * Execute this component and its children
     * Only call this on the parent/page component, usualy in
     * a front controller
     *
     * @return boolean
     */
    function execute()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        
        Tk::log('Init Component: ' . get_class($this), Tk::LOG_INFO);
        $this->init();
        $eventExecuted = false;
        foreach ($this->events as $event => $method) {
            if (Tk_Request::exists($event)) {
                $this->executeEvent($method);
                $eventExecuted = true;
                break;
            }
        }
        if (!$eventExecuted) {
            $this->executeEvent('doDefault');
        }
        foreach ($this->children as $child) {
            $child->execute();
        }
        if ($this->form instanceof Form) {
            $this->form->execute(); // ?? ????
        }
        $this->postInit();
        return true;
    }
    
    /**
     * The Component Render Engine Lies HERE!
     * This fucntion calls all show methods and renders the widgets
     * Only call this on the parent/page component, usualy in
     * a front controller
     *
     * @return boolean
     */
    function render()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        foreach ($this->children as $child) {
            $child->render();
        }
        
        Tk::log('Render Component: ' . get_class($this), Tk::LOG_INFO);
        if ($this->form != null) {
            if ($this->form instanceof Form) {
            	$renderer = new Form_Renderer($this->form);
            	$this->insertRenderer($renderer, $this->form->getId());
            } else if ($this->form instanceof Com_Form_Object) {
                $formRenderer = new Com_Form_Renderer($this->form);
                $formRenderer->setTemplate($this->getTemplate());
                $formRenderer->show();
            }
        }
        
        if ($this->parent) {
            $this->parent->insertRenderer($this, $this->getInsertVar());
        }
        return true;
    }
    
    /**
     * Insert a Renderer template into the component
     * Returns the given $renderer object
     *
     * @param Dom_RendererInterface $renderer
     * @param string|DOMNode
     */
    function insertRenderer(Dom_RendererInterface $renderer, $insertVar = '')
    {
    	if ($insertVar == '') {
    		$insertVar = get_class($renderer);
    	}
        if ($renderer->getTemplate() === null) {
            $renderer->setTemplate($this->getTemplate());
        }
        $renderer->show();
        
        if ($renderer->getTemplate() !== $this->getTemplate()) {
            if ($insertVar instanceof DOMNode || $this->getTemplate()->keyExists('var', $insertVar)) {
                $this->getTemplate()->insertTemplate($insertVar, $renderer->getTemplate());
            }
        }
        return $renderer;
    }
    
    /**
     * Add a child component to this component
     * Returns the given $component object
     *
     * @param Com_Web_Component $component
     * @param string $var The template var where the child dom will be inserted.
     * @return Com_Web_Component
     */
    function addChild(Com_Web_Component $component, $insertVar = '')
    {
        $component->setParent($this);
        $component->setPage($this->page);
        if ($insertVar) {
            $component->insertVar = $insertVar;
        }
        $this->children[] = $component;
        return $component;
    }
    
    /**
     * The default show method.
     */
    function show() {}
    
    /**
     * Render all object vars with parameter names
     * NOTE: This only uses the template function replaceText...
     * 
     * @param Dom_Template $template
     * @param Tk_Object $obj
     * @experimental Still under development, as in testing....
     *   currently this generated to many errors, suggest using the Reflection object.
     */
    function showObj($template, $obj) 
    {
        $parameters = get_class_methods(get_class($obj));
        foreach ($parameters as $method) {
            if (preg_match('/^get([a-z]*)/i', $method, $regs)) {
                if (!isset($regs[1])) {
                    continue;
                }
                $var = lcFirst($regs[1]);
                if (method_exists($obj, $method)) {
                    try {
                        $template->insertText($var, $obj->$method());
                    } catch (Exception $e) { continue; }
                }
            }
        }
    }
    
    
    
    /**
     * The default init method
     *
     */
    function init() {}
    
    /**
     * Post init
     *
     */
    function postInit() {}
    
    /**
     * The default event handler.
     *
     */
    function doDefault() {}
    
    /**
     * SAet the insert var or node.
     * 
     * @param string|DOMNode $var 
     */
    function setInsertVar($var)
    {
        $this->insertVar;
    }
    
    /**
     * Execute this objects event and all sub object events.
     *
     * @param string $method
     * @return mixed
     */
    private function executeEvent($method)
    {
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }
    
    /**
     * Adds an event.
     *
     * Where $event is a parameter for the request. Events trigger the
     * call to $method. For example, if $event = 'submit' and the $method = 'doSubmit' the
     * doSubmit() method will be called if submit is found in the request.
     *
     * When executing returns at the first event found in the request. If
     * there are no events or no events found in the request the the
     * doDefault() method is called.
     *
     * @param string $event The request parameter key/name
     * @param string $method The method to execute
     * @return Form_Event
     */
    function addEvent($event, $method)
    {
        $this->events[$event] = $method;
        return $event;
    }
    
    /**
     * Return true if this page is SSL enabled.
     *
     * @return boolean
     */
    function isSecure()
    {
        foreach ($this->children as $child) {
            if ($child->isSecure()) {
                return true;
            }
        }
        return $this->secure;
    }
    
    /**
     * Set the SSL status of the page.
     * NOTE: It only takes one component to be secure
     * then the page will be redirected to the https://... url if not so already
     *
     * NOTE: For this to work you must have the SSL certificate installed
     * to the same directory as main website.
     *
     * @see Com_Web_ComponentController
     * @param boolean $b
     */
    function setSecure($b)
    {
        $this->secure = $b;
    }
    
    /**
     * Set the parent component
     *
     * @param Com_Web_Component $component
     */
    function setParent(Com_Web_Component $component)
    {
        $this->parent = $component;
    }
    
    /**
     * Get the parent component
     *
     * @return Com_Web_Component
     */
    function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Set the top most page component.
     *
     * @param Com_Web_Component $component
     */
    function setPage(Com_Web_Component $component)
    {
        $this->page = $component;
    }
    
    /**
     * Get the top most page component.
     * This is the base XHTML Template component. Use this component to
     * set any elements on the page template.
     * The code would look like:
     * <code>
     *   $component->getPage()->getTemplate()->insertText('var', 'text');
     * </code>
     *
     * @return Com_Web_Component
     */
    function getPage()
    {
        return $this->page;
    }
    
    /**
     * Is this the owner/page component.
     *
     * @return boolean
     */
    function isPage()
    {
        return ($this->page === null);
    }
    
    /**
     * Get the insert var name for the template
     * we will be inserting this component into
     *
     * @return string
     */
    function getInsertVar()
    {
        return $this->insertVar;
    }
    
    /**
     * Get the request key for an event. This will include the component id
     * This can be nessasery to avoid event collisions when using multiple
     * instances of a component.
     *
     * @param string $event
     * @return string
     * @deprecated
     */
    function getEventKey($event)
    {
        return $event . '_' . $this->getId();
    }
    
    /**
     * Hide this component, this adds a style tag style="display: none"
     * to the root node of the component HTML
     *
     * @return Com_Web_Component
     * @deprecated
     */
    function hide()
    {
        $this->getTemplate()->getDocument(false)->documentElement->setAttribute('style', 'display: none;');
        return $this;
    }
    
    /**
     * A factory method to generate new component id
     * This will allow for the id to be created in the constructor
     *
     * @param boolean $incrementIdx Set this to false to give child components the same ID as it parent
     * @return integer
     */
    static function createId()
    {
        static $idx = 0;
        return $idx++;
    }
    
    
    
    
    
    
    /**
     * Consider deprecating the methods below as they are not an
     * integral part of the component system. They are more utiliy
     * methods and should be implemented in higher level classes
     */
    
    
    
    
    
    
    
    /**
     * Set a form for this component. if set then the form is rendered automatically
     *
     * @param mixed $form One of the Com_Form_Object or Form objects
     * @deprecated
     */
    function setForm($form)
    {
        if (method_exists($form, 'setContainer')) {
            $form->setContainer($this);
        }
        
        $this->form = $form;
    }
    
    /**
     * Return the component form object.
     *
     * @return Form Returns null if not set
     * @deprecated
     */
    function getForm()
    {
        return $this->form;
    }
    
    /**
     * Get the widget class session context. This is a map that contains session data
     * for a widget class, not an instance so all class instances have access to this data.
     *
     * @return ArrayObject
     * @deprecated
     */
    function getSessionContext()
    {
        $context = new ArrayObject();
        if ($this->getSession()->exists('context_' . get_class($this)) && $this->getSession()->getParameter('context_' . get_class($this)) instanceof ArrayObject) {
            $context = $this->getSession()->getParameter('context_' . get_class($this));
        } else {
            $this->getSession()->setParameter('context_' . get_class($this), $context);
        }
        return $context;
    }
    
    
    
    /**
     * Get the user base directory for creating urls.
     *
     * @return Auth_Db_User
     * @deprecated use Auth::getUser()
     */
    function getUser()
    {
        $user = null;
        if (Tk::moduleExists('Auth')) {
            $user = Auth::getUser();
        } else {
            $user = Com_Auth::getInstance()->getUser();
        }
        return $user;
    }
    
    /**
     * Get the user base directory for creating urls.
     *
     * @return string
     * @deprecated Use Auth::getUserPath();
     */
    function getUserDir()
    {
        if (Tk::moduleExists('Auth')) {
            return Auth::getUserPath();
        }
        if ($this->getUser()) {
            return dirname($this->getUser()->getHomeUrl()->toString());
        }
        return '/';
    }
    
    
    
    /**
     * Add a top crumb for this component
     *
     * @param Tk_Type_Url $url
     * @param string $name
     * @deprecated
     */
    function addCrumbUrl(Tk_Type_Url $url, $name = '', $ignoreQueryString = true)
    {
        if ($this->getParent() !== $this->getPage()) {
            return;
        }
        
        if ($ignoreQueryString) {
            if (Tk_Request::requestUri()->getBasename() != $url->getBasename()) {
                if ($name == 'Index' || $name == 'Home') {
                    $this->getCrumbStack()->reset();
                }
                $this->getCrumbStack()->putUrl($url, $name);
            }
        } else {
            if (Tk_Request::requestUri()->toString() != $url->toString()) {
                if ($name == 'Index' || $name == 'Home') {
                    $this->getCrumbStack()->reset();
                }
                $this->getCrumbStack()->putUrl($url, $name);
            }
        }
    }
    
    /**
     * Get the topmost crumb url
     *
     * @return Tk_Type_Url
     * @deprecated
     */
    function getCrumbUrl()
    {
        $url = $this->getCrumbStack()->getCurrent();
        if ($url == null) {
            $url = new Tk_Type_Url('index.html');
        }
        return $url;
    }

    /**
     * Create a default crumb for this page and add it to the crumb list
     * @deprecated
     */
    function addDefaultCrumb()
    {
        if ($this->getRequest()->getReferer()) {
            $name = ucSplit(str_replace('.html', '', $this->getRequest()->getReferer()->getBasename()));
            if (strtolower($name) == 'index') {
                $name = 'Home';
            }
            $this->addCrumbUrl($this->getRequest()->getReferer(), $name);
        } else {
            $this->addCrumbUrl(Tk_Type_Url::createUrl('/index.html'), 'Home');
        }
    }
    
    /**
     * Get the crumbs object
     *
     * @return Com_Util_CrumbStack
     * @deprecated
     */
    function getCrumbStack($name = '_widgetCrumbs')
    {
        return Com_Util_CrumbStack::getInstance($name);
    }
    
    /**
     * Get the sites config object
     *
     * @return Com_Config
     * @deprecated
     */
    function getConfig()
    {
        return Com_Config::getInstance();
    }
    
    /**
     * Get the request object.
     *
     * @return Tk_Request
     * @deprecated
     */
    function getRequest()
    {
        return Tk_Request::getInstance();
    }
    
    /**
     * Get the current session object
     *
     * @return Tk_Session
     * @deprecated
     */
    function getSession()
    {
        return Tk_Session::getInstance();
    }
    
}