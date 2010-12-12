<?php
/*
Plugin Name: Markdown Geshi
Plugin URI: http://github.com/drm/Markdown_Geshi
Description: <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> with GeSHi code highlighting added.
Version: 0.2
Author: Gerard van Helden
Author URI: http://melp.nl
*/
define('MARKDOWN_PARSER_CLASS', 'MarkdownGeshi_Parser');

require_once 'markdown.php';
require_once 'wp-syntax/geshi/geshi.php';

class MarkdownGeshi_Parser extends MarkdownExtra_Parser {
    /**
     * The 'processing instruction' pattern for the code blocks parser.
     * Format is defined as : #!language@linenumber. The @linenumber 
     * part is optional.
     */
    public $shebang = '/^\s*#!(\w+)(?:@(\d+))?\s*\n(.*)/s';
    
    function hasShebang($code) {
        if(preg_match($this->shebang, $code, $m)) {
            return $m;
        }
        return false;
    }

    function _doCodeBlocks_callback($matches) {
        if($m = $this->hasShebang($matches[1])) {
            return $this->_doGeshi($m);
        } else {
            return parent::_doCodeBlocks_callback($matches);
        }
    }
    
    function _doFencedCodeBlocks_callback($matches) {
        if($m = $this->hasShebang($matches[2])) {
            return $this->_doGeshi($m);
        } else {
            return parent::_doFencedCodeBlocks_callback($matches);
        }
    }
        
    function _doGeshi($shebangMatch) {
        $language = $shebangMatch[1];
        $line = (int) (($shebangMatch[2] > 1) ? $shebangMatch[2] : 0);
        $codeblock = $shebangMatch[3];
        
        $highlighter = new GeSHi($this->outdent(trim($codeblock)), $language);
        $highlighted = $highlighter->parse_code();
        if($line) {
            preg_match('!^(\s*<pre[^>]+>)(.*)(</pre>)!s', $highlighted, $m);

            $ret = '<ol';
            if($line) {
                $ret .= ' start="' . $line .'"';
            }
            $ret .= '>';
            $ret .= preg_replace(
                '/.+(\n|$)/', 
                '<li>$0</li>', 
                $m[2]
            );
            $ret .= '</ol>';
            
            $ret = $m[1] . $ret . $m[3];
        } else {
            $ret = $highlighted;
        }
        
        return "\n\n" . $this->hashBlock($ret) . "\n\n";
    }
}