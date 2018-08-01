<?php

defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
	// Import from source server
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['l10nmgr-Export'] =
        \Dpool\Website\Command\ExportCommandController::class;

    // export to destination server
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['l10nmgr-Import'] =
        \Dpool\Website\Command\ImportCommandController::class;
}

