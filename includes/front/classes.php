<?php

class Bootstrap_NavWalker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="dropdown-menu">';
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

        if ( in_array('menu-item-has-children', $classes) ) {
            $class_names .= ' dropdown';
        }

        $output .= '<li class="' . esc_attr( $class_names ) . '">';

        $atts = array();
        $atts['class'] = 'nav-link';
        if ( $depth == 0 && in_array('menu-item-has-children', $classes) ) {
            $atts['class'] .= ' dropdown-toggle';
            $atts['data-bs-toggle'] = 'dropdown';
            $atts['aria-expanded'] = 'false';
        }

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            $attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
        }

        $output .= '<a' . $attributes . ' href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
    }
}
