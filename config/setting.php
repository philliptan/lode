<?php
use Cake\Core\Configure;

Configure::write('Area', [
    'north' 	=> ['code' => 1, 'text' => 'Miền Bắc'],
    'center' 	=> ['code' => 2, 'text' => 'Miền Trung'],
    'south' 	=> ['code' => 3, 'text' => 'Miền Nam'],
]);

Configure::write('City', [
    1 => ['code' => 1, 'text' => 'Hà nội', 'w' => 0],
    2 => ['code' => 2, 'text' => 'Quảng Ninh', 'w' => 1],
    3 => ['code' => 3, 'text' => 'Bắc Ninh', 'w' => 2],
    4 => ['code' => 4, 'text' => 'Hà nội', 'w' => 3],
    5 => ['code' => 5, 'text' => 'Hải Phòng', 'w' => 4],
    6 => ['code' => 6, 'text' => 'Nam Định', 'w' => 5],
    7 => ['code' => 7, 'text' => 'Thái Bình', 'w' => 6],
]);

Configure::write('CITY_IN_SOUTH', [
    //'hcm' => ['code' => 1, 'slug' => 'tp-hcm', 'text' => 'Hồ Chí Minh', 'w' => [1, 6]],
    // 'dongthap' => ['code' => 2, 'slug' => 'dong-thap', 'text' => 'Đồng Tháp', 'w' => [1]],
    // 'bentre' => ['code' => 3, 'slug' => 'ben-tre', 'text' => 'Bến Tre', 'w' => [2]],
    // 'vungtau' => ['code' => 4, 'slug' => 'vung-tau', 'text' => 'Vũng Tàu', 'w' => [2]],
    // 'dongnai' => ['code' => 5, 'slug' => 'dong-nai', 'text' => 'Đồng Nai', 'w' => [3]],
    // 'cantho' => ['code' => 6, 'slug' => 'can-tho', 'text' => 'Cần Thơ', 'w' => [3]],
    'tayninh' => ['code' => 7, 'slug' => 'tay-ninh', 'text' => 'Tây Ninh', 'w' => [4]],
    'angiang' => ['code' => 8, 'slug' => 'an-giang', 'text' => 'An Giang', 'w' => [4]],
    'vinhlong' => ['code' => 9, 'slug' => 'vinh-long', 'text' => 'Vĩnh Long', 'w' => [5]],
    'binhduong' => ['code' => 10, 'slug' => 'binh-duong', 'text' => 'Bình Dương', 'w' => [5]],
    'longan' => ['code' => 11, 'slug' => 'long-an', 'text' => 'Long An', 'w' => [6]],
    'tiengiang' => ['code' => 12, 'slug' => 'tien-giang', 'text' => 'Tiền Giang', 'w' => [0]],
    'kiengiang' => ['code' => 13, 'slug' => 'kien-giang', 'text' => 'Kiên Giang', 'w' => [0]],
]);

Configure::write('Result_Level', [
    'giaidb' => 1,
    'giai1' => 2,
    'giai2' => 3,
    'giai3' => 4,
    'giai4' => 5,
    'giai5' => 6,
    'giai6' => 7,
    'giai7' => 8,
    'giai8' => 9,
]);

Configure::write('RATIO', [
    'WIN'     => 73,
    'LOSE'    => 79,
]);

Configure::write('BET', [
    'WIN_ON_DAY' => 300,
    'WIN_MIN'    => 4,
    'MONEY_START' => 5000
]);