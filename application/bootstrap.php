<?php
/**
 *
 * Copyright (c) 2009, xtube.fm
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of thePMP.com. located at
 * www.xtube.fm. If you received this code occasionally and without intent to use
 * it, please report this incident to the author by email: privacy@xtube.fm
 *
 * @author David Akimov <david@xtube.fm>
 * @copyright Copyright (c) xtube.fm, 2009
 * @version $Id$
 *
 */

define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT',    3);
define('HDOM_TYPE_ENDTAG',  4);
define('HDOM_TYPE_ROOT',    5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO',     3);
define('HDOM_INFO_BEGIN',   0);
define('HDOM_INFO_END',     1);
define('HDOM_INFO_QUOTE',   2);
define('HDOM_INFO_SPACE',   3);
define('HDOM_INFO_TEXT',    4);
define('HDOM_INFO_INNER',   5);
define('HDOM_INFO_OUTER',   6);
define('HDOM_INFO_ENDSPACE',7);

define("ENTITY_IMAGES_PATH", realpath(dirname(__FILE__))."/../public/images/entities/");
define("ALERT_IMAGES_PATH", realpath(dirname(__FILE__))."/../public/images/alerts/");


function dashString($string) {
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}


// helper functions
// -----------------------------------------------------------------------------
// get html dom form file
function file_get_html() {
    $dom = new simple_html_dom;
    $args = func_get_args();
    $dom->load(call_user_func_array('file_get_contents', $args), true);
   return $dom;
}

// get html dom form string
function str_get_html($str, $lowercase=true) {
    $dom = new simple_html_dom;
    $dom->load($str, $lowercase);
    return $dom;
}

// dump html dom tree
function dump_html_tree($node, $show_attr=true, $deep=0) {
    $lead = str_repeat('    ', $deep);
    echo $lead.$node->tag;
    if ($show_attr && count($node->attr)>0) {
        echo '(';
        foreach($node->attr as $k=>$v)
            echo "[$k]=>\"".$node->$k.'", ';
        echo ')';
    }
    echo "\n";

    foreach($node->nodes as $c)
        dump_html_tree($c, $show_attr, $deep+1);
}

// get dom form file (deprecated)
function file_get_dom() {
    $dom = new simple_html_dom;
    $args = func_get_args();
    $dom->load(call_user_func_array('file_get_contents', $args), true);
    return $dom;
}

// get dom form string (deprecated)
function str_get_dom($str, $lowercase=true) {
    $dom = new simple_html_dom;
    $dom->load($str, $lowercase);
    return $dom;
}

// simple html dom node
// -----------------------------------------------------------------------------
class simple_html_dom_node {
    public $nodetype = HDOM_TYPE_TEXT;
    public $tag = 'text';
    public $attr = array();
    public $children = array();
    public $nodes = array();
    public $parent = null;
    public $_ = array();
    private $dom = null;

    function __construct($dom) {
        $this->dom = $dom;
        $dom->nodes[] = $this;
    }

    function __destruct() {
        $this->clear();
    }

    function __toString() {
        return $this->outertext();
    }

    // clean up memory due to php5 circular references memory leak...
    function clear() {
        $this->dom = null;
        $this->nodes = null;
        $this->parent = null;
        $this->children = null;
    }
    
    // dump node's tree
    function dump($show_attr=true) {
        dump_html_tree($this, $show_attr);
    }

    // returns the parent of node
    function parent() {
        return $this->parent;
    }

    // returns children of node
    function children($idx=-1) {
        if ($idx===-1) return $this->children;
        if (isset($this->children[$idx])) return $this->children[$idx];
        return null;
    }

    // returns the first child of node
    function first_child() {
        if (count($this->children)>0) return $this->children[0];
        return null;
    }

    // returns the last child of node
    function last_child() {
        if (($count=count($this->children))>0) return $this->children[$count-1];
        return null;
    }

    // returns the next sibling of node    
    function next_sibling() {
        if ($this->parent===null) return null;
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx<$count && $this!==$this->parent->children[$idx])
            ++$idx;
        if (++$idx>=$count) return null;
        return $this->parent->children[$idx];
    }

    // returns the previous sibling of node
    function prev_sibling() {
        if ($this->parent===null) return null;
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx<$count && $this!==$this->parent->children[$idx])
            ++$idx;
        if (--$idx<0) return null;
        return $this->parent->children[$idx];
    }

    // get dom node's inner html
    function innertext() {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '';
        foreach($this->nodes as $n)
            $ret .= $n->outertext();
        return $ret;
    }

    // get dom node's outer text (with tag)
    function outertext() {
        if ($this->tag==='root') return $this->innertext();

        // trigger callback
        if ($this->dom->callback!==null)
            call_user_func_array($this->dom->callback, array($this));

        if (isset($this->_[HDOM_INFO_OUTER])) return $this->_[HDOM_INFO_OUTER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        // render begin tag
        $ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();

        // render inner text
        if (isset($this->_[HDOM_INFO_INNER]))
            $ret .= $this->_[HDOM_INFO_INNER];
        else {
            foreach($this->nodes as $n)
                $ret .= $n->outertext();
        }

        // render end tag
        if(isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END]!=0)
            $ret .= '</'.$this->tag.'>';
        return $ret;
    }

    // get dom node's plain text
    function text() {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        switch ($this->nodetype) {
            case HDOM_TYPE_TEXT: return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
            case HDOM_TYPE_COMMENT: return '';
            case HDOM_TYPE_UNKNOWN: return '';
        }
        if (strcasecmp($this->tag, 'script')===0) return '';
        if (strcasecmp($this->tag, 'style')===0) return '';

        $ret = '';
        foreach($this->nodes as $n)
            $ret .= $n->text();
        return $ret;
    }
    
    function xmltext() {
        $ret = $this->innertext();
        $ret = str_ireplace('<![CDATA[', '', $ret);
        $ret = str_replace(']]>', '', $ret);
        return $ret;
    }

    // build node's text with tag
    function makeup() {
        // text, comment, unknown
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '<'.$this->tag;
        $i = -1;

        foreach($this->attr as $key=>$val) {
            ++$i;

            // skip removed attribute
            if ($val===null || $val===false)
                continue;

            $ret .= $this->_[HDOM_INFO_SPACE][$i][0];
            //no value attr: nowrap, checked selected...
            if ($val===true)
                $ret .= $key;
            else {
                switch($this->_[HDOM_INFO_QUOTE][$i]) {
                    case HDOM_QUOTE_DOUBLE: $quote = '"'; break;
                    case HDOM_QUOTE_SINGLE: $quote = '\''; break;
                    default: $quote = '';
                }
                $ret .= $key.$this->_[HDOM_INFO_SPACE][$i][1].'='.$this->_[HDOM_INFO_SPACE][$i][2].$quote.$val.$quote;
            }
        }
        $ret = $this->dom->restore_noise($ret);
        return $ret . $this->_[HDOM_INFO_ENDSPACE] . '>';
    }

    // find elements by css selector
    function find($selector, $idx=null) {
        $selectors = $this->parse_selector($selector);
        if (($count=count($selectors))===0) return array();
        $found_keys = array();

        // find each selector
        for ($c=0; $c<$count; ++$c) {
            if (($levle=count($selectors[0]))===0) return array();
            if (!isset($this->_[HDOM_INFO_BEGIN])) return array();

            $head = array($this->_[HDOM_INFO_BEGIN]=>1);

            // handle descendant selectors, no recursive!
            for ($l=0; $l<$levle; ++$l) {
                $ret = array();
                foreach($head as $k=>$v) {
                    $n = ($k===-1) ? $this->dom->root : $this->dom->nodes[$k];
                    $n->seek($selectors[$c][$l], $ret);
                }
                $head = $ret;
            }

            foreach($head as $k=>$v) {
                if (!isset($found_keys[$k]))
                    $found_keys[$k] = 1;
            }
        }

        // sort keys
        ksort($found_keys);

        $found = array();
        foreach($found_keys as $k=>$v)
            $found[] = $this->dom->nodes[$k];

        // return nth-element or array
        if (is_null($idx)) return $found;
		else if ($idx<0) $idx = count($found) + $idx;
        return (isset($found[$idx])) ? $found[$idx] : null;
    }

    // seek for given conditions
    protected function seek($selector, &$ret) {
        list($tag, $key, $val, $exp, $no_key) = $selector;

        // xpath index
        if ($tag && $key && is_numeric($key)) {
            $count = 0;
            foreach ($this->children as $c) {
                if ($tag==='*' || $tag===$c->tag) {
                    if (++$count==$key) {
                        $ret[$c->_[HDOM_INFO_BEGIN]] = 1;
                        return;
                    }
                }
            } 
            return;
        }

        $end = (!empty($this->_[HDOM_INFO_END])) ? $this->_[HDOM_INFO_END] : 0;
        if ($end==0) {
            $parent = $this->parent;
            while (!isset($parent->_[HDOM_INFO_END]) && $parent!==null) {
                $end -= 1;
                $parent = $parent->parent;
            }
            $end += $parent->_[HDOM_INFO_END];
        }

        for($i=$this->_[HDOM_INFO_BEGIN]+1; $i<$end; ++$i) {
            $node = $this->dom->nodes[$i];
            $pass = true;

            if ($tag==='*' && !$key) {
                if (in_array($node, $this->children, true))
                    $ret[$i] = 1;
                continue;
            }

            // compare tag
            if ($tag && $tag!=$node->tag && $tag!=='*') {$pass=false;}
            // compare key
            if ($pass && $key) {
                if ($no_key) {
                    if (isset($node->attr[$key])) $pass=false;
                }
                else if (!isset($node->attr[$key])) $pass=false;
            }
            // compare value
            if ($pass && $key && $val  && $val!=='*') {
                $check = $this->match($exp, $val, $node->attr[$key]);
                // handle multiple class
                if (!$check && strcasecmp($key, 'class')===0) {
                    foreach(explode(' ',$node->attr[$key]) as $k) {
                        $check = $this->match($exp, $val, $k);
                        if ($check) break;
                    }
                }
                if (!$check) $pass = false;
            }
            if ($pass) $ret[$i] = 1;
            unset($node);
        }
    }

    protected function match($exp, $pattern, $value) {
        switch ($exp) {
            case '=':
                return ($value===$pattern);
            case '!=':
                return ($value!==$pattern);
            case '^=':
                return preg_match("/^".preg_quote($pattern,'/')."/", $value);
            case '$=':
                return preg_match("/".preg_quote($pattern,'/')."$/", $value);
            case '*=':
                if ($pattern[0]=='/')
                    return preg_match($pattern, $value);
                return preg_match("/".$pattern."/i", $value);
        }
        return false;
    }

    protected function parse_selector($selector_string) {
        // pattern of CSS selectors, modified from mootools
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string).' ', $matches, PREG_SET_ORDER);
        $selectors = array();
        $result = array();
        //print_r($matches);

        foreach ($matches as $m) {
            $m[0] = trim($m[0]);
            if ($m[0]==='' || $m[0]==='/' || $m[0]==='//') continue;
            // for borwser grnreated xpath
            if ($m[1]==='tbody') continue;

            list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
            if(!empty($m[2])) {$key='id'; $val=$m[2];}
            if(!empty($m[3])) {$key='class'; $val=$m[3];}
            if(!empty($m[4])) {$key=$m[4];}
            if(!empty($m[5])) {$exp=$m[5];}
            if(!empty($m[6])) {$val=$m[6];}

            // convert to lowercase
            if ($this->dom->lowercase) {$tag=strtolower($tag); $key=strtolower($key);}
            //elements that do NOT have the specified attribute
            if (isset($key[0]) && $key[0]==='!') {$key=substr($key, 1); $no_key=true;}

            $result[] = array($tag, $key, $val, $exp, $no_key);
            if (trim($m[7])===',') {
                $selectors[] = $result;
                $result = array();
            }
        }
        if (count($result)>0)
            $selectors[] = $result;
        return $selectors;
    }

    function __get($name) {
        if (isset($this->attr[$name])) return $this->attr[$name];
        switch($name) {
            case 'outertext': return $this->outertext();
            case 'innertext': return $this->innertext();
            case 'plaintext': return $this->text();
            case 'xmltext': return $this->xmltext();
            default: return array_key_exists($name, $this->attr);
        }
    }

    function __set($name, $value) {
        switch($name) {
            case 'outertext': return $this->_[HDOM_INFO_OUTER] = $value;
            case 'innertext':
                if (isset($this->_[HDOM_INFO_TEXT])) return $this->_[HDOM_INFO_TEXT] = $value;
                return $this->_[HDOM_INFO_INNER] = $value;
        }
        if (!isset($this->attr[$name])) {
            $this->_[HDOM_INFO_SPACE][] = array(' ', '', ''); 
            $this->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
        }
        $this->attr[$name] = $value;
    }

    function __isset($name) {
        switch($name) {
            case 'outertext': return true;
            case 'innertext': return true;
            case 'plaintext': return true;
        }
        //no value attr: nowrap, checked selected...
        return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
    }

    function __unset($name) {
        if (isset($this->attr[$name]))
            unset($this->attr[$name]);
    }

    // camel naming conventions
    function getAllAttributes() {return $this->attr;}
    function getAttribute($name) {return $this->__get($name);}
    function setAttribute($name, $value) {$this->__set($name, $value);}
    function hasAttribute($name) {return $this->__isset($name);}
    function removeAttribute($name) {$this->__set($name, null);}
    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=null) {return $this->find($name, $idx);}
    function parentNode() {return $this->parent();}
    function childNodes($idx=-1) {return $this->children($idx);}
    function firstChild() {return $this->first_child();}
    function lastChild() {return $this->last_child();}
    function nextSibling() {return $this->next_sibling();}
    function previousSibling() {return $this->prev_sibling();}
}

