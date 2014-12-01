<?php
namespace SDS\ClassSupport;

class Klass
{
    protected $class;
    
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