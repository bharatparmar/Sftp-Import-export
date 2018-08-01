<?php

namespace Dpool\Website\Command;

use phpseclib\Net\SFTP;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MigrateUploadFilesToFALCommandController
 * @package Dpool\App\Command
 */

class ImportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

     /**
     * @param int $sourcePath (Full Source Path of server)
     * @param string $task (The values can be: importString = Import a XML string importFile = Import a XML file preview = Generate a preview of the source from a XML string)
     * @param string $destinationPath (Path of local server relative to site root path)
     * @param string $notificationEmails (Comma-seprated email list)
     *
    */
	public function importCommand($sourcePath,$task,$destinationPath,$notificationEmails) {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        // Get Configuration
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $extbaseFrameworkConfiguration['plugin.']['tx_website_website.']['settings.'];

        $sftpDetail = [
            'host'=>$settings['server.']['host'],
            'user'=>$settings['server.']['user'],
            'password'=>$settings['server.']['password'],
            'port'=>$settings['server.']['port']

        ];

        try {
            $sftp = new SFTP($sftpDetail['host'],$sftpDetail['port'],60);

            if($sftp->login($sftpDetail['user'],$sftpDetail['password'])) {
                // Download file
                $sftp->get($sourcePath, PATH_site.$destinationPath);

                // Preparing argument for l10n import
                $_SERVER['argv'] = ['./typo3/cli_dispatch.phpsh',"l10nmgr_import","--task=".$task,"--file=".PATH_site.$destinationPath];

                $importObject = GeneralUtility::makeInstance(\Localizationteam\L10nmgr\Cli\Import::class);
                $importObject->cli_main($_SERVER['argv']);

                // Send notification
                $recievers = explode(',',$notificationEmails);
                if(count($recievers)>0){
                    foreach($recievers as $reciever){
                         $recieverMailArguments = [
                            'templateName' => 'Import',
                            'receiverName' => $reciever,
                            'receiverEmail' => $reciever,
                            'senderEmail' => $settings['senderEmail'],
                            'senderName' => $settings['senderName'],
                            'subject' => $settings['importSubject'],
                            'filename' => PATH_site.$destinationPath,
                        ];
                        $mailService->sendMail($recieverMailArguments);
                    }
                }

            }
        }
        catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
        return true;

    }


}

?>