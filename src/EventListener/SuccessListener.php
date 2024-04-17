<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;


final class SuccessListener
{
    private $excludedRoutes = ['api/login'];

    public function __construct(private \Symfony\Component\HttpFoundation\RequestStack $requestStack)
    {
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        
        $response = $event->getResponse();
        $request = $this->requestStack->getCurrentRequest();
        
        if(('200' === $response->getStatusCode() || '201' === $response->getStatusCode() ) 
        && !in_array($request->attributes->get('_route'), $this->excludedRoutes)) {
        
            $data = json_decode($response->getContent());
            $data['status'] = $response->getStatusCode();
            $data['message'] = null;
            
            $event->setResponse(new JsonResponse($data));
        }

    }
}
