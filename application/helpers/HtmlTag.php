<?php

class HtmlTag {
    const VOID_ELEMENT = true;
    private $tag;
    private $isVoid = true;
    private $attrs = array();
    private $children = array();

    /**
     * 
     * @param tag The HTML tag name.
     * @param attr An array containig the HTML element attributes.
     * @param isVoid Defines whether the element can have children or not.
     * @param children 
     */
    public function __construct($tag, $attrs, $isVoid=true, $children=array()) {
        $this->tag = $tag;
        $this->attrs = $attrs;
        $this->isVoid = $isVoid;
        if ( !$isVoid )
            $this->children = $children;
    }

    /**
     * Add an HTML element attribute. E.g. "name="value""
     */
    public function addAttr($attr, $value) {
        $this->attrs[$attr] = $value;
    }

    /**
     * Get the value of an HTML element attribute.
     */
    public function getAttr($attr) {
        return $this->attrs[$attr];
    }

    /**
     * Get the HTML string of the tag's attributes.
     */
    private function getAttrString() {
        $attrs = '';
        foreach ($this->attrs as $a => $v) {
            $attrs .= "$a=\"$v\" ";
        }
        return $attrs;
    }

    /**
     * This function is the one that creates the actual HTML string
     */
    public function getHTML() {
        $html = "<$tag ";
        $html .= $this->getAttrString();
        
        if ( $isVoid ) {
            $html .= '/>';
        } else {
            $html .= '>';
            foreach($this->children as $c) {
                $html .= $c->getHTML();
            }
            $html .= "</ $tag>";
        }

        return $html;
    }

    public function __toString() {
        return $html->getHTML();
    }
}
