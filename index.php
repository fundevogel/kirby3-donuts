<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Fundevogel\Chart;

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
                $file = (new Chart($this, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                ]))->render();
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
                $file = (new Chart($page, [
                    'entries' => $entries,
                    'thickness' => $thickness,
                    'spacing' => $spacing,
                ]))->render();
            } catch (Exception $e) {
                throw $e;
            }

            return $file;
        }
    ]
]);
