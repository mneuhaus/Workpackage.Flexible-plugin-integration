<?php
namespace TYPO3\NeosDemoTypo3Org\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "NeosDemoTypo3Org".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Controller that handles the creation of temporary Accounts
 *
 */
class RegistrationController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @return string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function indexAction() {
	}

	/**
	 * Displays a form that creates a temporary account
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function newAccountAction() {
		$number = (time() - 1302876012);
		$registration = new \TYPO3\NeosDemoTypo3Org\Domain\Model\Registration();
		$registration->setFirstName('John');
		$registration->setLastName('Doe');
		$registration->setUsername('demo' . $number);
		$registration->setPassword('demo');

		$this->view->assign('registration', $registration);
	}

	/**
	 * Action for creating a temporary account
	 *
	 * @param \TYPO3\NeosDemoTypo3Org\Domain\Model\Registration $registration
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function createAccountAction(\TYPO3\NeosDemoTypo3Org\Domain\Model\Registration $registration) {
		$accountIdentifier = $registration->getUsername();
		$existingAccount = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, 'Typo3BackendProvider');
		if ($existingAccount !== NULL) {
			$this->addFlashMessage('An account with the username "' . $accountIdentifier . '" already exists.');
			$this->forward('newAccount');
		}

		$this->createTemporaryAccount($accountIdentifier, $registration->getPassword(), $registration->getFirstName(), $registration->getLastName());

		$uriBuilder = new \TYPO3\Flow\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($this->request->getParentRequest());
		$redirectUri = $uriBuilder
			->setCreateAbsoluteUri(TRUE)
			->uriFor('index', array('username' => $accountIdentifier), 'Login', 'TYPO3.TYPO3');
		$this->redirectToUri($redirectUri);
	}

	/**
	 * Creates a temporary account
	 *
	 * @param string $accountIdentifier
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @return \TYPO3\Flow\Security\Account
	 */
	protected function createTemporaryAccount($accountIdentifier, $password, $firstName, $lastName) {
		if (strlen($firstName) === 0 && strlen($lastName) === 0) {
			$firstName = 'Santa';
			$lastName = 'Claus';
		}

		$user = new \TYPO3\Neos\Domain\Model\User();
		$user->setName(new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName));
		$user->getPreferences()->set('context.workspace', 'user-' . $accountIdentifier);
		$this->partyRepository->add($user);

		$account = $this->accountFactory->createAccountWithPassword($accountIdentifier, $password, array('Administrator'), 'Typo3BackendProvider');
		$account->setParty($user);
		$account->setExpirationDate(new \DateTime('+1 week'));

		$this->accountRepository->add($account);
	}

	/**
	 * Confirmation for createAccountAction
	 *
	 * @param string $username Identifier of the account that was created
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function createAccountConfirmationAction($username) {
		$this->view->assign('username', $username);
	}

	/**
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}
}
?>
