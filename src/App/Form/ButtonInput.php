<?php
namespace App\Form;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ButtonInput extends \Tk\Form\Field\Input
{
    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var array
     */
    protected $btnAttr  = array();


    /**
     * ButtonInput constructor.
     *
     * @param string $name
     * @param string $icon
     */
    public function __construct($name, $icon = '')
    {
        parent::__construct($name);
        $this->icon = $icon;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setBtnAttr($key, $value)
    {
        $this->btnAttr[$key] = $value;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getBtnAttr($key)
    {
        if (isset($this->btnAttr[$key]))
            return $this->btnAttr[$key];
        return '';
    }
    
    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $template = parent::getHtml();
        
        if ($this->icon) {
            foreach ($this->btnAttr as $k => $v) {
                $template->setAttr('btn', $k, $v);
            }
            $template->addClass('icon', $this->icon);
            $template->setAttr('btn', 'id', $this->makeId('fid_btn_'));
            $template->setChoice('btn');
            if ($this->getAttr('disabled')) {
                $template->addClass('btn', 'disabled');
            }
        }
        
        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="input-group input-group-sm" var="group">
  <input type="text" var="element" />
  <div class="input-group-btn" choice="btn">
    <a href="#" class="btn btn-default" var="btn"><i var="icon"></i></a>
  </div>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }
    
}