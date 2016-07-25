<?php

namespace EMC\FileinputBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use EMC\FileinputBundle\Form\DataTransformer\FileInputDataTransformer;
use EMC\FileinputBundle\Entity\File;

class FileinputType extends AbstractType
{
    /**
     *
     * @var UploadableManager
     */
    private $uploadableManager;
    
    function setUploadableManager(UploadableManager $uploadableManager) {
        $this->uploadableManager = $uploadableManager;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $files = array();
        if ($view->vars['value'] instanceof \ArrayAccess) {
            $files = $view->vars['value']->map(function(File $file=null){
                return $file === null ?: $file->getData();
            })->toArray();
        } elseif (is_array($view->vars['value']) ) {
            $files = array_map(function(File $file){
                return $file === null ?: $file->getData();
            }, $view->vars['value']);
        }
        
        $view->vars['value'] = array_filter($files);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('path', 'file', array(
            'data_class' => null    ,
            'required' => false,
            'multiple' => $options['multiple'],
            'mapped' => true
        ));
        
        $builder->add('deletedIds', 'hidden', array(
            'required' => false
        ));
        
        $builder->addModelTransformer(new FileInputDataTransformer($this->uploadableManager, $options['multiple']));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => null,
            'multiple'  => false,
            'attr' => array(
                'class' => 'fileinput',
                'accept' => implode(',', File::$extensions)
            )
        ));
    }

    public function getName()
    {
        return 'fileinput';
    }
}