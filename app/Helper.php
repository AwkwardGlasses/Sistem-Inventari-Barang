<?php

/**
 * decode json resource from base_path('database/resource/json/*')
 * @param string $json_file_name : file name
 * @param boolean $assoc : decode as associative array or not, default false;
 * @param string $json_path : custom path
 */
if (!function_exists('db_resource_json')) {
    function db_resource_json($json_file_name, $assoc = false, $json_path = "database/seeds/resources/json") {
        $data = \Illuminate\Support\Facades\File::get(base_path("{$json_path}/{$json_file_name}.json"));
        return json_decode($data, $assoc);
    }
}
