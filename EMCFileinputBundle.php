<?php

namespace EMC\FileinputBundle;

use EMC\FileinputBundle\DependencyInjection\Compiler\UploadableRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EMCFileinputBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new UploadableRegistryPass());
    }

}
