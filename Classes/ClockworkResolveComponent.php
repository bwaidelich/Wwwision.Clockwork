<?php
namespace Wwwision\Clockwork;

use Clockwork\Clockwork;
use Doctrine\Common\Persistence\ObjectManager as DoctrineEntityManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;

/**
 * HTTP Component that finalizes the clockwork debugging and adds the X-Clockwork-* headers to the current HTTP response
 */
class ClockworkResolveComponent implements ComponentInterface
{

    /**
     * @Flow\Inject
     * @var DoctrineEntityManager
     */
    protected $doctrineEntityManager;

    /**
     * @param ComponentContext $componentContext
     * @return void
     * @api
     */
    public function handle(ComponentContext $componentContext)
    {
        /** @var Clockwork $clockwork */
        $clockwork = $componentContext->getParameter(ClockworkResolveComponent::class, 'clockwork');
        if ($clockwork === null) {
            return;
        }

        $clockwork->resolveRequest();
        $clockwork->storeRequest();

        $httpResponse = $componentContext->getHttpResponse();
        $httpResponse->setHeader('X-Clockwork-Version', Clockwork::VERSION);
        $httpResponse->setHeader('X-Clockwork-Id', $clockwork->getRequest()->id);
    }
}