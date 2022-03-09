<?php

namespace Plugin\SortProduct4Plus;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getNav()
    {
        return [
            'product' => [
                'children' => [
                    'sort_product' => [
                        'name' => 'sort_product.nav',
                        'url' => 'plugin_SortProduct',
                    ]
                ]
            ]
        ];
    }
}
