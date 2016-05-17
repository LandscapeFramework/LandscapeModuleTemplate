<?php namespace Landscape\Template;

    class TemplateFunctionType
    {
        const SINGLE = "S";
        const WRAPER = "W";
    }

    class TemplateEngineDefaultFunctions
    {
        public static function ifFunc($key, $args, $space)
        {}

        public static function timeFunc($key, $args)
        {
            return "12:32:12 - 12.12.1992";
        }

        public function hey()
        {
            print "hey";
        }

    }

    class TemplateEngine
    {
        private $TemplateFunctions = [

            ["time", TemplateFunctionType::SINGLE, __NAMESPACE__."\TemplateEngineDefaultFunctions::timeFunc"],
            ["if", TemplateFunctionType::WRAPER, NULL],

        ];

        private $file;
        private $context;

        public function __construct($file, array $context)
        {
            $this->file = $file;
            $this->context = $context;
        }

        public function setContext(array $context)
        {
            $this->context = $context;
        }

        public function getContext()
        {
            return $this->context;
        }

        public function setFile($file)
        {
            $this->file = $file;
        }

        public function getFile()
        {
            return $this->file;
        }

        public function render()
        {
            $f = file($this->file);


            foreach($f as $line)
            {
                $pos = strpos($line, "{%");
                if($pos !== false)
                {
                    $pos2 = strpos($line, "%}", $pos);
                    $fc = substr($line, $pos+2, $pos2-$pos-2);
                    $temp = $fc;
                    $func = strtok($temp, ":");
                    $len = strlen($func)+1;
                    $func = trim($func);
                    $args = substr($fc, $len);

                }
            }
        }
    }
?>
