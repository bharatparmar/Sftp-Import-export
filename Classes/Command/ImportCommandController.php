<?php

namespace Dpool\Website\Command;

use phpseclib\Net\SFTP;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImportCommandController
 * @package Dpool\App\Command
 */

class ImportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

     /**
     * @param string $sourcePath (Full Source Path of server)
     * @param string $task (The values can be: importString = Import a XML string importFile = Import a XML file preview = Generate a preview of the source from a XML string)
     * @param string $destinationPath (Path of local server relative to site root path)
     * @param string $notificationEmails (Comma-seprated email list)
     *
    */
	public function importCommand($sourcePath,$task,$destinationPath,$notificationEmails) {

        $sftp = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['website']);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        // Get Configuration
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $extbaseFrameworkConfiguration['plugin.']['tx_website_website.']['settings.'];

        $sftpDetail = [
            'host'=>$sftp['server.']['host'],
            'user'=>$sftp['server.']['user'],
            'password'=>$sftp['server.']['password'],
            'port'=>$sftp['server.']['port']

        ];

        try {
            $sftp = new SFTP($sftpDetail['host'],$sftpDetail['port'],60);

            if($sftp->login($sftpDetail['user'],$sftpDetail['password'])) {
                $allFiles = $sftp->nlist($sourcePath,true);
                $files = [];
                foreach($allFiles as $file){


                   if($sftp->is_file($sourcePath.$file))
                   {
                        $path_info = pathinfo($sourcePath.$file);
                        if($path_info['extension']=='xml'){
                            $files[] = $file;
                        }
                   }

                }

                // Download file and import
                chmod(PATH_site.$destinationPath, 755);
                foreach($files as $file){
                    $path_info = pathinfo($sourcePath.$file);
                    $sftp->get($sourcePath.$file, PATH_site.$destinationPath.$path_info['basename']);

                    $_SERVER['argv'] = ['./typo3/cli_dispatch.phpsh',"l10nmgr_import","--task=".$task,"--file=".PATH_site.$destinationPath.$path_info['basename']];

                    $importObject = GeneralUtility::makeInstance(\Localizationteam\L10nmgr\Cli\Import::class);
                    $importObject->cli_main($_SERVER['argv']);
                    // delete local files so that
                    //chmod(PATH_site.$destinationPath.$path_info['basename'], 755);
                    //unlink(PATH_site.$destinationPath.$path_info['basename']);

                }
                // delete file once import finished
                foreach($files as $file){
                    $sftp->delete($sourcePath.$file);
                }

                $mailService = $objectManager->get('Dpool\\Website\\Service\\MailService');

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

                        ];
                        $mailService->sendMail($recieverMailArguments);
                    }
                }

            }
        }
        catch(Exception $e) {
            $this->output($e->getMessage());
        }
        return true;

    }


}

?>