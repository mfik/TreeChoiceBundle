<?php

namespace CyberApp\TreeChoiceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class BundleCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // get resources value
        $resources   = $container->getParameter('twig.form.resources');
        $resources[] = 'CyberAppTreeChoiceBundle:Form:bootstrap3.html.twig';

        // update resources value
        $container->setParameter('twig.form.resources', $resources);
    }
}
