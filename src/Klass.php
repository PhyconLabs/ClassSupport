<?php
namespace SDS\ClassSupport;

class Klass
{
    protected $class;
    
    public static function createFromPath($path)
    {
        if (!is_file($path)) {
            throw new Exceptions\InvalidClassFileException(
                "Missing file `{$path}` to create `\\SDS\\ClassSupport\\Klass` instance."
            );
        }
        
        $filePointer = fopen($path, "r");
        
        if ($filePointer === false) {
            throw new Exceptions\InaccessibleClassFileException(
                "Couldn't open file `{$path}` to create `\\SDS\\ClassSupport\\Klass` instance."
            );
        }
        
        $buffer = "";
        $class = false;
        $namespace = [];
        
        while ($class === false && !feof($filePointer)) {
            $buffer .= fread($filePointer, 512);
            $tokens = token_get_all($buffer);
            $tokenCount = count($tokens);
            
            if (strpos($buffer, "{") === false) {
                continue;
            }
            
            for ($i = 0; $i < $tokenCount; $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($u = $i + 1; $u < $tokenCount; $u++) {
                        if ($tokens[$u] === "{" || $tokens[$u] === ";") {
                            break;
                        } elseif ($tokens[$u][0] === T_STRING) {
                            $namespace[] = $tokens[$u][1];
                        }
                    }
                }
                
                if ($tokens[$i][0] === T_CLASS) {
                    for ($u = $i + 1; $u < $tokenCount; $u++) {
                        if ($tokens[$u] === "{") {
                            $class = $tokens[$i + 2][1];
                            break 2;
                        }
                    }
                }
            }
        }
        
        fclose($filePointer);
        
        if ($class === false) {
            throw new Exceptions\InvalidClassFileException(
                "File `{$path}` doesn't contain class declaration."
            );
        }
        
        $class = implode("\\", $namespace) . "\\{$class}";
        
        return new static($class);
    }
    
    public function __construct($class)
    {
        $this->setClass($class);
    }
    
    public function aliasTo($alias)
    {
        $alias = trim($alias, "\\");
        
        if (class_exists($alias)) {
            throw new Exceptions\AliasAlreadyExistsException(
                "Can't alias `{$this->getClass()}` to `{$alias}` as this alias is taken."
            );
        }
        
        if (!class_alias($this->getClass(), $alias)) {
            throw new Exceptions\UnexpectedAliasException(
                "Couldn't alias `{$this->getClass()}` to `{$alias}`."
            );
        }
        
        return $this;
    }
    
    public function aliasToIfFree($alias)
    {
        try {
            $this->aliasTo($alias);
            
            return true;
        } catch (Exceptions\AliasAlreadyExistsException $e) {
            return false;
        }
    }
    
    public function getNamespace($leadingSlash = false, $trailingSlash = false)
    {
        $namespace = explode("\\", $this->getClass());
        
        if (count($namespace) > 1) {
            array_pop($namespace);
        }
        
        $namespace = implode("\\", $namespace);
        
        if ($leadingSlash) {
            $namespace = "\\{$namespace}";
        }
        
        if ($trailingSlash) {
            $namespace = "{$namespace}\\";
        }
        
        return $namespace;
    }
    
    public function getName()
    {
        $class = $this->getClass();
        $name = explode("\\", $class);
        $name = array_pop($name);
        
        return $name;
    }
    
    public function getParentClass()
    {
        $parent = get_parent_class($this->getClass());
        
        return ($parent === false) ? null : $parent;
    }
    
    public function getParentClasses()
    {
        $parents = [];
        $current = $this->getClass();
        
        while (($parent = get_parent_class($current)) !== false) {
            $parents[] = $parent;
            $current = $parent;
        }
        
        return $parents;
    }
    
    public function getParentKlass()
    {
        $parent = $this->getParentClass();
        
        return isset($parent) ? new static($parent) : null;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function isA($class)
    {
        $class = trim($class, "\\");
        
        return is_a($this->getClass(), $class, true);
    }
    
    protected function setClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        $class = trim($class, "\\");
        
        $this->class = $class;
        
        return $this;
    }
}