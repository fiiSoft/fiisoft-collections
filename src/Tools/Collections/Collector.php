<?php

namespace FiiSoft\Tools\Collections;

use Countable;
use Traversable;

final class Collector implements Countable
{
    /** @var array */
    private $items = [];
    
    /**
     * @param Collector|array $initial
     */
    public function __construct($initial = null)
    {
        if (is_array($initial)) {
            $this->items = $initial;
        } elseif ($initial instanceof Collector) {
            $this->items = $initial->items;
        }
    }
    
    public function __invoke(...$args)
    {
        $this->add(...$args);
    }
    
    /**
     * Collect many values.
     *
     * @param iterable $values
     * @return void
     */
    public function addMany($values)
    {
        if (is_array($values) || $values instanceof Traversable) {
            foreach ($values as $value) {
                if ($value instanceof Collector) {
                    if (!empty($value->items)) {
                        array_splice($this->items, count($this->items), 0, $value->items);
                    }
                } else {
                    $this->items[] = $value;
                }
            }
        } else {
            $this->add($values);
        }
    }
    
    /**
     * @param Collector|mixed $value
     * @param array $more
     * @return void
     */
    public function add($value, ...$more)
    {
        if ($value instanceof Collector) {
            if (!empty($value->items)) {
                array_splice($this->items, count($this->items), 0, $value->items);
            }
        } else {
            $this->items[] = $value;
        }
    
        if (!empty($more)) {
            $this->addMany($more);
        }
    }
    
    /**
     * @return void
     */
    public function reset()
    {
        $this->items = [];
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }
    
    /**
     * @param string $glue
     * @return string
     */
    public function asString($glue = ' - ')
    {
        return implode($glue, $this->items);
    }
    
    public function __toString()
    {
        return $this->asString();
    }
    
    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
    
    /**
     * @return Collector copy with only unique values
     */
    public function unique()
    {
        //TODO make new class UniqueCollector instead of this
        return new static(array_unique($this->items, SORT_REGULAR));
    }
    
    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value)
    {
        return in_array($value, $this->items, true);
    }
    
    /**
     * @param Collector|iterable $values
     * @return bool
     */
    public function containsAny($values)
    {
        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($values)) {
            return true;
        }
    
        if ($values instanceof Collector) {
            $values = $values->items;
        }
    
        if (is_array($values) || $values instanceof Traversable) {
            foreach ($values as $value) {
                if ($this->contains($value)) {
                    return true;
                }
            }
            
            return false;
        }
    
        return $this->contains($values);
    }
    
    /**
     * @param Collector|iterable $values
     * @return bool
     */
    public function containsAll($values)
    {
        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($values)) {
            return false;
        }
        
        if ($values instanceof Collector) {
            $values = $values->items;
        }
        
        if (is_array($values) || $values instanceof Traversable) {
            foreach ($values as $value) {
                if (!$this->contains($value)) {
                    return false;
                }
            }
            
            return true;
        }
        
        return $this->contains($values);
    }
    
    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }
}