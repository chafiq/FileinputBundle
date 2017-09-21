<?php

namespace EMC\FileinputBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use EMC\FileinputBundle\Driver\DriverInterface;

/**
 * Abstract File Entity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class File implements FileInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\UploadableFilePath
     * @var string
     */
    protected $path;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileMimeType
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     * @var integer
     */
    protected $size;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $height;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $driver;

    /**
     * @var \EMC\FileinputBundle\Driver\DriverInterface
     */
    private $_driver;
    
    public function __toString() {
        return $this->getUrl();
    }

    public function __clone() {
        $this->id = null;

        if ($this->path instanceof UploadedFile) {
            $this->path = clone $this->path;
        } elseif(is_string($this->path) && file_exists($this->path)) {
            $pathname = sprintf('%s/file-clone-%s.%s', sys_get_temp_dir(), uniqid(), $this->getExtension());
            if (!copy($this->path, $pathname)) {
                throw new \RuntimeException(sprintf('Unable to copy file from %s to %s', $this->path, $pathname));
            }
            $this->path = new UploadedFile($pathname, $pathname, $this->mimeType, $this->size, null, true);
        } else {
            $this->path = null;
        }
    }

    public function __sleep()
    {
        if ($this->path instanceof UploadedFile) {
            try {
                $filename = uniqid('emc_filesess_');
                $pathname = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                $uploadedFile = $this->path;

                $this->path = array(
                    'path' => $pathname,
                    'clientOriginalName' => $uploadedFile->getClientOriginalName(),
                    'mimeType' => $uploadedFile->getMimeType(),
                    'size' => $uploadedFile->getSize(),
                    'error' => $uploadedFile->getError()
                );

                $uploadedFile->move(sys_get_temp_dir(), $filename);
            } catch(\Exception $exception) {
                $this->path = null;
            }
        }

        $ref   = new \ReflectionClass(static::class);
        $props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);

        $serialize_fields = array();

        foreach ($props as $prop) {
            $serialize_fields[] = $prop->name;
        }

        return $serialize_fields;
    }

    function __wakeup()
    {
        if (is_array($this->path)) {
            try {
                $this->path = new UploadedFile(
                    $this->path['path'],
                    $this->path['clientOriginalName'],
                    $this->path['mimeType'],
                    $this->path['size'],
                    $this->path['error'],
                    true
                );
            } catch(\Exception $exception) {
                $this->path = null;
            }
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set name
     *
     * @param string $name
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
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType) {

        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return File
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getExtension() {
        $mimeTypeGuesser = new MimeTypeExtensionGuesser;
        return $mimeTypeGuesser->guess($this->mimeType);
    }

    public function getHumanReadableSize($dec = 2) {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->size) - 1) / 3);
        return sprintf("%.{$dec}f %s", $this->size / pow(1024, $factor), @$sizes[$factor]);
    }

    public function isImage() {
        return substr($this->mimeType, 0, 6) === 'image/';
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getUrl() {
        return $this->_driver ? $this->_driver->getUrl($this->path) : substr($this->path, 1);
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getThumbnail() {
        return $this->_driver ? $this->_driver->getThumbnail($this->path) : null;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function render() {
        return $this->_driver ? $this->_driver->render($this->path) : null;
    }

    public function getMetadata() {
        return array(
            'id'  => $this->getId(),
            'name' => $this->getName(),
            'path' => $this->getUrl(),
            'mimeType' => $this->getMimeType(),
            'size' => $this->getHumanReadableSize(),
            'extension' => $this->getExtension()
        );
    }

    /**
     * @return string
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * @param string $driver
     * @return FileInterface
     */
    function setDriver($driver, DriverInterface $_driver = null) {
        $this->driver = $driver;
        $this->_driver = $_driver;
        return $this;
    }

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist(){
		try {
			if ($this->path instanceof UploadedFile && substr($this->path->getMimeType(), 0, 6) === 'image/'){
				if ($info = getimagesize($this->path->getPathname())){
					list($this->width, $this->height) = $info;
				}
			}
		} catch (\Exception $exception){}
	}

	/**
	 * @param int $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @param int $height
	 * @return File
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}


	public static function createFromBase64($base64, $name = null) {

        if (!is_string($base64) || !preg_match('/^data:(image\/([a-z]+));base64,(.*)$/', $base64, $matches)) {
            throw new \InvalidArgumentException('Base64 string format required');
        }

        $mimeType = $matches[1];
        $extension = $matches[2];
        $data = $matches[3];

        $path = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid('file-base-64'), $extension);
        $fp = fopen($path, 'wb');
        fwrite($fp, base64_decode($data));
        fclose($fp);

        $uploadedFile = new UploadedFile($path, $path, $mimeType, filesize($path));

        /* @var $file FileInterface */
        $file = new static;
        $file->setName($name);
        $file->setPath($uploadedFile);

        return $file;
    }


}
