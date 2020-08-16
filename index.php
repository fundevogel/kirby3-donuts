<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Fundevogel\Donut;

/**
 * @param Kirby\Cms\Page $page
 * @param string $fileName
 * @param array $data
 * @return Kirby\Cms\File|string
 */
function render(Kirby\Cms\Page $page, string $fileName, array $data)
{
    $thickness = $data['thickness'] ?? option('fundevogel.donuts.thickness');
    $spacing = $data['spacing'] ?? option('fundevogel.donuts.spacing');

    try {
        $donut = new Donut(
            $data['entries'],
            $thickness,
            $spacing,
        );
    } catch (Exception $e) {
        throw $e;
    }

    $donut->setSize(option('fundevogel.donuts.size'));
    $donut->setPreferViewbox(option('fundevogel.donuts.viewbox'));
    $donut->setRole(option('fundevogel.donuts.role'));
    $donut->setClasses($data['classes']);
    $donut->setPieChart($data['isPieChart']);
    $donut->setBackgroundColor($data['backgroundColor']);

    $content = $donut->render();

    if ($fileName === '') {
        $fileName = 'chart-' . hash('md5', $content);
    }

    $file = new File([
        'parent' => $page,
        'filename' => $fileName . '.svg',
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
        'viewbox' => true,
        'role' => 'img',
        'inline' => false,
        'template' => 'donut',
    ],
    'blueprints' => [
        'fields/donut' => __DIR__ . '/blueprints/field.yml',
        'files/donut' => __DIR__ . '/blueprints/file.yml',
    ],
    'pageMethods' => [
        'toDonut' => function (
            array $entries,
            string $fileName = '',
            float $thickness = null,
            float $spacing = null,
            string $classes = '',
            bool $isPieChart = false,
            string $backgroundColor = 'transparent'
        ) {
            try {
                $file = render($this, $fileName, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                    'classes' => $classes,
                    'isPieChart' => $isPieChart,
                    'backgroundColor' => $backgroundColor,
                ]);
            } catch (Exception $e) {
                throw $e;
            }

            return $file;
        }
    ],
    'fieldMethods' => [
        'toDonut' => function (
            Kirby\Cms\Field $field,
            string $fileName = '',
            float $thickness = null,
            float $spacing = null,
            string $classes = '',
            bool $isPieChart = false,
            string $backgroundColor = 'transparent'
        ) {
            $page = $field->model();
            $entries = $field->toStructure()->toArray();

            try {
                $file = render($page, $fileName, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                    'classes' => $classes,
                    'isPieChart' => $isPieChart,
                    'backgroundColor' => $backgroundColor,
                ]);
            } catch (Exception $e) {
                throw $e;
            }

            return $file;
        }
    ]
]);
