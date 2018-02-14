<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace digitv\bootstrap\widgets;

use digitv\bootstrap\assets\BootstrapPluginAsset;
use digitv\bootstrap\Html;
use digitv\bootstrap\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Collapse renders an accordion bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Collapse::widget([
 *     'items' => [
 *         // equivalent to the above
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             // open its content by default
 *             'contentOptions' => ['class' => 'in']
 *         ],
 *         // another group item
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             'contentOptions' => [...],
 *             'options' => [...],
 *         ],
 *         // if you want to swap out .panel-body with .list-group, you may use the following
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => [
 *                 'Anim pariatur cliche...',
 *                 'Anim pariatur cliche...'
 *             ],
 *             'contentOptions' => [...],
 *             'options' => [...],
 *             'footer' => 'Footer' // the footer label in list-group
 *         ],
 *     ]
 * ]);
 * ```
 *
 * @see http://getbootstrap.com/javascript/#collapse
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Collapse extends Widget
{
    /**
     * @var array list of groups in the collapse widget. Each array element represents a single
     * group with the following structure:
     *
     * - label: string, required, the group header label.
     * - encode: boolean, optional, whether this label should be HTML-encoded. This param will override
     *   global `$this->encodeLabels` param.
     * - content: array|string|object, required, the content (HTML) of the group
     * - options: array, optional, the HTML attributes of the group
     * - contentOptions: optional, the HTML attributes of the group's content
     *
     * Since version 2.0.7 you may also specify this property as key-value pairs, where the key refers to the
     * `label` and the value refers to `content`. If value is a string it is interpreted as label. If it is
     * an array, it is interpreted as explained above.
     *
     * For example:
     *
     * ```php
     * echo Collapse::widget([
     *     'items' => [
     *       'Introduction' => 'This is the first collapsable menu',
     *       'Second panel' => [
     *           'content' => 'This is the second collapsable menu',
     *       ],
     *       [
     *           'label' => 'Third panel',
     *           'content' => 'This is the third collapsable menu',
     *       ],
     *   ]
     * ])
     * ```
     */
    public $items = [];
    /**
     * @var boolean whether the labels for header items should be HTML-encoded.
     */
    public $encodeLabels = true;
    /**
     * @var boolean whether to close other items if an item is opened. Defaults to `true` which causes an
     * accordion effect. Set this to `false` to allow keeping multiple items open at once.
     * @since 2.0.7
     */
    public $autoCloseItems = true;


    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerPlugin('collapse');
        return implode("\n", [
            Html::beginTag('div', $this->options),
            $this->renderItems(),
            Html::endTag('div')
        ]) . "\n";
    }

    /**
     * Renders collapsible items as specified on [[items]].
     * @throws InvalidConfigException if label isn't specified
     * @return string the rendering result
     */
    public function renderItems()
    {
        $items = [];
        $index = 0;
        foreach ($this->items as $key => $item) {
            if (!is_array($item)) {
                $item = ['content' => $item];
            }
            if (!array_key_exists('label', $item)) {
                if (is_int($key)) {
                    throw new InvalidConfigException("The 'label' option is required.");
                } else {
                    $item['label'] = $key;
                }
            }
            $items[] = $this->renderItem($item, ++$index);
        }

        return implode("\n", $items);
    }

    /**
     * Renders a single collapsible item group
     * @param string $header a label of the item group [[items]]
     * @param array $item a single item from [[items]]
     * @param int $index the item index as each item group content must have an id
     * @return string the rendering result
     * @throws InvalidConfigException
     */
    public function renderItem($item, $index)
    {
        $header = $item['label'];
        if (array_key_exists('content', $item)) {
            $id = $this->options['id'] . '-collapse-' . $index;
            $options = ArrayHelper::getValue($item, 'contentOptions', []);
            $options['id'] = $id;
            Html::addCssClass($options, ['widget' => 'collapse']);

            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            if ($encodeLabel) {
                $header = Html::encode($header);
            }

            $headerOptions = [
                'class' => 'collapse-toggle',
                'data-toggle' => 'collapse',
            ];

            $headerToggle = Html::a($header, '#' . $id, $headerOptions) . "\n";

            if (is_string($item['content']) || is_numeric($item['content']) || is_object($item['content'])) {
                $content = Html::tag('div', $item['content'], ['class' => 'card-body']) . "\n";
            } elseif (is_array($item['content'])) {
                $content = Html::ul($item['content'], [
                    'class' => 'list-group list-group-flush',
                    'itemOptions' => [
                        'class' => 'list-group-item'
                    ],
                    'encode' => false,
                ]) . "\n";
            } else {
                throw new InvalidConfigException('The "content" option should be a string, array or object.');
            }
        } else {
            throw new InvalidConfigException('The "content" option is required.');
        }

        if ($this->autoCloseItems) {
            $options['data-parent'] = '#' . $this->options['id'];
        }
        $contentCollapse = Html::tag('div', $content, $options);

        $cardConfig = [
            'options' => [
                'id' => $this->id . '-collapse-card-' . $index,
            ],
            'body' => $contentCollapse,
            'bodyOptions' => null,
            'header' => $headerToggle,
            'footer' => isset($item['footer']) ? $item['footer'] : null,
        ];

        return Card::widget($cardConfig);
    }

    /**
     * Registers a specific Bootstrap plugin and the related events
     * @param string $name the name of the Bootstrap plugin
     */
    protected function registerPlugin($name = 'collapse')
    {
        $view = $this->getView();

        BootstrapPluginAsset::register($view);

        $this->registerClientEvents();
    }

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     * @since 2.0.2
     */
    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $idWidget = $this->options['id'];
            $itemsCount = count($this->items);
            for ($i = 1; $i <= $itemsCount; $i++) {
                $id = $idWidget . '-collapse-' . $i;
                $js = [];
                foreach ($this->clientEvents as $event => $handler) {
                    $js[] = "jQuery('#$id').on('$event', $handler);";
                }
                $this->getView()->registerJs(implode("\n", $js));
            }
        }
    }
}
