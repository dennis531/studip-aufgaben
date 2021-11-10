<?php

/**
 * EPPluginStudipController - pimp the controller to work neatly in plugins
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


namespace EPP;

use StudipController;
use PageLayout;
use Trails_Flash;
use Config;
use PluginEngine;
use Request;
use URLHelper;


class Controller extends StudipController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->plugin = $this->dispatcher->current_plugin;
        $this->flash  = Trails_Flash::instance();

        // Localization
        $this->_ = function ($string) {
            return call_user_func_array(
                [$this->plugin, '_'],
                func_get_args()
            );
        };

        $this->_n = function ($string0, $tring1, $n) {
            return call_user_func_array(
                [$this->plugin, '_n'],
                func_get_args()
            );
        };
    }

    /**
     * Intercepts all non-resolvable method calls in order to correctly handle
     * calls to _ and _n.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws RuntimeException when method is not found
     */
    public function __call($method, $arguments)
    {
        $variables = get_object_vars($this);
        if (isset($variables[$method]) && is_callable($variables[$method])) {
            return call_user_func_array($variables[$method], $arguments);
        }
        throw new RuntimeException("Method {$method} does not exist");
    }

    /**
     * overwrite the default url_for to enable to it work in plugins
     * @param type $to
     * @return type
     */
    public function url_for($to = '')
    {
        $args = func_get_args();

        // find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        // urlencode all but the first argument
        $args    = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join('/', $args));
    }

    /**
     * Throw an array at this function and it will call render_text to output
     * the json-version of that array while setting an appropriate http-header
     * @param array $data
     */
    public function render_json($data)
    {
        $this->response->add_header('Content-Type', 'application/json');
        $this->render_text(json_encode($data));
    }


    /**
     * checks all possible locations of a valid seminar_id and retuns it if found
     * @return string the found seminar_id
     */
    public function getSeminarId()
    {
        return \Context::getId();
    }

    /**
     * Return the Content-Type of the HTTP request.
     * @return string the content type
     */
    public function contentType()
    {
        if (preg_match('/^([^,\;]*)/', @$_SERVER['CONTENT_TYPE'], $matches)) {
            return strtolower(trim($matches[1]));
        }
        return null;
    }
}
