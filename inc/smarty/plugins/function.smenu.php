<?php

/**
 * @author FLS-Homepapgeteam.moo
 * @copyright 2007
 */
/**
 * Smarty {smenu} plugin
 *
 * Type:     function<br>
 * Name:     smenu<br>
 * Purpose:  display menu and highlight clicked menu point
 * @author FlS-Homepageteam.Moo
 * @param array marr Menuarray, layer(standard 0)
 * @param Smarty
 * @return string menu
 */
function smarty_function_smenu($params, &$smarty) {
    if (isset($params['marr'])) {
        $men = $params['marr'];
    } else {
        $men = $params;
    }
    $layer = (!isset($params['layer']) || $params['layer'] == 0) ? '' : $params['layer'];
    $text = '';

    foreach ($men['child'] as $menu) {
        $draft = '';
        if (isset($menu['release']) && $menu['release'] == '1') {
            $draft = '<img src="res/ico/link_error.png" alt="Entwurf" width="12" height="12" title="Entwurf" />';
        }

        $bold = (isset($menu['highlight']) && $menu['highlight'] === true);

        $text .= '<li class="link '. ($bold ? 'linkHigh' : '') .' menu ' . 
            (isset($menu['child']) ? 'dropdown' : '') . '">' . 
            '<a href="' . $menu['link'] . '">' . $draft . $menu['text'] . '</a>' . "\n";

        if (isset($menu['child']) && is_array($menu['child'])) {
            $text .= smarty_function_smenu($menu, $smarty);
        }
        $text .= '</li>' . "\n";
    }

    if (strlen($text) > 0) {
        $text = '<ul>' . $text . '</ul>';
    }

    return $text;
}
?>
