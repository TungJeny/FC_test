<?php

function isActiveMenu($moduleId, $currentModuleId)
{
    $moduleMapping = [
        'employees' => ['departments'],
    ];
    return ($moduleId == $currentModuleId || (!empty($moduleMapping[$moduleId]) && in_array($currentModuleId, $moduleMapping[$moduleId])));
}