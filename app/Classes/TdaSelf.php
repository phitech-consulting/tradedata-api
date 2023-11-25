<?php

namespace app\Classes;

use Illuminate\Foundation\Application;

class TdaSelf
{
    const VERSION = "v0.7";
    const LAST_EDIT_AT = "2023-11-25 12:30";
    const LAST_EDIT = "Dynamic scheduling of daily IEX symbol sets";


    /**
     * Returns a list of all properties for this installation.
     * Use as follows: "echo new App\Classes\TdaSelf;".
     * @return string
     */
    public function __toString() {
        $self_description = "\n";
        foreach(self::describe() as $property => $value) {
            $self_description .= $property . " = " . $value . "\n";
        }
        $self_description .= "\n";
        return $self_description;
    }


    /**
     * Method that returns version of current Tradedata API.
     * @return string
     */
    public static function get_version() {
        return self::VERSION;
    }


    /**
     * Get an array representation of the application properties.
     * @return array
     */
    public static function describe() {
        return [
            'name' => config("app.name"),
            'env' => config("app.env"),
            'debug' => config("app.debug"),
            'url' => config("app.url"),
            'timezone' => config("app.timezone"),
            'framework_version' => Application::VERSION,
            'version' => self::VERSION,
            'last_edit_at' => self::LAST_EDIT_AT,
            'last_edit' => self::LAST_EDIT,
        ];
    }
}