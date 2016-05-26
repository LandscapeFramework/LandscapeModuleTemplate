<?php

    namespace Test;

    require_once('TemplateEngine.php');
    use Landscape\TemplateEngine;

    class TemplateEngineTest extends \PHPUnit_Framework_TestCase
    {
        public function testGetSet()
        {
            $temp = new TemplateEngine("file2", array("a" => "b2", "x" => "y2"));

            $temp->setFile("file");
            $temp->setContext(array("a" => "b", "x" => "y"));

            $file = $temp->getFile();
            $cont = $temp->getContext();

            $this->assertTrue($file == "file");
            $this->assertTrue($cont['a'] == "b");
            $this->assertTrue($cont['x'] == "y");
        }

        public function testConstructor()
        {
            $temp = new TemplateEngine("file2", array("a" => "b2", "x" => "y2"));

            $file = $temp->getFile();
            $cont = $temp->getContext();

            $this->assertTrue($file == "file2");
            $this->assertTrue($cont['a'] == "b2");
            $this->assertTrue($cont['x'] == "y2");
        }

        public function testRender()
        {
            $temp = new TemplateEngine("Test/test.html", array("valid" => true, 'var' => 'Hello World', 'var2' => '!!'));
            $expected = implode('', file("Test/expected.html"));

            $x = $temp->renderStart();
            $this->assertTrue($x == $expected);
        }
    }

?>
