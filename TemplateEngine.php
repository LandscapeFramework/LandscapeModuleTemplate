<?php namespace Landscape\Template;

    class TemplateFunctionType
    {
        const SINGLE = "S";
        const WRAPER = "W";
    }

    function ifFunc($context, $key, $args, $space)
    {}

    function TemplateDefaultTimeFunc($context, $key, $args)
    {
        if(!isset($args[0]))
        {
            $args[0] = "%c";
        }
        return strftime($args[0]);
    }
    
    function TemplateDefaultIncludeFunc($context, $key, $args)
    {
        return implode('', file($args[0]));
    }

    class TemplateEngine
    {
        private $TemplateFunctions = [

            ["if", TemplateFunctionType::WRAPER, "default" ,NULL],
            ["time",TemplateFunctionType::SINGLE, "default", "\\TemplateDefaultTimeFunc", false],
            ["include", TemplateFunctionType::SINGLE, "default", "\\TemplateDefaultIncludeFunc", true],
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
        
        public function renderStart()
        {
            $f = file($this->file);
            while($this->render($f) == false);
            return implode('', $f);
        }

        public function render(&$f)
        {


            for($x = 0; $x < sizeof($f); $x++)
            {
                $pos = strpos($f[$x], "{%");
                if($pos !== false)
                {
                    $pos2 = strpos($f[$x], "%}", $pos);
                    $fc = substr($f[$x], $pos+2, $pos2-$pos-2);
                    $temp = $fc;
                    $func = strtok($temp, ":");
                    $len = strlen($func)+1;
                    $func = trim($func);
                    $argsstr = substr($fc, $len);
                    $args = array_values(preg_grep('/^\s*\z/', explode('\'', $argsstr), PREG_GREP_INVERT));

                    // Match Function
                    foreach($this->TemplateFunctions as $tfunc)
                    {
                        if($tfunc[0] == $func)
                        {
                            if($tfunc[2] == "default")
                            {
                                if($tfunc[1] == TemplateFunctionType::SINGLE)
                                {
                                    $res = call_user_func(__NAMESPACE__.$tfunc[3],$this->context ,$fc, $args);
                                }
                                $f[$x] = str_replace(substr($f[$x], $pos, $pos2-$pos+2), $res, $f[$x]);
                            }
                            if($tfunc[4] == true)
                            {
                                return false;
                            }
                        }
                    }



                }
            }

            return true;
        }
    }
?>
