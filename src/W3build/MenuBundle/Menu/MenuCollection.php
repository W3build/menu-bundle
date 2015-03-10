<?php
/**
 * Created by PhpStorm.
 * User: lukas_jahoda
 * Date: 13.1.15
 * Time: 21:43
 */

namespace W3build\MenuBundle\Menu;


use Traversable;

class MenuCollection implements \IteratorAggregate, \Countable {

    private $collection = array();

    private $items = array();

    private $children = array();

    private $root = array();

    public function add(MenuItem $menuItem, $parent = null){
        if(!$menuItem->getSymlink()){
            $this->items[$menuItem->getId()] = $menuItem;
        }

        if($parent){
            if(!array_key_exists($parent, $this->children)){
                $this->children[$parent] = array();
            }
            $this->children[$parent][] = $menuItem->getId();
            return;
        }

        $this->root[] = $menuItem->getId();
    }

    private function addChildren(MenuItem $menuItem, MenuItem $context = null){
        $childrens = $context ? $context->getChildren() : $this->collection;
        if($menuItem->getOrder()){
            if(array_key_exists($menuItem->getOrder(), $childrens)){
                array_splice($childrens, $menuItem->getOrder(), 0, array($menuItem));
                return;
            }
        }

        $childrens[] = $menuItem;
        if($context){
            return $context->setChildren($childrens);

        }

        $this->collection = $childrens;
    }

    public function build(){
        foreach($this->root as $key){
            $this->collection[] = $this->items[$key];
        }

        foreach($this->children as $parentKey => $children){
            foreach($children as $child){
                $parent = $this->items[$parentKey];
                $parent->addChildren($this->items[$child]);
            }
        }
    }

    public function exists($key){
        return array_key_exists($key, $this->keys);
    }

    public function getChildren(){
        return $this->collection;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->collection);
    }


}