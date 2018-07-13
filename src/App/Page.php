<?php
namespace App\Page;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Page extends \Bs\Page
{


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        return $template;
    }
    
}