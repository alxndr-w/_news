<?php

if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType('rex_cronjob_neues_publish');
}

if (rex_addon::get('yform')->isAvailable() && !rex::isSafeMode()) {
    rex_yform_manager_dataset::setModelClass(
        'rex_neues_entry',
        neues_entry::class,
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_neues_category',
        neues_category::class,
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_neues_author',
        neues_author::class,
    );
    rex_yform_manager_dataset::setModelClass(
        'rex_neues_entry_lang',
        neues_entry_lang::class,
    );
}

if (rex::isBackend() && 'neues/entry' == rex_be_controller::getCurrentPage() || 'yform/manager/data_edit' == rex_be_controller::getCurrentPage()) {
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $suchmuster = 'class="###neues-settings-editor###"';
        $ersetzen = rex_config::get('neues', 'editor');
        $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
    });
}

if (rex_plugin::get('yform', 'rest')->isAvailable() && !rex::isSafeMode()) {
    /* YForm Rest API */
    $rex_neues_entry_route = new rex_yform_rest_route(
        [
            'path' => '/neues/4/date/',
            'auth' => '\rex_yform_rest_auth_token::checkToken',
            'type' => neues_entry::class,
            'query' => neues_entry::query(),
            'get' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'id',
                        'status',
                        'name',
                        'teaser',
                        'description',
                        'domain_ids',
                        'lang_id',
                        'publishdate',
                        'author_id',
                        'url',
                        'image',
                        'images',
                        'createdate',
                        'createuser',
                        'updatedate',
                        'updateuser',
                    ],
                    'rex_neues_category' => [
                        'id',
                        'name',
                        'image',
                        'status',
                    ],
                    'rex_neues_author' => [
                        'id',
                        'name',
                        'nickname',
                        'text',
                        'image',
                        'be_user_id',
                    ],
                ],
            ],
            'post' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'status',
                        'name',
                        'teaser',
                        'description',
                        'domain_ids',
                        'lang_id',
                        'publishdate',
                        'author_id',
                        'url',
                        'image',
                        'images',
                        'createdate',
                        'createuser',
                        'updatedate',
                        'updateuser',
                    ],
                ],
            ],
            'delete' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'id',
                    ],
                ],
            ],
        ],
    );

    rex_yform_rest::addRoute($rex_neues_entry_route);

    /* YForm Rest API */
    $rex_neues_category_route = new rex_yform_rest_route(
        [
            'path' => '/neues/4/category/',
            'auth' => '\rex_yform_rest_auth_token::checkToken',
            'type' => neues_category::class,
            'query' => neues_category::query(),
            'get' => [
                'fields' => [
                    'rex_neues_category' => [
                        'id',
                        'name',
                        'image',
                        'status',
                    ],
                ],
            ],
            'post' => [
                'fields' => [
                    'rex_neues_category' => [
                        'name',
                        'image',
                        'status',
                    ],
                ],
            ],
            'delete' => [
                'fields' => [
                    'rex_neues_category' => [
                        'id',
                    ],
                ],
            ],
        ],
    );

    rex_yform_rest::addRoute($rex_neues_category_route);
}

rex_extension::register('YFORM_DATA_LIST', static function ($ep) {
    if ('rex_neues_entry' == $ep->getParam('table')->getTableName()) {
        $list = $ep->getSubject();

        $list->setColumnFormat(
            'name',
            'custom',
            static function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_neues_entry')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = [];
                $params['table_name'] = 'rex_neues_entry';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';

                return '<a href="' . rex_url::backendPage('neues/entry', $params) . '">' . $a['value'] . '</a>';
            },
        );
        $list->setColumnFormat(
            'neues_category_id',
            'custom',
            static function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_neues_category')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = [];
                $params['table_name'] = 'rex_neues_category';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';

                $return = [];

                $category_ids = array_filter(array_map('intval', explode(',', $a['value'])));

                foreach ($category_ids as $category_id) {
                    /** @var neues_category $neues_category */
                    $neues_category = neues_category::get($category_id);
                    if ($neues_category) {
                        $return[] = '<a href="' . rex_url::backendPage('neues/category', $params) . '">' . $neues_category->getName() . '</a>';
                    }
                }
                return implode('<br>', $return);
            },
        );
    }
});
