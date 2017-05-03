<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Gedmo\Uploadable\UploadableListener;
use EMC\FileinputBundle\Annotation\Fileinput;

class DefaultUploadableListener extends UploadableListener implements UploadableListenerInterface {
    
    /**
     * @var array
     */
    protected $extraFileInfoObjects = array();
    
    public function addExtraFileInfoObjects($entity, $owner=null, Fileinput $annotation=null) {
        $this->extraFileInfoObjects[spl_object_hash($entity)] = array(
            'owner' => $owner,
            'annotation' => $annotation
        );
    }
}
