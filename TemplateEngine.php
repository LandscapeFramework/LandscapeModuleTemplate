<?php namespace Landscape;

    class TemplateFunctionType
    {
        const SINGLE = "S";
        const WRAPER = "W";
    }

    function TemplateDefaultIfFunc($context, $key, $args, $parser ,$space)
    {
        if($context[$args[0]])
            return $space;
    }

    function TemplateDEfaultForFunc($context, $key,$args, $parser, $space)
    {
        $key = $args[0];
        $arr = $args[2];
        $ret = "";

        $context = $parser->getContext();
        $arr = $context[$arr];

        foreach($arr as $value)
        {
            $context[$key] = $value;
            $pa = new TemplateEngine("", $context);
            $pa->f = $space;
            $temp = $pa->renderStart();
            $ret = $ret.$temp;
        }
        return explode('\n', $ret);
    }

    function TemplateDefaultTimeFunc($context, $key, $args, $parser)
    {
        if(!isset($args[0]))
        {
            $args[0] = "%c";
        }
        return strftime($args[0]);
    }

    function TemplateDefaultIncludeFunc($context, $key, $args, $parser)
    {
        return implode('', file($args[0]));
    }

    class TemplateEngine
    {
        private $TemplateFunctions = [
            ["if", TemplateFunctionType::WRAPER, "default" ,"\\TemplateDefaultIfFunc", true],
            ["for", TemplateFunctionType::WRAPER, "default" ,"\\TemplateDefaultForFunc", true],
            ["time",TemplateFunctionType::SINGLE, "default", "\\TemplateDefaultTimeFunc", false],
            ["include", TemplateFunctionType::SINGLE, "default", "\\TemplateDefaultIncludeFunc", true],
        ];

        private $file;
        private $context;

        public $f;

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
            if($this->file != "")
                $this->f = file($this->file);
            while($this->render($this->f) == false);
            if($this->containsRenderable())
                return $this->renderStart();
            else
                return implode('', $this->f);
        }

        private function containsRenderable()
        {
            foreach ($this->f as $value)
            {
                if(strpos($value, "{%") !== false)
                    return true;
                else if(strpos($value, "{{") !== false)
                    return true;
            }
            return false;
        }

        public function render(&$f)
        {
            for($x = 0; $x < sizeof($f); $x++)
            {
                $pos = strpos($f[$x], "{%");
                $posV= strpos($f[$x], "{{");

                if($posV !== false)
                {
                    $posV2 = strpos($f[$x], "}}", $posV);
                    $fc = substr($f[$x], $posV+2, $posV2-$posV-2);
                    $fc = trim($fc);
                    $posObj = strpos($fc, "->");
                    if($posObj === false)
                        $rep = $this->context[$fc];
                    else
                    {
                        $tmp = strtok($fc, "->");
                        $tmp2= substr($fc, $posObj+2);
                        if(!is_callable(array($this->context[$tmp],$tmp2)))
                            $rep = $this->context[$tmp]->$tmp2;
                        else
                            $rep = $this->context[$tmp]->$tmp2();
                    }
                    $f[$x] = str_replace(substr($f[$x], $posV, $posV2-$posV+2), $rep, $f[$x]);
                }

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
                            $offset = 0;
                            if($tfunc[2] == "default")
                            {
                                $res="";
                                if($tfunc[1] == TemplateFunctionType::SINGLE)
                                {
                                    $res = call_user_func(__NAMESPACE__.$tfunc[3],$this->context ,$fc, $args, $this);
                                    $f[$x] = str_replace(substr($f[$x-$offset], $pos, $pos2-$pos+2), $res, $f[$x-$offset]);
                                }
                                elseif($tfunc[1] == TemplateFunctionType::WRAPER)
                                {
                                    $endtag = "{% end:".$func." %}";
                                    $inline = [];
                                    $depth = 0;
                                    while($depth >= 0 && $f[$x] != NULL)
                                    {

                                        if(strpos($f[$x], "{% $func") !== false && $offset != 0)
                                        {
                                            $depth++;
                                        }

                                        if(strpos($f[$x], $endtag) !== false)
                                        {
                                            $depth--;
                                        }

                                        if($offset != 0 && $depth >= 0)
                                            $inline[] = $f[$x];

                                        $f[$x] = "";
                                        $x++;
                                        $offset++;
                                    }
                                    $res = call_user_func(__NAMESPACE__.$tfunc[3],$this->context ,$fc, $args, $this, $inline);
                                    $t = array_slice($f, 0, $x-1);
                                    $t2= array_slice($f, $x-1);
                                    $f = array_merge($t, $res, $t2);
                                }

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
