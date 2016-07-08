<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Page
 *
 * @author vazgen
 */
class Page
{
    

    /**
     * Page id
     *
     * @var int|null
     */
    protected $id;
    
    /**
     * Page name
     *
     * @var string|null
     */    
    protected $name;
    
    /**
     * Page href(url)
     *
     * @var string|null
     */   
    
    protected $href; 
    
    /**
     * Page description
     *
     * @var string|null
     */    
    protected $title;
    
    /**
     * Page oredr for sorted 
     *
     * @var int|null
     */    
    protected $order;
    
    /**
     * Whether this page should be considered active
     *
     * @var bool 
     */     
    protected $active = false;
    
    /**
     * Whether this page should be considered visible
     *
     * @var bool
     */    
    protected $visible = true;

   /**
     * Parent page
     *
     * @var Page Object|null
     */
    protected $parent; 
    
    /**
     * Chiled pages if it  exists
     * 
     * @var  array|null
     */
    public $pages = null;
    
    /**
     * Custom HTML attributes
     *
     * @var array
     */    
    protected $customHtmlAttribs = [];
    
    /**
     * Constructor set a given properies 
     * 
     * @param array|object $properties 
     * @throws InvalidArgumentException
     */
    public function __construct($properties=[])
    {
        if (is_array($properties) || is_object($properties)) {
        
            foreach ($properties as $key => $value) {
                  $this->set($key, $value);
            }
            
        } else {
            throw new InvalidArgumentException(
                       "Argument should be an array or Object given".gettype($properties)
                      );            
        }
        
    }

    /** 
     * Normalizes a prorerty name 
     * (Uppercase the first character of each word in a string)
     * 
     * @param  string $property  property name to normalize
     * @return string   normalized property name
     */
    protected static function normalizePropertyName($name)
    {
        return ucwords($name);
    }
    
    /**
     * Sets the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * set method will be used. Otherwise, it will be set as a custom property.
     * 
     * @param  string $property   property name
     * @param  mixed  $value      value to set    
     * @return Page Object  
     * @throws InvalidArgumentException
     */    
    public function set($property,$value)
    {
         if (!is_string($property) || empty($property)) {
            throw new InvalidArgumentException(
                       "Argument {$property} should be an string given ".gettype($property)
                      );
         }
        $method = 'set' . self::normalizePropertyName($property);
        
        if (method_exists($this, $method)) {
             $this->$method($value);
        } else {
            $this->customHtmlAttribs[$property] = $value;
        }
        
        return $this;
    }
   
    /**
     * Sets a custom property
     *
     * @param  string $name   property name
     * @param  mixed  $value  value to set
     * @return void

     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Returns the value of the given property
     *
     * If the given property is native (id, class, title, etc), the matching
     * get method will be used. Otherwise, it will return the matching custom
     * property, or null if not found.
     *
     * @param  string $property   property name
     * @return mixed  the property's value or null
     * @throws InvalidArgumentException
     */    
    public function get($property)
    {
        if (!is_string($property) || empty($property)) {
            throw new InvalidArgumentException(
                       "Argument should be an array given ".gettype($property)
                      );
        } 
                        
        $method = 'get' . self::normalizePropertyName($property);
        
        
        if (method_exists($this,$method)) {
           
            return $this->$method();
        } elseif (isset($this->customHtmlAttribs[$property])) { 
           
            return $this->customHtmlAttribs[$property];
        } 
        
        return null;
    }
    
     public function __call($method, $arguments)
     {
        // echo $method; die;
         if (preg_match('/get([A-Z]+[A-Za-z]+)/', $method,$match)) {
            
             return $this->get(strtolower($match[1]));
         }
         throw new BadMethodCallException(
            sprintf(
                'Bad method call: Unknown method %s::%s',
                get_class($this),
                $method
            )
        );
     }
    /**
     * Returns a property, or null if it doesn't exist
     *
     * Magic overload for enabling <code>$page->propname</code>.
     *
     * @param  string $name               property name
     * @return mixed                      property value or null
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
       
        return $this->get($name);
    }
    
    /**
     * Returns a hash code value for the page
     *
     * @return string  a hash code value for this page
     */
    public function getHashCode()
    {
        return spl_object_hash($this);
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setHref($href)
    {
        $this->href = preg_replace('/\s+/', '', $href);
        return $this;
    }
    
    public function getHref()
    {
        return $this->href;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setChild($child)
    {
        $this->pages = $child;
        return $this;
    }
    
    public function hasChild()
    {
        if (is_array($this->pages) && !empty($this->pages)) {
            return true;
        }
        return false;
    }

    public function getChild(){
        return $this->pages;
    }
    
    public function setParent($parent){
        $this->parent = $parent;
        return $this;
    }
    public function getParent(){
        return $this->parent;
    }
    
    public function setOrder($order){
        $this->order = $order;
    }
    public function getOrder(){
        return $this->order;
    }

    public function setActive($active = true)
    {        
        $this->active = $active;               
        return $this;
    }
    
    public function isActive()
    {
        return $this->active;
    }
    
    public function setVisible($visible = true)
    {
        $this->visible = $visible;
        return $this;
    }
    
    public function isVisible(){
        
        return $this->visible;
    }
}
