<?php

/**
 * Generates HTML from tokens.
 * @todo Refactor interface so that configuration/context is determined
 *       upon instantiation, no need for messy generateFromTokens() calls
 * @todo Make some of the more internal functions protected, and have
 *       unit tests work around that
 */
class HTMLPurifier_Generator
{
    
    /**
     * Bool cache of %HTML.XHTML
     * @private
     */
    private $_xhtml = true;
    
    /**
     * Bool cache of %Output.CommentScriptContents
     * @private
     */
    private $_scriptFix = false;
    
    /**
     * Cache of HTMLDefinition
     * @private
     */
    private $_def;
    
    /**
     * Generates HTML from an array of tokens.
     * @param $tokens Array of HTMLPurifier_Token
     * @param $config HTMLPurifier_Config object
     * @return Generated HTML
     */
    public function generateFromTokens($tokens, $config, $context) {
        $html = '';
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        $this->_scriptFix   = $config->get('Output', 'CommentScriptContents');
        
        $this->_def = $config->getHTMLDefinition();
        $this->_xhtml = $this->_def->doctype->xml;
        
        if (!$tokens) return '';
        for ($i = 0, $size = count($tokens); $i < $size; $i++) {
            if ($this->_scriptFix && $tokens[$i]->name === 'script'
                && $i + 2 < $size && $tokens[$i+2] instanceof HTMLPurifier_Token_End) {
                // script special case
                // the contents of the script block must be ONE token
                // for this to work
                $html .= $this->generateFromToken($tokens[$i++]);
                $html .= $this->generateScriptFromToken($tokens[$i++]);
                // We're not going to do this: it wouldn't be valid anyway
                //while ($tokens[$i]->name != 'script') {
                //    $html .= $this->generateScriptFromToken($tokens[$i++]);
                //}
            }
            $html .= $this->generateFromToken($tokens[$i]);
        }
        if ($config->get('Output', 'TidyFormat') && extension_loaded('tidy')) {
            
            $tidy_options = array(
               'indent'=> true,
               'output-xhtml' => $this->_xhtml,
               'show-body-only' => true,
               'indent-spaces' => 2,
               'wrap' => 68,
            );
            if (version_compare(PHP_VERSION, '5', '<')) {
                tidy_set_encoding('utf8');
                foreach ($tidy_options as $key => $value) {
                    tidy_setopt($key, $value);
                }
                tidy_parse_string($html);
                tidy_clean_repair();
                $html = tidy_get_output();
            } else {
                $tidy = new Tidy;
                $tidy->parseString($html, $tidy_options, 'utf8');
                $tidy->cleanRepair();
                $html = (string) $tidy;
            }
        }
        // normalize newlines to system
        $nl = $config->get('Output', 'Newline');
        if ($nl === null) $nl = PHP_EOL;
        $html = str_replace("\n", $nl, $html);
        return $html;
    }
    
    /**
     * Generates HTML from a single token.
     * @param $token HTMLPurifier_Token object.
     * @return Generated HTML
     */
    public function generateFromToken($token) {
        if (!$token instanceof HTMLPurifier_Token) return '';
        if ($token instanceof HTMLPurifier_Token_Start) {
            $attr = $this->generateAttributes($token->attr, $token->name);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif ($token instanceof HTMLPurifier_Token_End) {
            return '</' . $token->name . '>';
            
        } elseif ($token instanceof HTMLPurifier_Token_Empty) {
            $attr = $this->generateAttributes($token->attr, $token->name);
             return '<' . $token->name . ($attr ? ' ' : '') . $attr .
                ( $this->_xhtml ? ' /': '' )
                . '>';
            
        } elseif ($token instanceof HTMLPurifier_Token_Text) {
            return $this->escape($token->data);
            
        } elseif ($token instanceof HTMLPurifier_Token_Comment) {
            return '<!--' . $token->data . '-->';
        } else {
            return '';
            
        }
    }
    
    /**
     * Special case processor for the contents of script tags
     * @warning This runs into problems if there's already a literal
     *          --> somewhere inside the script contents.
     */
    public function generateScriptFromToken($token) {
        if (!$token instanceof HTMLPurifier_Token_Text) return $this->generateFromToken($token);
        // return '<!--' . "\n" . trim($token->data) . "\n" . '// -->';
        // more advanced version:
        // thanks <http://lachy.id.au/log/2005/05/script-comments>
        $data = preg_replace('#//\s*$#', '', $token->data);
        return '<!--//--><![CDATA[//><!--' . "\n" . trim($data) . "\n" . '//--><!]]>';
    }
    
    /**
     * Generates attribute declarations from attribute array.
     * @param $assoc_array_of_attributes Attribute array
     * @return Generate HTML fragment for insertion.
     */
    public function generateAttributes($assoc_array_of_attributes, $element) {
        $html = '';
        foreach ($assoc_array_of_attributes as $key => $value) {
            if (!$this->_xhtml) {
                // remove namespaced attributes
                if (strpos($key, ':') !== false) continue;
                if (!empty($this->_def->info[$element]->attr[$key]->minimized)) {
                    $html .= $key . ' ';
                    continue;
                }
            }
            $html .= $key.'="'.$this->escape($value).'" ';
        }
        return rtrim($html);
    }
    
    /**
     * Escapes raw text data.
     * @param $string String data to escape for HTML.
     * @return String escaped data.
     */
    public function escape($string) {
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }
    
}

