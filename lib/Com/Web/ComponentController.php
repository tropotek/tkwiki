<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * This controller implements the components systems and executes the pages components
 *
 *
 * @package Com
 */
class Com_Web_ComponentController extends Tk_Object implements Tk_Util_ControllerInterface
{

    /**
     * @var Tk_Web_SiteFrontController
     */
    protected $siteController = null;

    /**
     * @var Tk_Web_ResourceMapper
     */
    protected $resourceMapper = null;

    /**
     * @var Com_Web_Component
     */
    protected $pageComponent = null;

    /**
     * @var Com_Web_NodeModifierController
     */
    protected $nodeModifier = null;

    /**
     * Hack to not load node modifiers, should have use observer patterns.....lol
     * @var boolean
     */
    protected $addNodeMod = true;

    /**
     * __construct
     *
     * @param Tk_Web_SiteFrontController $resourceMapper
     * @param Com_Web_ResourceMapper $resourceMapper (optional) Used for locating page templates
     */
    function __construct(Tk_Web_SiteFrontController $siteController, $resourceMapper = null, $addNodeMod = true)
    {
        if ($resourceMapper == null) {
            $resourceMapper = new Com_Web_ResourceMapper(new Tk_Type_Path(Com_Config::getTemplatePath()));
        }
        $this->addNodeMod = $addNodeMod;
        $this->resourceMapper = $resourceMapper;
        $this->siteController = $siteController;
        $this->nodeModifier = new Com_Web_NodeModifierController($this);
        Tk::log('Initalising Component Controller', Tk::LOG_INFO);
    }

    /**
     * Add a node modifier object to be iterated through by the NodeModifierController
     *
     * @param Com_Web_NodeModifierInterface $mod
     */
    function addNodeModifier(Com_Web_NodeModifierInterface $mod)
    {
        $this->nodeModifier->add($mod);
    }

    /**
     * Do all pre-initalisation operations
     * This method should be called before the execution method is called
     *
     */
    function init()
    {
        // Check request type and if css, js, image, media just serve it and exit
        if ($this->isMediaFile(Tk_Request::getInstance()->getRequestUri())) {
            $this->serveRequestFile(Tk_Request::getInstance()->getRequestUri());
        }
    }

    /**
     * Execute and render the components
     *
     */
    function execute()
    {
        Tk::log('Executing component controller', Tk::LOG_INFO);
        // Get the page template path using the resource mapper
        $templatePath = $this->resourceMapper->getResourcePath(Tk_Request::getInstance()->getRequestUri());

        // Create page Component
        $this->pageComponent = $this->createComponents($templatePath);

        if ($this->pageComponent == null) {
            $this->siteController->set404();
        }
        //$isSecure = (Com_Config::isSslEnabled() && $this->pageComponent->isSecure());
        $isSecure = Com_Config::isSslEnabled();
        $this->secureRedirect($isSecure);

        // Check if data directory writable
        $dataDir = Com_Config::getDataPath();
        if (!is_dir($dataDir) || !is_writable($dataDir)) {
            mkdir($dataDir);
        }
        if (!is_dir($dataDir) || !is_writable($dataDir)) {
            $body = $this->pageComponent->getTemplate()->getBodyElement();
            $div = $body->ownerDocument->createElement('div', "Warning: The Data directory `$dataDir' is not writable. Please use the command `chmod -R 777 $dataDir`");
            $div->setAttribute('style', 'font-size: 10px;border: 1px outset #F66; color: #333; background-color: #FCC;padding: 2px 4px;font-family: arial,sans-serif;');
            $body->insertBefore($div, $body->firstChild);
        }
    }

