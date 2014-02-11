<?php

namespace ETNA\FeatureContext;

function fake_time()
{
    return real_strtotime("2013-11-13 14:42:42");
}
function fake_date($format, $timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }
    return real_date($format, $timestamp);
}
function fake_strtotime($time, $now = null)
{
    if ($now === null) {
        $now = time();
    }
    return real_strtotime($time, $now);
}

trait FixedTime
{
    /**
     * @BeforeSuite
     */
    public static function blackMagicBeforeSuite()
    {
        $rename = function ($name) {
            runkit_function_rename($name, "real_{$name}");
            runkit_function_rename("ETNA\\FeatureContext\\fake_{$name}", $name);
        };
        $rename("time");
        $rename("date");
        $rename("strtotime");
    }
}
