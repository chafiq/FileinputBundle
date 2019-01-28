<?php

namespace EMC\FileinputBundle\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use EMC\FileinputBundle\Annotation\Fileinput;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Form\DataTransformer\DataTransformerInterface;
use EMC\FileinputBundle\Form\DataTransformer\FileDataTransformer;
use EMC\FileinputBundle\Form\DataTransformer\MultipleFileDataTransformer;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileinputType extends AbstractType
{
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    function setUploadableManager(UploadableManager $uploadableManager)
    {
        $this->uploadableManager = $uploadableManager;
    }

    function setFileClass($fileClass)
    {
        $this->fileClass = $fileClass;
    }

    function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
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

        usort($files, function($file1, $file2){
            if ($file1['position'] === $file2['position']) {
                return 0;
            }
            return $file1['position'] > $file2['position'] ? 1 : -1;
        });

        $view->vars['files'] = $files;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('path', FileType::class, [
            'data_class'             => null,
            'required'               => false,
            'multiple'               => $options['multiple'],
            'mapped'                 => true,
            'attr'                   => array(
                'accept'             => $options['accept'],
                'data-max-file-size' => $options['max_size'],
                'data-drop-zone'     => $options['drop_zone'],
            ),
            'post_max_size_message'  => $options['max_size'],
        ]);

        $builder->add('delete', HiddenType::class, ['required' => false]);
        $builder->add('position', HiddenType::class, ['required' => false]);

        if ($options['legend']) {
            $builder->add('name', HiddenType::class, ['required' => false]);
        }

        $modelDataTransformerClass = $options['multiple'] ? MultipleFileDataTransformer::class : FileDataTransformer::class;
        $dataTransformer = new $modelDataTransformerClass($this->uploadableManager, $this->fileClass);
        $builder->addModelTransformer($dataTransformer);
        $this->dataTransformer = $dataTransformer;

        $builder->addEventListener(FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($dataTransformer) {
                $form = $event->getForm();
                if ($form->getParent() === null || !$form->getConfig()->getMapped()) {
                    return;
                }

                $dataClass = $form->getParent()->getConfig()->getDataClass();
                if ($dataClass === null) {
                    return;
                }

                $reflectionProperty = new \ReflectionProperty($dataClass, $form->getName());

                // Prepare doctrine annotation reader
                $reader = new AnnotationReader();
                /* @var $annotation Fileinput */
                if ($annotation = $reader->getPropertyAnnotation($reflectionProperty, Fileinput::class)) {
                    $dataTransformer->setAnnotation($annotation);
                    $dataTransformer->setOwner($form->getParent()->getData());
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class'     => null,
            'multiple'       => false,
            'accept'         => '',
            'max_size'       => 100000,
            'error_bubbling' => false,
            'legend'         => false,
            'drop_zone'      => false,
        ]);
        $resolver->setAllowedTypes('legend', 'boolean');
        $resolver->setAllowedTypes('drop_zone', 'boolean');
    }

    public function getBlockPrefix()
    {
        return 'fileinput';
    }

}
