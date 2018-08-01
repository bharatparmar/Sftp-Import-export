<?php

namespace Dpool\Website\Service;

class MailService {

	/**
	 * return mail body
	 *
	 * @param mixed $arguments
	 * @return string
	 */
	public function sendMail($arguments = array()) {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
		$emailView = $objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

		// Get Configuration
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
		$extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

		// Get Template Path
		if(empty($extbaseFrameworkConfiguration['view']['templateRootPath'])){
			$extbaseFrameworkConfiguration['view']['templateRootPath'] = $extbaseFrameworkConfiguration['plugin.']['tx_website_website.']['view.']['templateRootPath'];
		}

		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);

		$templatePathAndFilename = $templateRootPath . '/Emails/' . $arguments['templateName'] . '.html';

		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		$emailView->assignMultiple($arguments);
		$emailBody = $emailView->render();



		/** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
		$message = $objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$message->setTo(array($arguments['receiverEmail'] => $arguments['receiverName']))
			  ->setFrom(array($arguments['senderEmail'] => $arguments['senderName']))
			  ->setSubject($arguments['subject']);

		// Possible attachments here
		//foreach ($attachments as $attachment) {
		//	$message->attach(\Swift_Attachment::fromPath($attachment));
		//}

		// HTML Email
		$message->setBody($emailBody, 'text/html');

		$message->send();
		return $message->isSent();
	}
}