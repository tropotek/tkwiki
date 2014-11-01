<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * 
 * @package Dom_Test
 */
class Dom_TemplateTest extends UnitTestCase
{

    public function __construct()
    {
        parent::__construct('Dom Template Test');
    }

    public function setUp()
    {
        
    }

    public function tearDown()
    {
        
    }
    
    
    /**
     * testObject
     * 
     */
    public function testObject1()
    {
        // test the load file and string methods
        $tpl = Dom_Template::loadFile(dirname(__FILE__).'/data/test.html');
        
        
        $tplStr = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1 var="title"></h1>
  <div var="content">
    <p>Existing Content</p>
  </div>
</body>
</html>
HTML;
        $tpl = Dom_Template::load($tplStr);
        $tpl->insertText('title', 'Test Title');
        $tpl->setAttr('title', 'id', 'title');
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1 id="title">Test Title</h1>
  <div>
    <p>Existing Content</p>
  </div>
</body>
</html>
HTML;
        vd(trim($result1), trim($tpl->toString()));
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing VAR text and attributes');
    
        
        
        
        
        $tpl = Dom_Template::load($tplStr);
        
        
        $tplStr1 = <<<HTML
<ul>
  <li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul>
HTML;
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <div><ul>
  <li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul></div>
</body>
</html>
HTML;
        
        $tpl1 = Dom_Template::load($tplStr1);
        $tpl->insertTemplate('content', $tpl1);
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing insertTemplate()');
        
        $tpl = Dom_Template::load($tplStr);
        $tpl->insertHtml('content', $tplStr1);
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <div><ul><li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul></div>
</body>
</html>
HTML;
        
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing insertHtml()');
        
        
        $tpl = Dom_Template::load($tplStr);
        $tpl1 = Dom_Template::load($tplStr1);
        $tpl->replaceTemplate('content', $tpl1);
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <ul>
  <li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul>
</body>
</html>
HTML;
        
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing replaceTemplate()');
        
        $tpl = Dom_Template::load($tplStr);
        $tpl->replaceHtml('content', $tplStr1);
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <div><ul><li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul></div>
</body>
</html>
HTML;
        
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing replaceHtml()');
        
        
        
        
        
        
        
        $tpl = Dom_Template::load($tplStr);
        $tpl1 = Dom_Template::load($tplStr1);
        $tpl->appendTemplate('content', $tpl1);
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <div>
    <p>Existing Content</p>
  <ul>
  <li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul></div>
</body>
</html>
HTML;
        
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing replaceTemplate()');
        
        $tpl = Dom_Template::load($tplStr);
        $tpl->appendHtml('content', $tplStr1);
        
        $result1 = <<<HTML
<html>
<head>
  <title>Dom Site</title>
</head>
<body>
  <h1></h1>
  <div>
    <p>Existing Content</p>
  <ul><li>List Item 1</li>
  <li>List Item 2</li>
  <li>List Item 3</li>
</ul></div>
</body>
</html>
HTML;
        
        $this->assertEquals(trim($result1), trim($tpl->toString()), 'Testing replaceHtml()');
        
        
    }
    
    
}