// simple html dom parser
// -----------------------------------------------------------------------------
class simple_html_dom {
    public $root = null;
    public $nodes = array();
    public $callback = null;
    public $lowercase = false;
    protected $pos;
    protected $doc;
    protected $char;
    protected $size;
    protected $cursor;
    protected $parent;
    protected $noise = array();
    protected $token_blank = " \t\r\n";
    protected $token_equal = ' =/>';
    protected $token_slash = " />\r\n\t";
    protected $token_attr = ' >';
    // use isset instead of in_array, performance boost about 30%...
    protected $self_closing_tags = array('img'=>1, 'br'=>1, 'input'=>1, 'meta'=>1, 'link'=>1, 'hr'=>1, 'base'=>1, 'embed'=>1, 'spacer'=>1);
    protected $block_tags = array('root'=>1, 'body'=>1, 'form'=>1, 'div'=>1, 'span'=>1, 'table'=>1);
    protected $optional_closing_tags = array(
        'tr'=>array('tr'=>1, 'td'=>1, 'th'=>1),
        'th'=>array('th'=>1),
        'td'=>array('td'=>1),
        'li'=>array('li'=>1),
        'dt'=>array('dt'=>1, 'dd'=>1),
        'dd'=>array('dd'=>1, 'dt'=>1),
        'dl'=>array('dd'=>1, 'dt'=>1),
        'p'=>array('p'=>1),
        'nobr'=>array('nobr'=>1),
    );

