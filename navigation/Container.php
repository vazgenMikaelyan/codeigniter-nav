<?php
class RecursiveListIterator extends RecursiveIteratorIterator { 
     
    public $tab = "\t"; 
     
    public function beginChildren() { 
        if (count($this->getInnerIterator()) == 0) { return; } 
        echo str_repeat($this->tab, $this->getDepth()), "<ul>\n"; 
    } 
     
    public function endChildren() { 
        if (count($this->getInnerIterator()) == 0) { return; } 
        echo str_repeat($this->tab, $this->getDepth()), "</ul>\n"; 
        echo str_repeat($this->tab, $this->getDepth()), "</li>\n"; 
    } 
     
    public function nextElement() { 
        // Display leaf node  
        if ( ! $this->callHasChildren()) { 
            echo str_repeat($this->tab, $this->getDepth()+1), '<li>', $this->current(), "</li>\n"; 
            return; 
        } 
         
        // Display branch with label 
        echo str_repeat($this->tab, $this->getDepth()+1), '<li>', $this->key(); 
        echo (count($this->callGetChildren()) == 0) ? "</li>\n" : "\n"; 
    } 
} 
 