# Markdown_Geshi #
A simple extension to the [PHP Markdown](http://michelf.com/projects/php-markdown/) implementation 
to add code highlighting to the wordpress plugin using [GeSHi](http://qbnz.com/highlighter/).

## Installation ##
 - Install the PHP Markdown plugin
 - Install the wp-syntax plugin
 - Download the markdown-geshi.php file in your plugin folder
 - Enable all three plugins and you're good to go

## Usage ##
The highlighter is triggered by adding a 'shebang' to the code block. 
The shebang follows the following syntax:

    #!lang@123
    code to be highlighted

`lang` is a language to use for highlighting and `123` is a line number 
to start the numbering with.

### Example ###
    #!php@12
    while(true)
        echo "Infinite time...!";

Would render the code starting at `while` and starting with line number 
12.

The line number is optional, so omitting it would start numbering at 1.

If the shebang is omitted, the standard code block handler from the 
Markdown_Parser class is used.

## Troubleshooting ##
If you have any trouble using the plugin, please report an [issue at github.com](http://github.com/drm/Markdown_Geshi/issues)
