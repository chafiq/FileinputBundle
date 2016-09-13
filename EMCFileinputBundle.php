<?php

namespace EMC\FileinputBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use EMC\FileinputBundle\DependencyInjection\Compiler\UploadableRegistryPass;

class EMCFileinputBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new UploadableRegistryPass());
    }

}
