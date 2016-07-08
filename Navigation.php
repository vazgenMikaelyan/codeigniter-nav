<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once __DIR__. '/navigation/Container.php';
require_once __DIR__. '/navigation/Page.php';

class Navigation 
{ 

    /**
     * CI_Controller instance
     * 
     * @var object
     */
    protected $CI;
    
    /**
     * Navigation Continer
     * 
     * @var array  
     */
    protected $container;
    
    /**
     * Name  and path of the partial file
     * 
     * @var string|null 
     */
    protected $partial = null;
    
    /**
     * active parent 
     */
    protected $activeWithParent = true;

    
    /**
     * Max level  for  nested 
     * 
     * @var null|int
     */
    protected $maxLevel = null;

    /**
     * UI class name  for parent  ul element
     * 
     * @var string
     */
    protected $ulClass = "nav navbar-nav";

    /**
     * UI class name  for child ul element
     * 
     * @var string
     */
    protected $childUlClass = "dropdown-menu";
    
    protected static $isFound=false;
    protected  $activeKey = null;

    
    /**
     * Constructor 
     */    
    public function __construct()
    {
        $this->CI = &get_instance();  
        $this->container = [];       
    }
    
    
    public function setOrder($container)
    {
        foreach ($container as $key => $value) {
            if (is_array($value)) {
                $container[$key] = (object) $value;
            }
        }
        
        usort($container, function($a,$b){

            if ($a->order === $b->order) {
                return 0;
            }
            
            return ($a->order < $b->order)? -1: 1;
        });
        return $container;
    }



    
    protected function addPages($pages, $parent = null)
    {
        $result = [];
       
        foreach ($pages as $item) {
            
            $page = new Page($item);
            if($page->parent === $parent) {
                
                $child = $this->addPages($pages,$page->id);
                
                if (count($child)) {
                   $page->setChild($child);                   
                }
                
                $uri  = $_SERVER['REQUEST_URI']; 
                if (strcmp($page->getHref(), $uri) === 0) {
                    $this->activeKey = $page->getParent();
                    $page->setActive(true); 
                                                                 
                }
   
                $hash = $page->getId();                
                $result[$hash] = $page;
                 
            }
        }
        return $result;        
    }

    protected function stieUrl($uri)
    {
        if (!function_exists('site_url')) {
            $this->load->helper('url');
        }
        return site_url($uri);
    }

    protected function getAllParents($pages, $lookup)
    {
        
        if (array_key_exists($lookup, $pages)) {
            return array($lookup);
        } else {
            foreach ($pages as $key => $page) {
                if ($page->hasChild()) {
                    $ret = $this->getAllParents($page->getChild(), $lookup);
                    if ($ret) {
                        $ret[] = $key;
                        return $ret;
                    }
                }
            }
        }
        return null;
    }

    protected function setParentToActive($pages, $parents)
    {
        
        foreach ($pages as $key => $page) {
            
            if(in_array($key, $parents)){
                 $page->setActive(true); 
            }
            if($page->hasChild()){               
                $this->setParentToActive($page->getChild(),$parents);                                
            }
        }
        return $pages;
    }

    protected function render($pages, $level = 1) {

        $uiClass = ($level === 1) ? $this->ulClass : $this->childUlClass;
        $menu = '<ul class="' . htmlspecialchars($uiClass) . '">' . PHP_EOL;
        foreach ($pages as $page) {
            if ($page->isVisible()) {

                $menu.='<li ' . ($page->isActive() ? 'class="active"' : "") . '>' . PHP_EOL;

                $menu.='<a href="' . $this->stieUrl(
                                               htmlspecialchars(
                                                       $page->getHref()
                                                )
                        ) . '" '
                        . 'title="' . (
                            $page->getTitle() ?
                                htmlspecialchars($page->getTitle()) :
                                htmlspecialchars($page->getName())
                        ) . '">' . PHP_EOL
                        . (
                            $page->hasChild() ?
                                '<span>' . htmlspecialchars($page->getName()) . '</span>' . PHP_EOL 
                                : htmlspecialchars($page->getName())
                        ) .
                        '</a>' . PHP_EOL;


                if ($page->hasChild()) {

                    if (is_null($this->maxLevel)) {
                        $menu.=$this->render($page->getChild(), ++$level);
                        --$level;
                    } elseif ($level < $this->maxLevel) {
                        $menu.=$this->render($page->getChild(), ++$level);
                        --$level;
                    }
                }
                $menu.='</li>' . PHP_EOL;
            }
        }
        $menu.='</ul>' . PHP_EOL;

        return $menu;
    }

    protected function renderPartial($partial, $data)
    {
        $config = $this->CI->config->item('templates');
        $view   = $config['template']."/html/".$partial;
        return $this->CI->load->view(
                        $view, $data, true
        );
    }

    public function setUIClass($className)
    {
        $this->ulClass = $className;
        return $this;
    }
    
    public function setChildUIClass($className)
    {
        $this->childUlClass = $className;
        return $this;
    }
     
    
    public function setMaxLevel($maxLevel = null)
    {
        $this->maxLevel = $maxLevel;
        return $this;
    }
     
    
    public function setActiveWithParent($flag = true)
    {
        $this->activeWithParent = $flag;
        return $this;
    }
     
    public function setPartial($partial)
    {
        $this->partial = $partial;
        return $this;
    }

    public function renderMenu($pages = null, $varName = null)
    {
       
        if (!is_array($pages) || is_null($pages)) {            
            throw new InvalidArgumentException(
                       "Argument should be an array given ".gettype($pages)
                      );
        }
        
        if (!is_string($varName) && !is_null($varName)) {
            throw new InvalidArgumentException(
                       "Argument should be an String given ".gettype($pages)
                      );            
        } 
     
         
        $container = $this->addPages(
                $this->setOrder($pages)
        );

        if ((true === $this->activeWithParent)) {

            if (!is_null($this->activeKey)) {

                $parents =  $this->getAllParents($container, $this->activeKey);
                $parents = array_reverse($parents); 
                
                $container = $this->setParentToActive($container, $parents);
                $this->activeKey = null;
            }
        }

        if (null !== $this->partial) {
            
            $data = [
                'ulClass' => $this->ulClass,
                'maxLevel' => $this->maxLevel,
                'childUlClass' => $this->childUlClass,
            ];
          
            $varname = !is_null($varName)? $varName : 'container';
            $data[$varname] = $container;
            return $this->renderPartial($this->partial,$data);
        }

        return $this->render($container);
    }
   
}
