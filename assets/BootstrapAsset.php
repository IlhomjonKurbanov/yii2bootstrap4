<?php

namespace digitv\bootstrap\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Twitter Bootstrap v4 CSS
 *
 * @link https://getbootstrap.com/docs/4.0/getting-started/introduction/
 * @author Digit <digit.vova@gmail.com>
 */
class BootstrapAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twbs/bootstrap/dist/css';
    public $css = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        //Add css depending on user environment
        $this->css[] = YII_ENV_DEV ? 'bootstrap.css' : 'bootstrap.min.css';
        parent::init();
    }
}