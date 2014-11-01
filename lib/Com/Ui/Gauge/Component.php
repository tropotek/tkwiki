<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */

/**
 * This component displays a smal singular gauge graph
 *
 *
 * @package Com
 */
class Com_Ui_Gauge_Component extends Com_Web_Component
{
    
    const IMG_EMPTY = '/empty.gif';
    const IMG_EMPTY_BEGIN = '/begin-empty.gif';
    const IMG_EMPTY_END = '/end-empty.gif';
    
    const IMG_FILLED = '/filled.gif';
    const IMG_FILLED_BEGIN = '/begin-filled.gif';
    const IMG_FILLED_END = '/end-filled.gif';
    
    const IMG_OVER = '/over.gif';
    const IMG_OVER_BEGIN = '/begin-over.gif';
    const IMG_OVER_END = '/end-over.gif';
    
    /**
     * @var float
     */
    private $percent = 0.0;
    
    /**
     * @var string
     */
    private $imageDir = '';
    
    /**
     * @var boolean
     */
    private $showText = true;
    
    /**
     * Create the guage component.
     * The percent value is a float. Values of over 100.0% are valid.
     *
     * @param float $percent
     * @param string $imageDir
     */
    function __construct($percent, $imageDir = '/lib/Com/Ui/Gauge/images')
    {
        parent::__construct();
        $this->percent = $percent;
        $this->imageDir = $imageDir;
    }
    
    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = '
<div class="gaugeArea">
  <div class="pcnt" var="pcnt" choice="pcnt"></div>
  <table cellspacing="0" width="100%" border="0" style="">
    <tr style="margin: 0;padding: 0;">
      <td var="barBegin">&#160;</td>
      <td class="barFilled" var="barFilled">&#160;</td>
      <td class="barEmpty" var="barEmpty" choice="barEmpty">&#160;</td>
      <td var="barEnd">&#160;</td>
    </tr>
  </table>
</div>';
        $template = Com_Web_Template::load($xmlStr);
        return $template;
    }
    
    /**
     * Render the widget.
     *
     */
    function show()
    {
        $template = $this->getTemplate();
        $spacing = 'padding: 0;margin: 0;height: 10px;line-height: 10px;';
        
        if ($this->showText) {
            $template->insertText('pcnt', intval($this->percent) . '%');
            $template->setAttr('pcnt', 'style', 'text-align: center;font-size: 0.7em;padding: 0;margin: 0;');
            $template->setChoice('pcnt');
        }
        
        if ($this->percent > 100) {
            $url = new Tk_Type_Url($this->imageDir . self::IMG_OVER_BEGIN);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat;" . $spacing;
            $template->setAttr('barBegin', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_OVER);
            $style = "width: 100%;background: url({$url->toString()}) repeat-x;" . $spacing;
            $template->setAttr('barFilled', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_OVER_END);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat;" . $spacing;
            $template->setAttr('barEnd', 'style', $style);
        
        } else if ($this->percent == 100) {
            $url = new Tk_Type_Url($this->imageDir . self::IMG_FILLED_BEGIN);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top left;" . $spacing;
            $template->setAttr('barBegin', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_FILLED);
            $style = "width: 100%;background: url({$url->toString()}) repeat-x;" . $spacing;
            $template->setAttr('barFilled', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_FILLED_END);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top right;" . $spacing;
            $template->setAttr('barEnd', 'style', $style);
        
        } else if ($this->percent > 0 && $this->percent < 100) {
            $url = new Tk_Type_Url($this->imageDir . self::IMG_FILLED_BEGIN);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top left;" . $spacing;
            $template->setAttr('barBegin', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_FILLED);
            $style = "background: url({$url->toString()}) repeat-x;width: {$this->percent}%;" . $spacing;
            $template->setAttr('barFilled', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_EMPTY);
            $rem = (100 - $this->percent);
            $style = "background: url({$url->toString()}) repeat-x;width: {$rem}%;" . $spacing;
            $template->setAttr('barEmpty', 'style', $style);
            $template->setChoice('barEmpty');
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_EMPTY_END);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top right;" . $spacing;
            $template->setAttr('barEnd', 'style', $style);
        
        } else {
            $url = new Tk_Type_Url($this->imageDir . self::IMG_EMPTY_BEGIN);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top left;" . $spacing;
            $template->setAttr('barBegin', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_EMPTY);
            $style = "width: 100%;background: url({$url->toString()}) repeat-x;" . $spacing;
            $template->setAttr('barFilled', 'style', $style);
            
            $url = new Tk_Type_Url($this->imageDir . self::IMG_EMPTY_END);
            $style = "width: 5px;background: url({$url->toString()}) no-repeat top right;" . $spacing;
            $template->setAttr('barEnd', 'style', $style);
        }
    
    }
    
    /**
     * Set this to true to show the percent value text
     *
     * @param boolean $b
     */
    function enableText($b)
    {
        $this->showText = $b;
    }
}