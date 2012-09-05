<?php
/**
 * Smarty plugin
 * Author: André Fielder < mail [at] andrefiedler [dot] de >
 * -------------------------------------------------------------
 * File: outputfilter.highlight_search_words.php
 * Type: outputfilter
 * Name: highlight_search_words
 * Purpose: Highlights words whitch were searched thrue an
 * searchengine below. A css class named searchWords
 * musst be available.
 * -------------------------------------------------------------
 *
 * @param string                   $output the output
 * @param Smarty_Internal_Template $smarty the engine
 * 
 * @return string output
 *
 * @version 1.0
 * @copyright 2005, André Fiedler
 */
function smarty_outputfilter_highlightsearch($output, Smarty_Internal_Template $smarty) {
    $search_engines = array();
    $conf = \FLS\Lib\Configuration\Configuration::getInstance();
    $ownHost = parse_url($conf->default->address->value);
    if (isset($ownHost['host'])) {
        $ownHost = $ownHost['host'];
    } else {
        $ownHost = 'localhost';
    }

    //---------------ENGINE-------VAR-NAME--------
    $search_engines['google'] = 'q';
    $search_engines['yahoo'] = 'p';
    $search_engines['lycos'] = 'query';
    $search_engines['altavista'] = 'q';
    $search_engines['alltheweb'] = 'q';
    $search_engines['excite'] = 'search';
    $search_engines['msn'] = 'q';
    $search_engines[$ownHost] = 'q';
    //--------------------------------------------

    if (!isset($_SERVER['HTTP_REFERER'])) {
        $url = array('host' => 'fls-wiesbaden.de', 'query' => $_SERVER['QUERY_STRING']);
    } else {
        $url = parse_url($_SERVER['HTTP_REFERER']);
    }
    $pattern = array();
    $replace = array();

    foreach($search_engines as $engine_name => $var_name) {
        if(preg_match("/($engine_name)/i", $url['host']) && isset($url['query'])) {
            parse_str($url['query'], $query_vars);
            if (isset($query_vars[$var_name])) {
                $words = explode(' ', urldecode($query_vars[$var_name]));
                foreach($words as $k => $word) {
                    if(trim($word) != '') {
                        $pattern[$k] = "/((<[^>]*)|$word)/ie";
                        $replace[$k] = '"\2"=="\1"? "\1":"<span class=\"searchWords\">\1</span>"';
                    }
                }
                if (count($pattern) > 0) {
                    $output = preg_replace($pattern, $replace, $output);
                }
            }
        }
    }
    return $output;
}
?>
