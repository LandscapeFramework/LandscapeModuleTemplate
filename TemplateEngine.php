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
            if(!isset($args[0]))
            {
                $args[0] = "%c";
            }
            return strftime($args[0]);
        }

    }

    class TemplateEngine
    {
        private $TemplateFunctions = [

            ["time", TemplateFunctionType::SINGLE, "default" ,"timeFunc"],
            ["if", TemplateFunctionType::WRAPER, "default" ,NULL],

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
                                $res = TemplateEngineDefaultFunctions::$tfunc[3]($fc, $args);
                                $f[$x] = str_replace(substr($f[$x], $pos, $pos2-$pos+2), $res, $f[$x]);
                            }

                        }
                    }



                }
            }

            return implode('', $f);
        }
    }
?>
