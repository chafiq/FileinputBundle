<?php

namespace EMC\FileinputBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EMC\FileinputBundle\Form\DataTransformer\FileDataTransformer;
use EMC\FileinputBundle\Form\DataTransformer\MultipleFileDataTransformer;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;
use EMC\FileinputBundle\Form\DataTransformer\DataTransformerInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\Common\Annotations\AnnotationReader;
use EMC\FileinputBundle\Annotation\Fileinput;

class FileinputType extends AbstractType {

    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * @var string
     */
    private $fileClass;
    
    /**
     * @var DataTransformerInterface 
     */
    private $dataTransformer;

    function setUploadableManager(UploadableManager $uploadableManager) {
        $this->uploadableManager = $uploadableManager;
    }

    function setFileClass($fileClass) {
        $this->fileClass = $fileClass;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
        $files = array();

        if ($options['multiple']) {
            if (is_array($view->vars['value'])) {
                foreach ($view->vars['value']['_path'] as $file) {
                    $files[] = $file->getMetadata();
                }
            }
        } else {
            if (is_array($view->vars['value']) && $view->vars['value']['_path'] instanceof FileInterface) {
                $files = array($view->vars['value']['_path']->getMetadata());
            }
        }
        $view->vars['files'] = $files;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('_delete', 'hidden', array(
            'required' => false
        ));

        $builder->add('path', 'file', array(
            'data_class' => null,
            'required' => false,
            'multiple' => $options['multiple'],
            'mapped' => true,
            'attr' => array(
                'accept' => $options['accept'],
                'data-max-file-size' => $options['max_size']
            ),
            'post_max_size_message' => $options['max_size']
        ));
        
        
        if ($options['legend']) {
            $builder->add('_name', 'hidden');
        }

        $modelDataTransformerClass = $options['multiple'] ? MultipleFileDataTransformer::class : FileDataTransformer::class;
        $dataTransformer = new $modelDataTransformerClass($this->uploadableManager, $this->fileClass);
        $builder->addModelTransformer($dataTransformer);
        $this->dataTransformer = $dataTransformer;
        
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use ($dataTransformer){
            $form = $event->getForm();
            
            $dataClass = $form->getParent()->getConfig()->getDataClass();
            if ($dataClass === null) {
            	return;
            }

            $property = $form->getName();
            
            $reflectionProperty = new \ReflectionProperty($dataClass, $property);

            // Prepare doctrine annotation reader
            $reader = new AnnotationReader();
            
            /* @var $annotation Fileinput */
            if ($annotation = $reader->getPropertyAnnotation($reflectionProperty, Fileinput::class)) {
                $dataTransformer->setAnnotation($annotation);
                $dataTransformer->setOwner($form->getParent()->getData());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => null,
            'multiple' => false,
            'accept' => '',
            'max_size' => 100000,
            'error_bubbling' => false,
            'legend' => false
        ));
        $resolver->setAllowedTypes(array(
            'legend' => 'boolean'
        ));
    }

    public function getName() {
        return 'fileinput';
    }

}
