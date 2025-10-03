<?php

return [
    'pre_check_items' => [
        ['key' => 'license', 'label' => '本人確認（免許証・顔写真）を行った'],
        ['key' => 'damage_front', 'label' => '車両前方の傷・凹みを一緒に確認した'],
        ['key' => 'damage_side', 'label' => '左右側面の傷・凹みを一緒に確認した'],
        ['key' => 'damage_rear', 'label' => '車両後方の傷・凹みを一緒に確認した'],
        ['key' => 'tire', 'label' => 'タイヤの摩耗・空気圧を確認した'],
        ['key' => 'fuel', 'label' => '燃料残量と給油ルールを共有した'],
        ['key' => 'controls', 'label' => '操作方法（モード切替・装備）を説明した'],
        ['key' => 'documents', 'label' => '車検証・保険証の所在を案内した'],
        ['key' => 'accessories', 'label' => '付属品（鍵・ヘルメット等）の受け渡しを確認した'],
        ['key' => 'warnings', 'label' => '注意事項・禁止事項を共有した'],
    ],

    'post_check_items' => [
        ['key' => 'fuel_return', 'label' => '返却時の燃料量を確認した'],
        ['key' => 'cleanliness', 'label' => '内外装の汚れ・清掃状況を確認した'],
        ['key' => 'damage_new', 'label' => '新たな傷・破損が無いことを確認した'],
        ['key' => 'tire_post', 'label' => 'タイヤの状態を確認した'],
        ['key' => 'mileage', 'label' => '走行距離を控えた'],
        ['key' => 'accessories_return', 'label' => '付属品・鍵の返却を確認した'],
        ['key' => 'documents_return', 'label' => '書類・備品の返却を確認した'],
        ['key' => 'trouble_report', 'label' => '走行中のトラブルや気づきを共有した'],
        ['key' => 'cleanup', 'label' => '車両の簡易清掃を確認した'],
        ['key' => 'next_steps', 'label' => '今後の連絡方法・評価方法を共有した'],
    ],

    'qa_links' => [
        ['label' => '事故が発生した場合', 'url' => '#'],
        ['label' => '返却が遅れそうなとき', 'url' => '#'],
        ['label' => '車両に不調を感じたとき', 'url' => '#'],
    ],
];