    function __construct($str=null) {
        if ($str) {
            if (preg_match("/^http:\/\//i",$str) || is_file($str)) 
                $this->load_file($str); 
            else
                $this->load($str);
        }
    }

    function __destruct() {
        $this->clear();
    }

    // load html from string
    function load($str, $lowercase=true) {
        // prepare
        $this->prepare($str, $lowercase);
        // strip out comments
        $this->remove_noise("'<!--(.*?)-->'is");
        // strip out cdata
        $this->remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);
        // strip out <style> tags
        $this->remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
        $this->remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
        // strip out <script> tags
        $this->remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
        $this->remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
        // strip out preformatted tags
        $this->remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
        // strip out server side scripts
        $this->remove_noise("'(<\?)(.*?)(\?>)'s", true);
        // strip smarty scripts
        $this->remove_noise("'(\{\w)(.*?)(\})'s", true);

        // parsing
        while ($this->parse());
        // end
        $this->root->_[HDOM_INFO_END] = $this->cursor;
    }

    // load html from file
    function load_file() {
        $args = func_get_args();
        $this->load(call_user_func_array('file_get_contents', $args), true);
    }

    // set callback function
    function set_callback($function_name) {
        $this->callback = $function_name;
    }

    // remove callback function
    function remove_callback() {
        $this->callback = null;
    }

    // save dom as string
    function save($filepath='') {
        $ret = $this->root->innertext();
        if ($filepath!=='') file_put_contents($filepath, $ret);
        return $ret;
    }

    // find dom node by css selector
    function find($selector, $idx=null) {
        return $this->root->find($selector, $idx);
    }

    // clean up memory due to php5 circular references memory leak...
    function clear() {
        foreach($this->nodes as $n) {$n->clear(); $n = null;}
        if (isset($this->parent)) {$this->parent->clear(); unset($this->parent);}
        if (isset($this->root)) {$this->root->clear(); unset($this->root);}
        unset($this->doc);
        unset($this->noise);
    }
    
    function dump($show_attr=true) {
        $this->root->dump($show_attr);
    }

    // prepare HTML data and init everything
    protected function prepare($str, $lowercase=true) {
        $this->clear();
        $this->doc = $str;
        $this->pos = 0;
        $this->cursor = 1;
        $this->noise = array();
        $this->nodes = array();
        $this->lowercase = $lowercase;
        $this->root = new simple_html_dom_node($this);
        $this->root->tag = 'root';
        $this->root->_[HDOM_INFO_BEGIN] = -1;
        $this->root->nodetype = HDOM_TYPE_ROOT;
        $this->parent = $this->root;
        // set the length of content
        $this->size = strlen($str);
        if ($this->size>0) $this->char = $this->doc[0];
    }

    // parse html content
    protected function parse() {
        if (($s = $this->copy_until_char('<'))==='')
            return $this->read_tag();

        // text
        $node = new simple_html_dom_node($this);
        ++$this->cursor;
        $node->_[HDOM_INFO_TEXT] = $s;
        $this->link_nodes($node, false);
        return true;
    }

    // read tag info
    protected function read_tag() {
        if ($this->char!=='<') {
            $this->root->_[HDOM_INFO_END] = $this->cursor;
            return false;
        }
        $begin_tag_pos = $this->pos;
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next

        // end tag
        if ($this->char==='/') {
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            $this->skip($this->token_blank_t);
            $tag = $this->copy_until_char('>');

            // skip attributes in end tag
            if (($pos = strpos($tag, ' '))!==false)
                $tag = substr($tag, 0, $pos);

            $parent_lower = strtolower($this->parent->tag);
            $tag_lower = strtolower($tag);

            if ($parent_lower!==$tag_lower) {
                if (isset($this->optional_closing_tags[$parent_lower]) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;

                    while (($this->parent->parent) && strtolower($this->parent->tag)!==$tag_lower)
                        $this->parent = $this->parent->parent;

                    if (strtolower($this->parent->tag)!==$tag_lower) {
                        $this->parent = $org_parent; // restore origonal parent
                        if ($this->parent->parent) $this->parent = $this->parent->parent;
                        $this->parent->_[HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->parent->parent) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;

                    while (($this->parent->parent) && strtolower($this->parent->tag)!==$tag_lower)
                        $this->parent = $this->parent->parent;

                    if (strtolower($this->parent->tag)!==$tag_lower) {
                        $this->parent = $org_parent; // restore origonal parent
                        $this->parent->_[HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->parent->parent) && strtolower($this->parent->parent->tag)===$tag_lower) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $this->parent = $this->parent->parent;
                }
                else
                    return $this->as_text_node($tag);
            }

            $this->parent->_[HDOM_INFO_END] = $this->cursor;
            if ($this->parent->parent) $this->parent = $this->parent->parent;

            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        $node = new simple_html_dom_node($this);
        $node->_[HDOM_INFO_BEGIN] = $this->cursor;
        ++$this->cursor;
        $tag = $this->copy_until($this->token_slash);

        // doctype, cdata & comments...
        if (isset($tag[0]) && $tag[0]==='!') {
            $node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until_char('>');

            if (isset($tag[2]) && $tag[1]==='-' && $tag[2]==='-') {
                $node->nodetype = HDOM_TYPE_COMMENT;
                $node->tag = 'comment';
            } else {
                $node->nodetype = HDOM_TYPE_UNKNOWN;
                $node->tag = 'unknown';
            }

            if ($this->char==='>') $node->_[HDOM_INFO_TEXT].='>';
            $this->link_nodes($node, true);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // text
        if ($pos=strpos($tag, '<')!==false) {
            $tag = '<' . substr($tag, 0, -1);
            $node->_[HDOM_INFO_TEXT] = $tag;
            $this->link_nodes($node, false);
            $this->char = $this->doc[--$this->pos]; // prev
            return true;
        }

        if (!preg_match("/^[\w-:]+$/", $tag)) {
            $node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until('<>');
            if ($this->char==='<') {
                $this->link_nodes($node, false);
                return true;
            }

            if ($this->char==='>') $node->_[HDOM_INFO_TEXT].='>';
            $this->link_nodes($node, false);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // begin tag
        $node->nodetype = HDOM_TYPE_ELEMENT;
        $tag_lower = strtolower($tag);
        $node->tag = ($this->lowercase) ? $tag_lower : $tag;

        // handle optional closing tags
        if (isset($this->optional_closing_tags[$tag_lower]) ) {
            while (isset($this->optional_closing_tags[$tag_lower][strtolower($this->parent->tag)])) {
                $this->parent->_[HDOM_INFO_END] = 0;
                $this->parent = $this->parent->parent;
            }
            $node->parent = $this->parent;
        }

        $guard = 0; // prevent infinity loop
        $space = array($this->copy_skip($this->token_blank), '', '');

        // attributes
        do {
            if ($this->char!==null && $space[0]==='') break;
            $name = $this->copy_until($this->token_equal);
            if($guard===$this->pos) {
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                continue;
            }
            $guard = $this->pos;

            // handle endless '<'
            if($this->pos>=$this->size-1 && $this->char!=='>') {
                $node->nodetype = HDOM_TYPE_TEXT;
                $node->_[HDOM_INFO_END] = 0;
                $node->_[HDOM_INFO_TEXT] = '<'.$tag . $space[0] . $name;
                $node->tag = 'text';
                $this->link_nodes($node, false);
                return true;
            }

            // handle mismatch '<'
            if($this->doc[$this->pos-1]=='<') {
                $node->nodetype = HDOM_TYPE_TEXT;
                $node->tag = 'text';
                $node->attr = array();
                $node->_[HDOM_INFO_END] = 0;
                $node->_[HDOM_INFO_TEXT] = substr($this->doc, $begin_tag_pos, $this->pos-$begin_tag_pos-1);
                $this->pos -= 2;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $this->link_nodes($node, false);
                return true;
            }

            if ($name!=='/' && $name!=='') {
                $space[1] = $this->copy_skip($this->token_blank);
                $name = $this->restore_noise($name);
                if ($this->lowercase) $name = strtolower($name);
                if ($this->char==='=') {
                    $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                    $this->parse_attr($node, $name, $space);
                }
                else {
                    //no value attr: nowrap, checked selected...
                    $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
                    $node->attr[$name] = true;
                    if ($this->char!='>') $this->char = $this->doc[--$this->pos]; // prev
                }
                $node->_[HDOM_INFO_SPACE][] = $space;
                $space = array($this->copy_skip($this->token_blank), '', '');
            }
            else
                break;
        } while($this->char!=='>' && $this->char!=='/');

        $this->link_nodes($node, true);
        $node->_[HDOM_INFO_ENDSPACE] = $space[0];

        // check self closing
        if ($this->copy_until_char_escape('>')==='/') {
            $node->_[HDOM_INFO_ENDSPACE] .= '/';
            $node->_[HDOM_INFO_END] = 0;
        }
        else {
            // reset parent
            if (!isset($this->self_closing_tags[strtolower($node->tag)])) $this->parent = $node;
        }
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return true;
    }

    // parse attributes
    protected function parse_attr($node, $name, &$space) {
        $space[2] = $this->copy_skip($this->token_blank);
        switch($this->char) {
            case '"':
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('"'));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            case '\'':
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_SINGLE;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('\''));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            default:
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
                $node->attr[$name] = $this->restore_noise($this->copy_until($this->token_attr));
        }
    }

    // link node's parent
    protected function link_nodes(&$node, $is_child) {
        $node->parent = $this->parent;
        $this->parent->nodes[] = $node;
        if ($is_child)
            $this->parent->children[] = $node;
    }

    // as a text node
    protected function as_text_node($tag) {
        $node = new simple_html_dom_node($this);
        ++$this->cursor;
        $node->_[HDOM_INFO_TEXT] = '</' . $tag . '>';
        $this->link_nodes($node, false);
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return true;
    }

    protected function skip($chars) {
        $this->pos += strspn($this->doc, $chars, $this->pos);
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
    }

    protected function copy_skip($chars) {
        $pos = $this->pos;
        $len = strspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        if ($len===0) return '';
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until($chars) {
        $pos = $this->pos;
        $len = strcspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until_char($char) {
        if ($this->char===null) return '';

        if (($pos = strpos($this->doc, $char, $this->pos))===false) {
            $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
            $this->char = null;
            $this->pos = $this->size;
            return $ret;
        }

        if ($pos===$this->pos) return '';
        $pos_old = $this->pos;
        $this->char = $this->doc[$pos];
        $this->pos = $pos;
        return substr($this->doc, $pos_old, $pos-$pos_old);
    }

    protected function copy_until_char_escape($char) {
        if ($this->char===null) return '';

        $start = $this->pos;
        while(1) {
            if (($pos = strpos($this->doc, $char, $start))===false) {
                $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
                $this->char = null;
                $this->pos = $this->size;
                return $ret;
            }

            if ($pos===$this->pos) return '';

            if ($this->doc[$pos-1]==='\\') {
                $start = $pos+1;
                continue;
            }

            $pos_old = $this->pos;
            $this->char = $this->doc[$pos];
            $this->pos = $pos;
            return substr($this->doc, $pos_old, $pos-$pos_old);
        }
    }

    // remove noise from html content
    protected function remove_noise($pattern, $remove_tag=false) {
        $count = preg_match_all($pattern, $this->doc, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);

        for ($i=$count-1; $i>-1; --$i) {
            $key = '___noise___'.sprintf('% 3d', count($this->noise)+100);
            $idx = ($remove_tag) ? 0 : 1;
            $this->noise[$key] = $matches[$i][$idx][0];
            $this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
        }

        // reset the length of content
        $this->size = strlen($this->doc);
        if ($this->size>0) $this->char = $this->doc[0];
    }

    // restore noise to html content
    function restore_noise($text) {
        while(($pos=strpos($text, '___noise___'))!==false) {
            $key = '___noise___'.$text[$pos+11].$text[$pos+12].$text[$pos+13];
            if (isset($this->noise[$key]))
                $text = substr($text, 0, $pos).$this->noise[$key].substr($text, $pos+14);
        }
        return $text;
    }

    function __toString() {
        return $this->root->innertext();
    }

    function __get($name) {
        switch($name) {
            case 'outertext': return $this->root->innertext();
            case 'innertext': return $this->root->innertext();
            case 'plaintext': return $this->root->text();
        }
    }

    // camel naming conventions
    function childNodes($idx=-1) {return $this->root->childNodes($idx);}
    function firstChild() {return $this->root->first_child();}
    function lastChild() {return $this->root->last_child();}
    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=-1) {return $this->find($name, $idx);}
    function loadFile() {$args = func_get_args();$this->load(call_user_func_array('file_get_contents', $args), true);}
} 

function api($level) {
	$apis2 = array(
	  4 => '1.6 Donut',
	  7 => '2.1 Eclair',
	  8 => '2.2 Froyo',
	  9 => '2.3 - 2.3.2 Gingerbread',
	  10 => '2.3.3 - 2.3.7 Gingerbread',
	  12 => '3.1 Honeycomb',
	  13 => '3.2 Honeycomb',
	  15 => '4.0.3 - 4.04 Ice Creaem Sandwich',
	  16 => '4.1 Jelly Bean',
	  17 => '4.2 Jelly Bean'
	);
	$apis = array(
	  4 => '1.6',
	  7 => '2.1',
	  8 => '2.2',
	  9 => '2.3.2+',
	  10 => '2.3.7-',
	  12 => '3.1',
	  13 => '3.2',
	  15 => '4.0.4-',
	  16 => '4.1',
	  17 => '4.2'
	);

	return $apis[$level];
}

function translit($text, $type='de'){
    if (!preg_match("[^А-Яа-яёЁ]", $text)) {
        echo 'translit: ';
        $data = explode(" ", $text);
        if(count($data) == ''){
    	    return '';
        }
        $alphas=array(
            'yii'=>'ы',
            'ji'=>'й',
            'ic'=>'ик',
            'ci'=>'си',
            'yo'=>'ё',
            'ya'=>'я',
            'shc'=>'щ',
            'sh'=>'ш',
            'ea'=>'я',
            'ohn'=>'он',
            'oo'=>'у',
            'ace'=>'эйс',
            'ack'=>'эк',
            'ugh'=>'ью',
            'oo'=>'у',
            'ii'=>'й',
            'zh'=>'ж',
            'ch'=>'ч',
            'iy'=>'ю',
            'ts'=>'ц',
            'u'=>'у',
            'w'=>'в',
            'x'=>'кс',
            'v'=>'в',
            'i'=>'и',
            'y'=>'у',
            'd'=>'д',
            't'=>'т',
            'T'=>'Т',
            'b'=>'б',
            'p'=>'п',
            'n'=>'н',
            'f'=>'ф',
            ''=>'ь',
            ''=>'ъ',
            'z'=>'з',
            'l'=>'л',
            'k'=>'к',
            's'=>'с',
            'm'=>'м',
            'r'=>'р',
            's'=>'с',
            'h'=>'х',
            'j'=>'ж',
            'g'=>'г',
            '_'=>'',
            'A'=>'А',
            'B'=>'Б',
            'C'=>'Ц',
            'D'=>'Д',
            'E'=>'Е',
            'F'=>'Ф',
            'G'=>'Г',
            'H'=>'Х',
            'I'=>'И',
            'J'=>'Дж',
            'K'=>'К',
            'L'=>'Л',
            'M'=>'М',
            'N'=>'Н',
            'O'=>'О',
            'P'=>'П',
            'Qu'=>'Ку',
            'R'=>'Р',
            'S'=>'С',
            'T'=>'Т',
            'U'=>'У',
            'V'=>'В',
            'W'=>'В',
            'X'=>'Кс',
            'Y'=>'У',
            'Z'=>'З',
            'a'=>'а');
        $total = '';
    
        foreach($data as $k=>$v){
        	if(preg_match("/^[a-zA-Z]*/",$v)){
        		foreach($alphas as $id=>$value){
        			if($type=='de'){
        				if(strcasecmp($v,$id) AND !eregi("->",$v)){
        					$v=str_replace($id,$value,$v);
        				}elseif(eregi("->",$v)){
        					$v=str_replace("->","",$v);
        				}
        			}elseif($type='translit'){
        				if(strcasecmp($v,$value) AND !eregi("->",$v)){
        					$v=str_replace($value,$id,$v);
        				}elseif(eregi("->",$v)){
        					$v=str_replace("->","",$v);
        				}
        			}
        		}
        	}
        	$total.=$v." ";
        }
        return $total;
    } else {
        return $text;
    }
}




function duration($fname) {
    $str = "mplayer -msglevel identify=4 '".$fname."' -frames 0 -ao null -vo null -quiet 2>/dev/null | grep 'ID_LENGTH='";
    $sult = exec($str);
    $res = substr($sult, 10);
    return $res;
}

function checkZombie($txt, $doCreate = true) {
    if (fopen(FLAGS_PATH.$txt, 'r')) {
        clog ($txt.' already started');
        clog ('Kill '.$txt.' in '.FLAGS_PATH);
        clog ('Finished with errors');
        exit;
    } else {
        if ($doCreate) {
//		echo "createing ".FLAGS_PATH.$txt;exit;
            $f = fopen(FLAGS_PATH.$txt, 'a+');
            fclose($f);
            return false;
        }
    }
    
}

function killZombie ($txt) {
    unlink(FLAGS_PATH.$txt);
}


function fsize($filesize){
   
    if(is_numeric($filesize)){
        $decr = 1024; $step = 0;
        $prefix = array('Байт','Кб','Мб','Гб','Тб','Пб');
       
        while(($filesize / $decr) > 0.9){
            $filesize = $filesize / $decr;
            $step++;
        }
        return round($filesize,2).' '.$prefix[$step];
    } else {
        return 'NaN';
    }
}

function wordLimiter($text, $limit=20){
    $explode = explode(' ',$text);
    $string  = '';

    $dots = '...';
    if(count($explode) <= $limit){
        $dots = '';
    }
    for($i=0;$i<$limit;$i++){
        $string .= $explode[$i]." ";
    }
    if ($dots) {
        $string = substr($string, 0, strlen($string));
    }

    return $string.$dots;
}


function clog($class, $action, $isCaption = false) {
    $f = fopen(APPLICATION_PATH."/../../xtube-cron.log", "a+");
    $str = $class."\n";
    fwrite($f, $str);
    fclose($f);
    echo $isCaption ? "---> ".$str : "  -> ".$str;
    
}


function curl_check_redirect($ch) {
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        list($header) = explode("\r\n\r\n", $data, 2);
        $matches = array();
        preg_match('/(Location:|URI:) (.*)/', $header, $matches);
        $url = trim(array_pop($matches));
        return $url;
    } else {
        return false;
    }
}


function deleteSymbols($string) {
		$filter = new Zend_Filter_StripTags();
		$res = $filter->filter($string);
		$parsed = str_replace('&nbsp;', ' ', $res);
		return $parsed;
}



/**
 * Bootstraper
 *
 * @package application
 */
class Bootstrap extends FaZend_Application_Bootstrap_Bootstrap { 

    /**
     * Initialize it.
     *
     * This method has to be the first in bootstrap!
     *
     * @return void
     */
    protected function _initAll()
    {
        $this->bootstrap('fz_logger');
        $this->bootstrap('fz_starter');

//	if (!defined('WEBSITE_URL')) {
//	    define(
//	        'WEBSITE_URL',
//	        'http://' . preg_replace(
//	            '/^www\./i',
//	            '',
//	            isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'
//	        )
//	    );
//	}

	if (APPLICATION_ENV != "production") {
		define('UPLOAD_PATH', "z:\\code\\xtube\\upload\\");
		define('FLAGS_PATH', "z:\\code\\xtube\\flags\\");
		define('MOVIES_PATH', "z:\\code\\xtube\\movies\\");

	} else {
		define('UPLOAD_PATH', '/upload/');
		define('MOVIES_PATH', '/movies/');
		define('FLAGS_PATH', '/flags/');
	}
    }

    /**
     * Initialize translator
     *
     * @return void
     **/
    protected function _initTranslator() {
        $suffixes = array(
            true => '<span style="color:#268af0">*</span>', 
            false => '');
        FaZend_View_Helper_Forma::setLabelSuffixes($suffixes);        

		$this->_bootstrap('fz_translate');
		$lang = "ru";
        Zend_Registry::get('Zend_Translate')->setLocale($lang);
    }
}

