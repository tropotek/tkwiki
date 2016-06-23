<?php
namespace App\Db;

use Tk\Db\Map\Model;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Content extends Model
{
    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $pageId = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $html = '';

    /**
     * @var string
     */
    public $keywords = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $css = '';

    /**
     * @var string
     */
    public $js = '';

    /**
     * Bytes 
     * @var integer
     */
    public $size = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Db\User
     */
    private $user = null;

    /**
     * @var \App\Db\Page
     */
    private $page = null;
    

    /**
     * User constructor.
     * 
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();
    }

    /**
     * @param \App\Db\Content $src
     * @return static
     */
    static function cloneContent($src)
    {
        $dst = new static();
        $dst->userId = \App\Factory::getConfig()->getUser()->id;
        if ($src) {
            $dst->pageId = $src->pageId;
            $dst->html = $src->html;
            $dst->keywords = $src->keywords;
            $dst->description = $src->description;
            $dst->css = $src->css;
            $dst->js = $src->js;
        }
        return $dst;
    }
    
    /**
     * 
     * @return Page|null
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = \App\Db\Page::getMapper()->find($this->pageId);
        }
        return $this->page;
    }

    /**
     * 
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->user) {
            $this->user = \App\Db\User::getMapper()->find($this->userId);
        }
        return $this->user;
    }
    
    
    public function save()
    {
        // TODO: calculate content size...
        $this->size = self::strByteSize($this->html.$this->js.$this->css);
        
        return parent::save();
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
     */
    static public function strByteSize($str)
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
        }
        return $d;
    }


}

class ContentValidator extends \App\Helper\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var Content $obj */
        $obj = $this->getObject();

        if (!$obj->pageId) {
            $this->addError('pageId', 'Invalid page ID value.');
        }
        if (!$obj->userId) {
            $this->addError('userId', 'Invalid user ID value.');
        }
        
        // TODO: ????
        
        
    }
}