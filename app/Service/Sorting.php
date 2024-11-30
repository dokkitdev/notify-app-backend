<?php

namespace App\Service;

use Illuminate\Http\Request;

class Sorting
{
    static public function order($title, $key)
    {
        /** @var Request $request */
        $request = app('request');
        $sort = $request->get('sort');
        $direction = $sort == $key && $request->get('direction') == 'asc' ? 'desc' : 'asc';

        $uri = \URL::current();

        $params = $request->except(['page', 'sort', 'direction']);
        $uri = '<a href="'.$uri;
        $uri .= '?'.http_build_query($params).'&sort='.$key.'&direction='.$direction.'&page=1';
        $uri .= '">'.$title;
        $icon = 'fas fa-sort';
        if ($sort == $key) {
            $icon = $direction == 'desc' ? 'fa-sort-down' : 'fa-sort-up';
        }
        $uri .= ' <i class="fas '.$icon.'"></i><a/>';

        return $uri;
    }
}
