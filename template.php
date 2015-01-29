<?php

/**
 * @file
 * template.php
 */

/*adding example page template
*/

function asu_slimspark_menuitem_has_active_children($menuitem) {
  if (is_array($menuitem) && isset($menuitem['below']) && !empty($menuitem['below'])) {
    foreach ($menuitem['below'] as $child) {
      if (isset($child['link']) && $child['link']['access'] && ($child['link']['hidden'] == 0)) return true;
    }
  }
  return false;
}


function asu_slimspark_preprocess_page(&$variables) {
  $path = drupal_get_path_alias();
  if ($path == 'example') {
    $variables['theme_hook_suggestions'][] = 'page__example';
  }
    // Make sure default picture gets responsive panopoly stylingz
  if (theme_get_setting('default_picture', 'asu_slimspark') && theme_get_setting('picture_path', 'asu_slimspark')) {
    $image_style = module_exists('asu_cas') ? 'asu_header_image' : 'panopoly_image_full';
    $variables['asu_picture'] = theme('image_style', array(
      'style_name' => $image_style,
      'path' => theme_get_setting('picture_path', 'asu_slimspark'),
    )
    );
  }

    /** Web Standards transplant **/

  /* ----- { Passes ASUHeader.site_menu variable to client side (3 levels deep) } ----- */

  if (module_exists('tb_megamenu') && ($menu_tree = tb_megamenu_get_tree('main-menu'))) {
    $markup = '';
    $ja = array();
    $i=0;
    foreach ($menu_tree as $item) {
      if (isset($item['link']) && $item['link']['access'] && !$item['link']['hidden']) {
        $markup .= sprintf("<li class=\"tlb\"><div class=\"text\">%s</div>", l(t(strip_tags(htmlspecialchars_decode($item['link']['title']))), $item['link']['link_path'], $item['link']['options']));
        $ja[$i]['parent']['title'] = t(strip_tags(htmlspecialchars_decode($item['link']['title'])));
        $ja[$i]['parent']['path'] = t($item['link']['link_path']);
        $ja[$i]['parent']['options'] = t($item['link']['options']);
        // Render child items.
        if (asu_slimspark_menuitem_has_active_children($item)) {
          $markup .= "<div class=\"icn fa f-sort-down\"></div></li>"; // parent toggle icon
          $markup .= "<li class=\"clb closed\"><ul>";
          foreach ($item['below'] as $child) {
            if (isset($child['link']) && !$child['link']['hidden']) {
              $markup .= sprintf("<li class=\"cb\"><div class=\"text\">%s</div>",l(t($child['link']['title']), $child['link']['link_path'], $child['link']['options']));
              $ja[$i]['child']['title'] = t($item['link']['title']);
              $ja[$i]['child']['path'] = t($item['link']['link_path']);
              $ja[$i]['child']['options'] = t($item['link']['options']);
              // Render grandchild items.
              if (asu_slimspark_menuitem_has_active_children($child)) {
                $markup .= "<div class=\"icn2 fa f-sort-down\"></div></li>"; // parent toggle icon
                $markup .= "<li class=\"clb closed\"><ul>";
                $j=0;
                foreach ($child['below'] as $grandchild) {
                  if (isset($grandchild['link']) && !$grandchild['link']['hidden']) {
                    $markup .= sprintf("<li class=\"ccb\"><div class=\"text\">%s</div></li>",l(t($grandchild['link']['title']), $grandchild['link']['link_path'], $grandchild['link']['options']));
                    $ja[$j]['grandchild']['title'] = t($item['link']['title']);
                    $ja[$j]['grandchild']['path'] = t($item['link']['link_path']);
                    $ja[$j]['grandchild']['options'] = t($item['link']['options']);
                    ++$j;
                  }
                }
                $markup .= "</ul></li>";
              } else {
                $markup .= "</li>";
              }
            }
          }
          $markup .= "</ul></li>";
        } else {
          $markup .= "<div class=\"icn fa f-share-square-o\"></div></li>";
        }
        ++$i;
      }
    }
    //dpm($menu_tree);
    //dpm(get_defined_variables());
    //dpm(json_encode($markup));
    $js = '
    ASUHeader = ASUHeader || {};
    ASUHeader.site_menu = ASUHeader.site_menu || {};
    ASUHeader.site_menu.markup = '.json_encode($markup).'
    ASUHeader.site_menu.json = '.json_encode($ja).';
    ASUHeader.site_menu.site_name = '.json_encode($variables['site_name']);
    drupal_add_js($js, array('type' => 'inline', 'scope' => 'header', 'group' => JS_THEME, 'weight' => -10));
  } /* ----- { End Megamenu stuff } ----- */
}

