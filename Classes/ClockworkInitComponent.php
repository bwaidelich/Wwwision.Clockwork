<?php
namespace Wwwision\Clockwork;

use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\DataSource\PhpDataSource;
use Clockwork\Storage\FileStorage;
use Doctrine\Common\Persistence\ObjectManager as DoctrineEntityManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Utility\Environment;
use TYPO3\Flow\Utility\Files;
use Wwwision\Clockwork\DataSource\AuthenticationDataSource;

/**
 * HTTP Component that initializes the clockwork debugging and that renders the JSON response for /__clockwork/<id> requests
 */
class ClockworkInitComponent implements ComponentInterface
{

    /**
     * @Flow\Inject
     * @var DoctrineEntityManager
     */
    protected $doctrineEntityManager;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $clockwork = new Clockwork();
        $storage = new FileStorage(Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), 'Clockwork']));
        $clockwork->setStorage($storage);

        $clockwork->addDataSource(new PhpDataSource());
        /** @noinspection PhpParamsInspection */
        $clockwork->addDataSource(new DoctrineDataSource($this->doctrineEntityManager));
        $clockwork->addDataSource(new AuthenticationDataSource());

        $httpRequest = $componentContext->getHttpRequest();
        $requestPathSegments = explode('/', $httpRequest->getRelativePath());
        if ($requestPathSegments[0] === '__clockwork' && isset($requestPathSegments[1])) {
            $requestId = $requestPathSegments[1];
            $clockworkRequest = $storage->retrieve($requestId);
            echo $clockworkRequest->toJson();
            exit;
        }
        $componentContext->setParameter(ClockworkResolveComponent::class, 'clockwork', $clockwork);
    }
}