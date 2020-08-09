<?php

namespace Fundevogel;

use Fundevogel\Donut;

use \Kirby\Cms\File;
use \Kirby\Cms\Page;


/**
 * Class Chart
 *
 * Creates donut charts for Kirby v3
 *
 * @package kirby3-donuts
 */
class Chart
{
    /**
     * Current version number of kirby3-donut
     */
    const VERSION = '0.1.0';


    /**
     * This contains:
     * - Data points being visualized, where each entry consists of
     *   - a color string
     *   - a value representing the share (between 0 and 1)
     * - Thickness of the chart
     * - Spacing between chart segments
     * - Viewport width & height
     *
     * @var array
     */
    private $options;


    public function __construct(
        Kirby\Cms\Page $page,
        array $options
    ) {
        $this->page = $page;
        $this->thickness = $options['thickness'];
        $this->entries = $options['entries'];
        $this->spacing = $options['spacing'];
    }

    function render(Kirby\Cms\Page $page, array $data)
    {
        $thickness = $thickness ?? option('fundevogel.donuts.thickness');
        $spacing = $spacing ?? option('fundevogel.donuts.spacing');

        $donut = new Donut(
            $data['entries'],
            $data['thickness'],
            $data['spacing']
        );

        $content = $donut->getSVGElement();

        $file = new File([
            'parent' => $page,
            'filename' => 'chart-' . hash('md5', $content),
        ]);

        $file->update([
            'template' => option('fundevogel.donuts.template'),
        ]);

        if (file_exists($file->root())) {
            if (option('fundevogel.donuts.inline') === true) {
                return svg($file);
            }

            return $file;
        }

        if (F::write($file->root(), $content)) {

            if (option('fundevogel.donuts.inline') === true) {
                return svg($file);
            }

            return $file;
        }

        throw new Exception('Couldn\'t create chart!');
    }
}
