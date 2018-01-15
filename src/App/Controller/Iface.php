<?php
namespace App\Controller;

/**
 * Class Iface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Iface extends \Tk\Controller\Iface
{
    /**
     * @return string
     * @todo: we should come up with a more solid routing naming convention
     */
    public function getDefaultTitle()
    {
        $replace = array('admin-', 'client-', 'staff-', 'student-', '-base');
        /** @var \Tk\Request $request */
        $request = $this->getConfig()->getRequest();
        if ($request) {
            $routeName = $request->getAttribute('_route');
            $routeName = str_replace($replace, '', $routeName);
            return ucwords(trim(str_replace('-', ' ', $routeName)));
        }
        return '';
    }

    /**
     * Get a new instance of the page to display the content in.
     *
     * @return \App\Page\Iface
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = new \App\Page\Page();
        }
        return $this->page;
    }

    /**
     * Get the currently logged in user
     *
     * @return \App\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * @return \Tk\Config|\App\Config
     */
    public function getConfig()
    {
        return parent::getConfig();
    }

    /**
     * DomTemplate magic method example
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div></div>
HTML;
        return \Dom\Loader::load($html);
        // OR FOR A FILE
        //return \Dom\Loader::loadFile($this->getTemplatePath().'/public.xtpl');
    }

    

}