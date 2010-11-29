<?php
/*
Plugin Name: Markdown Geshi
Plugin URI: http://github.com/drm/Markdown_Geshi
Description: <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> with GeSHi code highlighting added.
Version: 0.1
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

	function _doCodeBlocks_callback($matches) {
		if(preg_match($this->shebang, $matches[1], $m)) {
		    $language = $m[1];
		    $line = (int) (($m[2] > 1) ? $m[2] : 1);
		    $codeblock = $m[3];
		    $highlighter = new GeSHi($this->outdent(trim($codeblock)), $language);
		    $codeblock = $highlighter->parse_code();
		    $ret = '<ol';
		    if($line) {
		        $ret .= ' start="' . $line .'"';
		    }
		    $ret .= '>';
		    preg_match('!^(\s*<pre[^>]+>)(.*)(</pre>)!s', $codeblock, $m);
		    $ret .= preg_replace(
		        '/.+(\n|$)/', 
		        '<li>$0</li>', 
		        $m[2]
	        );
		    $ret .= '</ol>';
		    return "\n\n" . $this->hashBlock($m[1] . $ret . $m[3]) . "\n\n";
		} else {
		    return parent::_doCodeBlocks_callback($matches);
		}
	}
}
