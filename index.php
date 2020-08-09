<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Fundevogel\Donut;

function saveSVG(Kirby\Cms\Page $page, array $data)
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

Kirby::plugin('fundevogel/donuts', [
    'options' => [
        'thickness' => 3,
        'spacing' => 0.005,
        'size' => 100,
        'inline' => false,
        'template' => 'donut',
    ],
    'blueprints' => [
        'fields/donut' => __DIR__ . '/blueprints/field.yml',
        'files/donut' => __DIR__ . '/blueprints/file.yml',
    ],
    'pageMethods' => [
        /**
         * @param array $entries
         * @param float $thickness
         * @param float $spacing
         * @return Kirby\Cms\File|string
         */
        'toDonut' => function (
            array $entries,
            float $thickness = null,
            float $spacing = null
        ) {
            try {
                $file = saveSVG($this, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                ]);
            } catch (Exception $e) {
                throw $e;
            }

            return $file;
        }
    ],
    'fieldMethods' => [
        /**
         * @param Kirby\Cms\Field $field
         * @param float $thickness
         * @param float $spacing
         * @return Kirby\Cms\File|string
         */
        'toDonut' => function (
            Kirby\Cms\Field $field,
            float $thickness = null,
            float $spacing = null
        ) {
            $page = $field->model();
            $entries = $field->toStructure()->toArray();

            try {
                $file = saveSVG($page, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                ]);
            } catch (Exception $e) {
                throw $e;
            }

            return $file;
        }
    ]
]);
