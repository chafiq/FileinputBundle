<?php

namespace EMC\FileInputBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract File Entity
 * @ORM\MappedSuperclass
 */
abstract class File
{
    public static $extensions = array(
        'txt' => "text/plain",
        'csv' => "text/plain",
        'png' => "image/png",
        'jpg' => "image/jpeg",
        'gif' => "image/gif",
        'bmp' => "image/x-ms-bmp",
        'svg' => "image/svg+xml",
        'eps' => "application/postscript",
        'ai' => "application/postscript",
        'ps' => "application/postscript",
        'psd'=> "application/octet-stream",
        'pdf' => "application/pdf",
        'zip' => "application/zip",
        'gz'  => "application/x-gzip",
        
        /* Open Office */
        'odt' => "application/vnd.oasis.opendocument.text",
        'ods' => "application/vnd.oasis.opendocument.spreadsheet",
        'odp' => "application/vnd.oasis.opendocument.presentation",
        
        /* Microsoft Office */
        "doc" => "application/msword",
        "dot" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "dotx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
        "docm" => "application/vnd.ms-word.document.macroEnabled.12",
        "dotm" => "application/vnd.ms-word.template.macroEnabled.12",
        "xls" => "application/vnd.ms-excel",
        "xlt" => "application/vnd.ms-excel",
        "xla" => "application/vnd.ms-excel",
        "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "xltx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
        "xlsm" => "application/vnd.ms-excel.sheet.macroEnabled.12",
        "xltm" => "application/vnd.ms-excel.template.macroEnabled.12",
        "xlam" => "application/vnd.ms-excel.addin.macroEnabled.12",
        "xlsb" => "application/vnd.ms-excel.sheet.binary.macroEnabled.12",
        "ppt" => "application/vnd.ms-powerpoint",
        "pot" => "application/vnd.ms-powerpoint",
        "pps" => "application/vnd.ms-powerpoint",
        "ppa" => "application/vnd.ms-powerpoint",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "potx" => "application/vnd.openxmlformats-officedocument.presentationml.template",
        "ppsx" => "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
        "ppam" => "application/vnd.ms-powerpoint.addin.macroEnabled.12",
        "pptm" => "application/vnd.ms-powerpoint.presentation.macroEnabled.12",
        "potm" => "application/vnd.ms-powerpoint.presentation.macroEnabled.12",
        "ppsm" => "application/vnd.ms-powerpoint.slideshow.macroEnabled.12"
    );
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="path", type="string")
     * @Gedmo\UploadableFilePath
     * @var string
     */
    protected $path;

    /**
     * @ORM\Column(name="name", type="string")
     * @Asser\NotBlank
     * @var string
     */
    protected $name;
    
    /**
     * @ORM\Column(name="mime_type", type="string")
     * @Gedmo\UploadableFileMimeType
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="size", type="decimal")
     * @Gedmo\UploadableFileSize
     * @var integer
     */
    protected $size;
    
    /**
     * @var string
     */
    private $_path;
    
    public function __clone(){
        $this->id = null;
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType)
    {
        if (!isset(self::$extensions[$mimeType])) {
            throw new \UnexpectedValueException('Unkown mime type "' . $mimeType . '"');
        }
        
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function preUpdate() {
        if ( $this->path === null ) {
            $this->path = $this->_path;
        }
        
        if ( $this->name === null ) {
            $this->name = $this->path;
        }
    }
    
    /**
     * @ORM\PostLoad()
     */
    public function postLoad() {
        $this->_path = $this->path;
    }
    
    public function getExtension() {
        if (strlen($this->path) === 0){
            return null;
        }
        if (($extension = strstr($this->path, '.')) === FALSE) {
            throw new \UnexpectedValueException(sprintf('File path "%s" is not valid !.', $this->path));
        }
        return $extension;
    }
    
    public function getHumanReadableSize($dec = 2) {
        $sizes   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->size) - 1) / 3);
        return sprintf("%.{$dec}f %s", $this->size / pow(1024, $factor), @$sizes[$factor]);
    }
    
    public function isImage() {
        return strstr($this->mimeType, '/', true) === 'image';
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getUrl()
    {
        return substr($this->path, 1);
    }
    
    public function getData() {
        return array(
            'id'  => $this->getId(),
            'path' => $this->getUrl(),
            'mimeType' => $this->getMimeType(),
            'size' => $this->getHumanReadableSize(),
            'extension' => $this->getExtension()
        );
    }
}
