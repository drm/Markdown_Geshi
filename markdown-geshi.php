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
     * 
     * Optional parameters are allowed past a semicolon.
     */
    public $shebang = '/^
        \s*
        \#!(?P<lang>\w+)
        (?:@(?P<linenumber>\d+))?
        \s*
        (?:;\s*(?P<params>.*?)\s*)?\n
        (?P<code>.*)
    /sx';
    
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
        $language = $shebangMatch['lang'];
        $line = (int) (($shebangMatch['linenumber'] > 1) ? $shebangMatch['linenumber'] : 0);
        $codeblock = $shebangMatch['code'];
        
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
        if($shebangMatch['params']) {
            $ret = $this->_processGeshiParams($ret,  $shebangMatch['params']);
        }
        
        return "\n\n" . $this->hashBlock($ret) . "\n\n";
    }
    
    
    function _processGeshiParams($highlighted, $params) {
        foreach(explode(',', $params) as $keyValuePair) {
            @list($key, $value) = array_map('trim', explode('=', $keyValuePair));
            if($key && $value) {
                switch($key) {
                    case 'gist':
                        $highlighted = 
                            sprintf(
                                '<cite class="gist">(GIST: <a href="https://gist.github.com/%1$d" target="_blank">%1$d</a>)</cite>', 
                                $value
                            )
                            . $highlighted
                        ;
                        break;
                }
            }
        }
        return $highlighted;
    }
}
