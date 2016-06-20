<?php
namespace Wwwision\Clockwork\DataSource;

use Clockwork\DataSource\DataSourceInterface;
use Clockwork\Request\Request as ClockworkRequest;
use TYPO3\Flow\Annotations as Flow;
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
     * @param ClockworkRequest $clockworkRequest
     * @return ClockworkRequest
     */
    public function resolve(ClockworkRequest $clockworkRequest)
    {
        foreach ($this->securityContext->getAuthenticationTokens() as $token) {
            if ($token->isAuthenticated()) {
                $clockworkRequest->sessionData['Authenticated tokens'][$token->getAuthenticationProviderName()][] = get_class($token);
            } else {
                $clockworkRequest->sessionData['Remaining tokens'][$token->getAuthenticationProviderName()][] = get_class($token);
            }
        }
        return $clockworkRequest;
    }
}