<?php
use Kirby\Cms\App;

/**
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Pages $pages
 */

Kirby::plugin('villa1203/custom-fields-methods', [
  'fieldMethods' => [
    'toBlocks_custom' => function ($field) {

      return $field->toBlocks()->map(function ($item) {
        return [
          'content' => $item->toArray(),
          'img_srcset' => [
            'tiny'  => $item->content()->image()->toFile()?->resize(50, null, 10)->url(),
            'small' => $item->content()->image()->toFile()?->resize(500)->url(),
            'reg'   => $item->content()->image()->toFile()?->resize(1280)->url(),
            'large' => $item->content()->image()->toFile()?->resize(1920)->url(),
            'xxl'   => $item->content()->image()->toFile()?->resize(2500)->url(),
          ],
        ];
      })->data();

    },
    'toStructure_custom' => function ($field) {

      return $field->toBlocks()->map(function ($item) {
        return [
          'content' => $item->toArray(),
          'img_srcset' => [
            'tiny'  => $item->content()->image()->toFile()?->resize(50, null, 10)->url(),
            'small' => $item->content()->image()->toFile()?->resize(500)->url(),
            'reg'   => $item->content()->image()->toFile()?->resize(1280)->url(),
            'large' => $item->content()->image()->toFile()?->resize(1920)->url(),
            'xxl'   => $item->content()->image()->toFile()?->resize(2500)->url(),
          ],
        ];
      })->data();

    }

  ]
]);
