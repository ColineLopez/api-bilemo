<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\RouterInterface;


// https://symfony.com/doc/current/serializer/custom_normalizer.html

class PotentialActionSerializer 
{
    function __construct(private readonly NormalizerInterface $normalizer, private RouterInterface $router)
    {
        
    }

    public function generate(array | object $data, string | array $groups) 
    {
        $array = [];

        $objects = is_array($data) ? $data : [$data];

        foreach ($objects as $object) {
            $class = str_replace('App\Entity\\', '', get_class($object));

            $uri = '/api/'.strtolower($class).'s/'.$object->getId();

            if('Customer' == $class){
                $uri = str_replace('/api/', '/api/partners/'.$object->getPartner()->getId().'/', $uri);
            }

            $uriWithoutId = preg_replace('/\/\d+$/', '', $uri);
            $routeInfo = $this->router->match($uri);
            $routeName = $routeInfo['_route'];

            if ('partners_one' == $routeName) {
                $uri .= '/customers';
            }

            $methods = match($routeName)  {
                'customers_one' => ['GET', 'DELETE'],
                default => ['GET'],
            };
    
            $json = $this->normalizer->normalize($object, null, ['groups' => $groups]);
            $json['potential_action'] = [
                'url' => $uri,
                'methods' => $methods
                // "roles" =>
            ];
    
            $array[] = $json;
        }
        
        return $array;

    }

}
