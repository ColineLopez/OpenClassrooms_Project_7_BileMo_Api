<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\RouterInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;


class PotentialActionSerializer 
{
    public function __construct(private readonly NormalizerInterface $normalizer, private RouterInterface $router)
    {   
    }

    public function generate(array | object $data, string | array $groups, ?SerializationContext $context = null) 
    {
        $array = [];

        $objects = is_array($data) ? $data : [$data];

        // var_dump($objects);die;

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

            // Vérifier si le contexte de sérialisation est défini et s'il contient une version
            if ($context instanceof SerializationContext && $context->hasAttribute('version')) {
                $version = $context->getAttribute('version');
                // var_dump($version);die;
                // Logique conditionnelle pour inclure les attributs en fonction de la version
                if ($version === '2.0') {
                    $json = $this->includeAttributesSinceVersion($json, $object);
                    var_dump($json);die;
                    // Inclure les attributs pour la version 1.0
                    // Par exemple, ne rien faire ici car les attributs seront déjà inclus selon les groupes
                } 
            }


            foreach($methods as $method){
                $json['potential_action'][] = [
                    'url' => $uri,
                    'method' => $method
                ];
            }
            
    
            $array[] = $json;
        }
        
        return $array;

    }

    private function includeAttributesSinceVersion(array $json, object $object): array
    {
        $includedAttributes = [];

        // Utilisation de Doctrine Annotations pour lire les annotations
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($object);
        $properties = $reflectionClass->getProperties();

        // var_dump($annotationReader);
        // var_dump($reflectionClass);
        // var_dump($properties);die;
        // Parcourir les propriétés de l'objet
        foreach ($properties as $property) {
            $annotations = $annotationReader->getPropertyAnnotations($property);
            var_dump($property);
            var_dump($annotations);
            foreach ($annotations as $annotation) {
                // Vérifier si l'annotation est "#[since(2.0)]"
                if ($annotation instanceof \JMS\Serializer\Annotation\Since) {
                    // Ajouter le nom de la propriété à inclure
                    $includedAttributes[] = $property->getName();
                }
            }
        }

        die;
        // Retourner uniquement les attributs avec l'annotation "#[since(2.0)]"
        return array_intersect_key($json, array_flip($includedAttributes));
        // return $json;
    }

}
