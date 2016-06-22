<?php
namespace Wwwision\Clockwork\DataSource;

use Clockwork\DataSource\DataSourceInterface;
use Clockwork\Request\Request as ClockworkRequest;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Security\Context;

/**
 * Clockwork DataSource that adds authentication details to the requests "sessionData"
 */
class AuthenticationDataSource implements DataSourceInterface
{

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param ClockworkRequest $clockworkRequest
     * @return ClockworkRequest
     */
    public function resolve(ClockworkRequest $clockworkRequest)
    {
        $authenticationTokens = [];
        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            if ($token->isAuthenticated()) {
                $authenticationTokens[$token->getAuthenticationProviderName()][get_class($token)] = 'AUTHENTICATED';
            } else {
                $authenticationTokens[$token->getAuthenticationProviderName()][get_class($token)] = 'not authenticated';
            }
        }
        $authenticatedAccount = $this->securityContext->getAccount();
        if ($authenticatedAccount !== null) {
            $authenticatedAccountIdentifier = $authenticatedAccount->getAccountIdentifier() . ' (' . $this->persistenceManager->getIdentifierByObject($authenticatedAccount) . ')';
        } else {
            $authenticatedAccountIdentifier = '-';
        }
        $clockworkRequest->sessionData['Flow Authentication'] = [
            'Authentication tokens' => $authenticationTokens,
            'Account' => $authenticatedAccountIdentifier,
            'Roles' => array_keys($this->securityContext->getRoles()),
        ];
        return $clockworkRequest;
    }
}