    /**
     * Do all post initalisation operations here
     * This function should be called after the execute method has been called
     *
     */
    function postInit()
    {
        $this->pageComponent->execute();
        $this->pageComponent->render();
        $template = $this->pageComponent->getTemplate();

        $template->appendMetaTag('generator', 'Tropotek Development - DomTemplate');
        $template->appendMetaTag('copyright', '(c)' . date('Y') . ' tropotek.com.au');

        if (Com_Config::isDebugMode()) {
            $title = htmlentities($this->pageComponent->getTemplate()->getTitleText());
            $this->pageComponent->getTemplate()->setTitleText('Debug: ' . $title);
            //$this->getPageComponent()->getTemplate()->getBodyElement()->setAttribute('style', 'border: 1px dashed #ccc;border-width: 2px 2px 0px 2px;');
        }
        if ($this->addNodeMod) {
            $this->nodeModifier->add(new Com_Web_NodeModifierPath());
            //$this->nodeModifier->add(new Com_Web_NodeModifierJs());       // Under construction
        }
        $this->nodeModifier->execute();    // Template parsed beyond this point.
        // Output the final rendered page
        Tk_Response::write($this->pageComponent->getTemplate()->toString('xml', true, true));
    }

    /**
     * Create the components from the Web_Factory object
     *
     * @param Tk_Type_Path $templatePath
     * @return Com_Web_Component
     */
    protected function createComponents(Tk_Type_Path $templatePath)
    {
        Tk::log('Creating Components: ' . $templatePath->getRalativeString(), Tk::LOG_INFO);
        // Return page if template exists
        if ($templatePath->isFile()) {
            return Com_Web_Factory::getInstance()->createPage($templatePath);
        }
        // Get Dynamic Page if available
        $pageData = null;
        //vd(Com_Config::getInstance()->getHtmlTemplates(), '', $templatePath->toString());
        $subPathStr = str_replace(Com_Config::getTemplatePath(), '', $templatePath->toString());
        if (substr($subPathStr, -5) != '.html') {
            $subPathStr .= '/index.html';
        }
        //vd(Com_Config::gettemplatePath(), '', $templatePath->toString(),$subPathStr);
        $adminPath = str_replace(array('/', '.'), array('\/', '\.'), Com_Config::getAdminPath());
        if ($adminPath) {
            $subPathStr = preg_replace('/^' . $adminPath . '\//', '/admin/', $subPathStr);
        }

        $pageList = Com_Config::getDynamicPages();
        if (array_key_exists($subPathStr, $pageList)) {
            $pageData = $pageList[$subPathStr];
            if (is_dir(Com_Config::getTemplatePath() . '/Defaults')) { // Old way
                $templatePath = new Tk_Type_Path(Com_Config::getTemplatePath() . '/Defaults' . $pageData->getTemplatePath());
            } else {  // New Way
                $templatePath = new Tk_Type_Path(Com_Config::getTemplatePath() . $pageData->getTemplatePath());
            }
        } else {
            $this->siteController->set404();
            throw new Tk_Exception('Page Not Found!');
        }
        $page = Com_Web_Factory::getInstance()->createPage($templatePath);
        if ($page && $pageData) {
            $method = 'create' . $pageData->getClassname();
            if (method_exists(Com_Web_Factory::getInstance(), $method)) {
                $com = Com_Web_Factory::getInstance()->$method($pageData);
            } else {
                $com = Com_Web_Factory::getInstance()->createDefaultComponent($pageData);
            }
            if (!$com) {
                return $page;
            }
            Tk::log('Creating Dynamic Component: ' . $pageData->getClassname(), Tk::LOG_INFO);
            $template = Com_Web_Factory::getInstance()->getDefaultTemplate($pageData);
            if ($template) {
                $com->setTemplate($template);
            }
            $page->addChild($com, $pageData->getInsertVar());
            if ($com->getTemplate()) {
                Com_Web_Factory::getInstance()->createComponents($com);
            }

            foreach ($pageData->getParameters() as $k => $v) {
                $method = 'set' . ucfirst($k);
                if (method_exists($com, $method)) {
                    $com->$method($v);
                } else {
                    Tk::log('Parameter setter `' . $method . '();` does not exist.', TK::LOG_ERROR);
                }
            }
        }
        return $page;
    }

