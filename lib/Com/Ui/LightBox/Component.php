<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * Display a processing Icon while dissabling the page so no input can take place
 *
 *
 *
 * @package Com
 */
class Com_Ui_LightBox_Component extends Com_Web_Component
{

    /**
     * @var Tk_Type_Url
     */
    private $backgroundImg = null;

    /**
     * @var string
     */
    private $html = '';

    /**
     * @var integer
     */
    private $width = 300;

    /**
     * @var integer
     */
    private $height = 300;

    /**
     * @var boolean
     */
    private $showHead = false;

    /**
     * @var string
     */
    private $headHtml = '';

    /**
     * @var string
     */
    private $footHtml = '';

    /**
     * Create the companent.
     *
     * @param Tk_Type_Url $html
     * @param integer $width
     * @param inetger $height
     * @param Tk_Type_Url $bgImage
     */
    function __construct($html, $width = 300, $height = 300, $bgImage = null)
    {
        parent::__construct();
        $this->html = $html;
        $this->width = $width;
        $this->height = $height;

        $this->backgroundImg = $bgImage;
        if ($this->backgroundImg == null) {
            $this->backgroundImg = new Tk_Type_Url('/lib/Com/Ui/LightBox/images/background.gif');
        }
    }

    /**
     * Auto make a template
     *
     * @return Dom_Template
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('
<table border="0" cellspacing="0" cellpadding="0" class="lbContainer" id="lbContainer_%s" onClick="return false;" style="background-image: url(\'%s\');display: none;" var="lbContainer">
  <tr>
    <td class="lbContainerWH" id="lbContainerWH_%s">
      <div class="lbLoader" id="lbLoader_%s" style="width: %spx;" var="lbLoader">
        <div class="lHead" choice="lHead">
          <p class="lHeadText" var="lHeadText"></p>
          <p class="lHeadClose"><a href="javascript:;" onclick="loff(%s);" title="Close">X</a></p>
          <div class="clear" />
        </div>
        <div class="lContent" var="lbContent" style="height: %spx;"></div>
        <div class="lFoot" var="lFoot" choice="lFoot"></div>
      </div>
    </td>
  </tr>
</table>', $this->getId(), $this->backgroundImg->toString(), $this->getId(), $this->getId(), $this->width, enquote($this->getId()), $this->height);

        $template = Com_Web_Template::load($xmlStr);
        return $template;
    }

    /**
     * Show
     *
     * @param Dom_Template $template
     */
    function show()
    {
        $template = $this->getTemplate();
        

        $template->replaceHTML('lbContent', $this->html);

        if (!$this->showHead) {
            $template->setChoice('lHead');
        }
        if ($this->headHtml != null) {
            $template->replaceHTML('lHeadText', $this->headHtml);
        }
        if ($this->footHtml != null) {
            $template->replaceHTML('lFoot', $this->footHtml);
            $template->setChoice('lFoot');
        }

        $pageTemplate = $this->getPage()->getTemplate();
        $js = sprintf("
  var img = new Image();
  img.src = '%s';
        ", $this->backgroundImg->toString());
        $pageTemplate->appendHeadElement('script', array('type' => 'text/javascript'), $js);

        $url = new Tk_Type_Url('/lib/Com/Ui/LightBox/images/lightBox.js');
        $pageTemplate->appendHeadElement('script', array('type' => 'text/javascript', 'src' => $url->toString()));

        $url = new Tk_Type_Url('/lib/Com/Ui/LightBox/images/lightBox.css');
        $pageTemplate->appendHeadElement('link', array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $url->toString()));
    }

    /**
     * Hide the close box/link
     *
     * @param boolean $b
     */
    function hideHead($b)
    {
        $this->showHead = $b;
    }

    function setHeadHtml($str)
    {
        $this->headHtml = $str;
    }

    function setFootHtml($str)
    {
        $this->footHtml = $str;
    }

}