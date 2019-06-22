<?php
namespace App\Controller\Admin;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Settings extends \Bs\Controller\Admin\Settings
{

    /**
     * init the form and other stuff before form->execute()
     * @throws Form\Exception
     */
    public function initForm(\Tk\Request $request)
    {

        $tab = 'Site';

        $this->getForm()->appendField(new Field\File('site.logo', '/site'), 'site.short.title')->setTabGroup($tab)->setLabel('Site Logo')
            ->addCss('tk-imageinput')->setAttr('accept', '.png,.jpg,.jpeg,.gif');

        $tab = 'Wiki Config';
        $this->getForm()->appendField(new \App\Form\ButtonInput('wiki.page.default', 'fa fa-folder-open'))->setTabGroup($tab)
            ->setLabel('Home Page')->setNotes('The default wiki home page URL');

        $this->getForm()->appendField(new Field\Checkbox('wiki.page.home.lock'))->setTabGroup($tab)->setLabel('Lock Home Page')
            ->setNotes('Only Allow Admin to edit the home page');
//        $this->getForm()->appendField(new Field\Checkbox('site.user.registration'))->setTabGroup($tab)->setLabel('User Registration')
//            ->setNotes('Allow users to create new accounts');
//        $this->getForm()->appendField(new Field\Checkbox('site.user.activation'))->setTabGroup($tab)->setLabel('User Activation')
//            ->setNotes('Allow users to activate their own accounts');
        $this->getForm()->appendField(new Field\Checkbox('site.page.header.hide'))->setTabGroup($tab)->setLabel('Hide Header Info')
            ->setNotes('Hide the page header info from public view.');
        $this->getForm()->appendField(new Field\Checkbox('site.page.header.title.hide'))->setTabGroup($tab)->setLabel('Hide Header Title')
            ->setNotes('Hide the page header title from public view.');

        $this->getForm()->addEventCallback('update', array($this, 'doWikiSubmit'));
        $this->getForm()->addEventCallback('save', array($this, 'doWikiSubmit'));

    }

    /**
     * @param Form $form
     * @throws Form\Exception
     * @throws \Tk\Exception
     */
    public function doWikiSubmit($form)
    {

        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('site.logo');
        $logo->isValid();
        
        if ($form->hasErrors()) {
            return;
        }
        
        $logo->saveFile();
        if ($logo->hasFile()) {
            $fullPath = $this->getConfig()->getDataPath() . $logo->getValue();
            \Tk\Image::create($fullPath)->bestFit(256, 256)->save();
            $this->getData()->set('site.logo', $logo->getValue());
            // Create favicon
            $rel1 = '/site/favicon.' . $logo->getUploadedFile()->getClientOriginalExtension();
            \Tk\Image::create($fullPath)->squareCrop(16)->save($this->getConfig()->getDataPath() . $rel1);
            $this->getData()->set('site.favicon', $rel1);
        }
        $this->getData()->save();
    }

    /**
     * init the action panel
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Plugins', \Bs\Uri::createHomeUrl('/plugins.html'), 'fa fa-plug'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Users', \Bs\Uri::createHomeUrl('/userManager.html'), 'fa fa-users'));
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();
        
        // Render select page dialog
        $pageSelect = new \App\Helper\PageSelect('#fid_btn_settings-wiki\\\\.page\\\\.default', '#settings-wiki\\\\.page\\\\.default');
        $pageSelect->show();
        $template->appendTemplate('content', $pageSelect->getTemplate());

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="tk-panel" data-panel-title="Settings" data-panel-icon="fa fa-cogs" var="form"></div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }
}