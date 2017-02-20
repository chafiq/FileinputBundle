<?php

namespace EMC\FileinputBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @var FilterConfiguration
	 */
	protected $filterConfiguration;

	/**
	 * @var string
	 */
	protected $fileClass;

	/**
	 * ExceptionListener constructor.
	 * @param EntityManager $entityManager
	 * @param FilterConfiguration $filterConfiguration
	 * @param string $fileClass
	 */
	public function __construct(EntityManager $entityManager, FilterConfiguration $filterConfiguration, $fileClass)
	{
		$this->entityManager = $entityManager;
		$this->filterConfiguration = $filterConfiguration;
		$this->fileClass = $fileClass;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		// You get the exception object from the received event
		$exception = $event->getException();


		if ($exception instanceof NotFoundHttpException) {
			try {
				$url = null;
				$request = $event->getRequest();
				if ($request->get('_route') === 'liip_imagine_filter') {
					$filter = $this->filterConfiguration->get($request->get('filter'));
					$size = $filter['filters']['thumbnail']['size'];

					$url = $this->getFakeImageUrl($request->getScheme(), $size[0], $size[1]);

				} elseif (preg_match('/\/uploads\/.*\.(jpg|png|gif|ico|jpeg)/i', $path = $request->getRequestUri()) === 1) {
					$repository = $this->entityManager->getRepository($this->fileClass);
					$image = $repository->findOneByPath('.' . $path);

					$width = $image->getWidth();
					$height = $image->getHeight();

					if ($width > 0 && $height > 0) {
						$url = $this->getFakeImageUrl($request->getScheme(), $width, $height);
					}
				}

				if ($url !== null) {
					$response = new RedirectResponse($url);
					$event->setResponse($response);
				}
			} catch(\Exception $exception) {}
		}
	}

	/**
	 * @param string $scheme
	 * @param int $width
	 * @param int $height
	 * @param string $text
	 * @return string
	 */
	private function getFakeImageUrl($scheme, $width, $height, $text = ''){
		return sprintf('%s://fakeimg.pl/%sx%s/?text=%s', $scheme, $width, $height, $text);
	}


}