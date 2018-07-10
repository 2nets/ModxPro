<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $sections = [
        'store' => [
            'ModStore',
        ],
    ];

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            foreach ($sections as $section => $groups) {
                if ($resources = $modx->getIterator('comSection', ['alias' => $section])) {
                    /** @var comSection $resource */
                    foreach ($resources as $resource) {
                        foreach ($groups as $group) {
                            $resource->joinGroup($group);
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            foreach ($sections as $section => $groups) {
                if ($resources = $modx->getIterator('comSection', ['alias:IN' => $groups])) {
                    /** @var comSection $resource */
                    foreach ($resources as $resource) {
                        foreach ($groups as $group) {
                            $resource->leaveGroup($group);
                        }
                    }
                }
            }
            break;
    }
}

return true;