    /**
     * Get the Master page component containing all other components
     *
     * @return Com_Web_Component
     */
    function getPageComponent()
    {
        return $this->pageComponent;
    }

    /**
     * Get the Resource Mapper object
     *
     * @return Com_Web_ResourceMapper
     */
    function getResourceMapper()
    {
        return $this->resourceMapper;
    }

    /**
     * Check the page and redirect to secure/unsecure as nessacery
     *
     * @param boolean $isSecure Is this a secure page
     */
    function secureRedirect($isSecure)
    {
        $requestUri = Tk_Request::requestUri();
        if ($requestUri->getScheme() == 'https' && !$isSecure) {
            $requestUri->setScheme('http');
            $requestUri->redirect();
        } elseif ($requestUri->getScheme() == 'http' && $isSecure) {
            $requestUri->setScheme('https');
            $requestUri->redirect();
        }
    }

    /**
     * Test if the file is a media file and not a HTML TEMPLATE
     * Returns true if the file is a media file
     *
     * @return boolean
     */
    private function isMediaFile(Tk_Type_Url $requestUri)
    {
        $reqPath = urldecode($requestUri->getPath());
        if (strlen(Com_Config::getHtdocRoot()) > 1) {
            $reqPath = str_replace(Com_Config::getHtdocRoot(), '', $reqPath);
        }

        if (preg_match('/\.(html|htm|php)$/', $reqPath) || $reqPath == '/' || $reqPath == '' || (!strstr(basename($reqPath), '.'))) {
            return false;
        }
        if (is_dir(Com_Config::getSitePath() . $reqPath)) {
            return false;
        }
        $ad = Com_Config::getAdminPath();
        if (substr($ad, -1) != '/') {
            $ad .= '/';
        }
        if ($ad == $reqPath || substr($ad, 0, -1) == $reqPath) {
            return false;
        }

        return true;
    }

    /**
     * This is used inplace of the .htaccess it serves requested media from the template folder
     * Files such as images, flash, css, javascript, etc
     *
     * If you have mod-rewrite enabled us ,htaccess instead as it is faster:
     * <code>
     *   RewriteEngine On
     *   RewriteBase /
     *
     *   # link directly to the template folder
     *   # NOTE: change path to the template folder (this case `html/`)
     *   RewriteCond %{REQUEST_URI} !^(.*)/lib/(.*)$ [NC]
     *   RewriteRule (css|js|images|media)/(.*) html/$1/$2 [NC,L]
     * </code>
     *
     */
    private function serveRequestFile(Tk_Type_Url $requestUri)
    {
        $reqPath = urldecode($requestUri->getPath());
        if (substr($reqPath, 0, strlen(Com_Config::getHtdocRoot())) != Com_Config::getHtdocRoot()) {
            $this->siteController->set404();
        }

        $reqPath = str_replace(array('./', '../'), '', $reqPath);
        if (strlen(Com_Config::getHtdocRoot()) > 1) {
            $reqPath = str_replace(Com_Config::getHtdocRoot(), '', $reqPath);
        }
        $templatePath = Com_Config::getTemplatePath();
        if (substr($templatePath, -1) != '/') {
            $templatePath .= '/';
        }
        $resourcePath = $templatePath . $reqPath;
        if (!is_file($resourcePath)) {
            $this->siteController->set404();
        }
        header('Cache-control: private');
        header('Content-Type: ' . getFileMimeType($resourcePath));
        header('Content-Length: ' . filesize($resourcePath));
        $this->readfileChunked($resourcePath);
        exit();
    }

    /**
     * This method reads and serves the media file in chunks as to avoid memory issues
     *
     * @return mixed Returns number of bytes served if successfull false on fail.
     */
    private function readfileChunked($filename, $retbytes = true)
    {
        $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
        $buffer = '';
        $cnt = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt;
        }
        return $status;
    }

}
