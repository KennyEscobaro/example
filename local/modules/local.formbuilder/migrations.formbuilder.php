<?php

use Local\FormBuilder\Helper\ModuleHelper;

return
    [
        'title' => 'FormBuilder',
        'migration_dir' => ModuleHelper::getModulePath() . '/migrations/',
        'migration_dir_absolute' => true,
        'migration_table' => 'lfb_migration_version',
        'exchange_dir' => ModuleHelper::getModulePath() . '/migrations/',
        'exchange_dir_absolute' => true,
    ];
