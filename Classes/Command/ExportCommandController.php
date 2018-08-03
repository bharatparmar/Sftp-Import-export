<?php

namespace Dpool\Website\Command;

use Localizationteam\L10nmgr\Cli;

use phpseclib\Net\SFTP;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class ExportCommandController
 * @package Dpool\App\Command
 */

class ExportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * @param int $configurationId (Configutation Id of L10nmgr)
     * @param string $format (Format to export ex, CATXML/CATXLF/EXCEL)
     * @param string $notificationEmails (Comma-seprated email list)
     * @param string $destinationPath (Full Path of server need to export)
     * @param int $targetLanguageId (Tagetlanguage Id)
    */

    public function exportCommand($configurationId,$format="CATXML",$notificationEmails,$destinationPath,$targetLanguageId) {


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


        $mailService = $objectManager->get('Dpool\\Website\\Service\\MailService');

        try {
            $sftp = new SFTP($sftpDetail['host'],$sftpDetail['port'],60);
            if($sftp->login($sftpDetail['user'],$sftpDetail['password']))
            {
                /*./typo3/cli_dispatch.phpsh l10nmgr_export --format=CATXLF --config=2 --target=3  --hidden=TRUE --updated=FALSE*/
                $_SERVER['argv'] = ['./typo3/cli_dispatch.phpsh',"l10nmgr_export","--format=".$format,"--config=".$configurationId,"--target=".$targetLanguageId,"--hidden=TRUE","--updated=FALSE"];
                $cleanerObj = GeneralUtility::makeInstance(\Localizationteam\L10nmgr\Cli\Export::class);
                $cleanerObj->cli_main($_SERVER['argv']);

                $results = $this->getLatestExported();
                $sftp->put($destinationPath.$results['filename'],PATH_site.'uploads/tx_l10nmgr/jobs/out/'.$results['filename'],SFTP::SOURCE_LOCAL_FILE);

                $recievers = explode(',',$notificationEmails);
                if(count($recievers)>0){
                    foreach($recievers as $reciever){
                         $recieverMailArguments = [
                            'templateName' => 'Export',
                            'receiverName' => $reciever,
                            'receiverEmail' => $reciever,
                            'senderEmail' => $settings['senderEmail'],
                            'senderName' => $settings['senderName'],
                            'subject' => $settings['exportSubject'],
                            'filename' => $results['filename'],
                        ];
                        $mailService->sendMail($recieverMailArguments);
                    }
                }

            }
            else{

                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                   'Failed',
                   'Authentication Failed', // [optional] the header
                   \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                   true
                );
                $flashMessageService = $objectManager->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
            }
        }
        catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }

        return true;
    }


    /**
     * Get Latest Exported file record
     *
     * @return mixed
     */
    private function getLatestExported() {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_l10nmgr_exportdata');
        $result = $queryBuilder
            ->select('*')
            ->from('tx_l10nmgr_exportdata')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0)
            )
            ->addOrderBy('uid', 'DESC')
            ->setMaxResults(1)
            ->execute();

            return $result->fetch();
    }


}

?>