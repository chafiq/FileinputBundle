<?php

namespace EMC\FileinputBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use EMC\FileinputBundle\Form\DataTransformer\FileDataTransformer;
use EMC\FileinputBundle\Form\DataTransformer\MultipleFileDataTransformer;
use EMC\FileinputBundle\Entity\FileInterface;

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
    
    function setUploadableManager(UploadableManager $uploadableManager) {
        $this->uploadableManager = $uploadableManager;
    }
    
    function setFileClass($fileClass) {
        $this->fileClass = $fileClass;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $files = array();
        
        if ($options['multiple']) {
            if (is_array($view->vars['value'])) {
                foreach($view->vars['value']['_path'] as $file) {
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
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('path', 'file', array(
            'data_class' => null,
            'required' => false,
            'multiple' => $options['multiple'],
            'mapped' => true,
            'attr' => array(
                'accept' => $options['accept']
            )
        ));
        
        $builder->add('_delete', 'hidden', array(
            'required' => false
        ));
        
        $modelDataTransformerClass = $options['multiple'] ? MultipleFileDataTransformer::class : FileDataTransformer::class;
        $builder->addModelTransformer(new $modelDataTransformerClass($this->uploadableManager, $this->fileClass));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => null,
            'multiple'  => false,
            'accept' => implode(',', array())
        ));
    }

    public function getName()
    {
        return 'fileinput';
    }
}