<?php

    namespace Test;

    require_once('TemplateEngine.php');
    use Landscape\TemplateEngine;

    class Dataset
    {
        public $value = "Variable in a nested class";

        public function method()
        {
            return "METHOD STRING";
        }
    }

    class TemplateEngineTest extends \PHPUnit_Framework_TestCase
    {
        public function testGetSet()
        {
            $obj = new Dataset();
            $temp = new TemplateEngine("file2", array("a" => "b2", "x" => "y2", "obj" => $obj));

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
            $obj = new Dataset();
            $temp = new TemplateEngine("Test/test.html", array("valid" => true, 'var' => 'Hello World', 'var2' => '!!', 'obj' => $obj, 'array' => [1,2,3,4,5]));
            $expected = implode('', file("Test/expected.html"));

            $x = $temp->renderStart();
            $this->assertTrue($x == $expected);
        }
    }

?>
