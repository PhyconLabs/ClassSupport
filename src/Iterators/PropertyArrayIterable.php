<?php
namespace SDS\ClassSupport\Iterators;

use \SDS\ClassSupport\Exceptions as Exceptions;

trait PropertyArrayIterable
{
	public function rewind()
	{
		reset($this->getIterableArrayProperty());
	}
	
	public function current()
	{
		return current($this->getIterableArrayProperty());
	}
	
	public function key()
	{
		return key($this->getIterableArrayProperty());
	}
	
	public function next()
	{
		return next($this->getIterableArrayProperty());
	}
	
	public function valid()
	{
		return !is_null(key($this->getIterableArrayProperty()));
	}
	
	protected function &getIterableArrayProperty()
	{
		$property = isset($this->iterableArrayProperty) ? $this->iterableArrayProperty : "array";
		
		if (!isset($this->{$property}) || !is_array($this->{$property})) {
			$class = get_class($this);
			
			throw new Exceptions\BadPropertyException(
				"Property `{$class}::\${$property}` must be an array."
			);
		}
		
		return $this->{$property};
	}
}