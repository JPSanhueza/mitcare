<?php

function strip_tags_for_order_name($name) {
    $title = str_replace(['</p>', '<br>', '<br/>', '<br />'], ' ', $name);
    $title = strip_tags($title);
    $title = preg_replace('/\s+/', ' ', $title);
    $title = trim($title);
    return $title;
}