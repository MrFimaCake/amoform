<?php
/**
 * Created by PhpStorm.
 * User: mrcake
 * Date: 12/2/17
 * Time: 12:41 AM
 */

namespace amocrm;


class View
{
    public static $FORCE_MODE_SHOW_FORM = 1;
    public static $FORCE_MODE_PROCESS_FORM = 2;

    private $file;

    protected $fields = [];
    protected $errors = [];

    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Render form with given variables
     *
     * @param array $variables
     * @return string
     */
    public function render($variables = [])
    {
        ob_start();

        extract($variables);

        require $this->file;

        return ob_get_clean();
    }
}