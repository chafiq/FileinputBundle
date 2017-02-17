<?php

namespace EMC\FileinputBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
	/**
	 * @var FilterConfiguration
	 */
	protected $filterConfiguration;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @param FilterConfiguration $filterConfiguration
	 */
	public function setFilterConfiguration(FilterConfiguration $filterConfiguration)
	{
		$this->filterConfiguration = $filterConfiguration;
	}

	/**
	 * @param EntityManager $em
	 * @return ExceptionListener
	 */
	public function setEm(EntityManager $em)
	{
		$this->em = $em;
		return $this;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		// You get the exception object from the received event
		$exception = $event->getException();


		if ($exception instanceof NotFoundHttpException) {

			$request = $event->getRequest();
			if ($request->get('_route') === 'liip_imagine_filter'){
				$filter = $this->filterConfiguration->get($request->get('filter'));
				$size = $filter['filters']['thumbnail']['size'];

				$url = $this->fakeImageUrl($request->getScheme(), $size[0], $size[1]);

			}
			elseif (preg_match('/\/uploads\/.*\.(jpg|png|gif|ico|jpeg)/i',$path = $request->getRequestUri()) === 1){
				$repository = $this->em->getRepository('AppBundle:File');
				$image = $repository->findOneByPath('.'.$path);

				$width = $image->getWidth();
				$height = $image->getHeight();

				if($width > 0 && $height > 0){
					$url = $this->fakeImageUrl($request->getScheme(), $width, $height);
				}
			}

			if(isset($url)){
				$response = new RedirectResponse($url);
				$event->setResponse($response);
			}
		}
	}

	/**
	 * @param string $scheme
	 * @param int $width
	 * @param int $height
	 * @param string $text
	 * @return string
	 */
	private function fakeImageUrl($scheme = 'http', $width, $height, $text = ''){
		return sprintf('%s://fakeimg.pl/%sx%s/?text=%s', $scheme, $width, $height, $text);
	}


}