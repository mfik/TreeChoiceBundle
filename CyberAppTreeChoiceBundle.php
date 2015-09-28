<?php

namespace CyberApp\TreeChoiceBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use CyberApp\TreeChoiceBundle\DependencyInjection\Compiler\BundleCompilerPass;

class CyberAppTreeChoiceBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BundleCompilerPass());

        parent::build($container);
    }
}
