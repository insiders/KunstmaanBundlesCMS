<?php

namespace Kunstmaan\FormBundle\Entity\FormSubmissionFieldTypes;

use Kunstmaan\FormBundle\Entity\FormSubmissionField;

use Gedmo\Sluggable\Util\Urlizer;

use Kunstmaan\FormBundle\Form\FileFormSubmissionType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * The ChoiceFormSubmissionField can be used to store files to a FormSubmission
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="kuma_file_form_submission_fields")
 */
class FileFormSubmissionField extends FormSubmissionField
{

    /**
     * The file name
     *
     * @ORM\Column(name="ffsf_value", type="string")
     */
    protected $fileName;

    /**
     * The url
     *
     * @ORM\Column(name="ffsf_url", type="string")
     */
    protected $url;

    /**
     * Uuid
     *
     * @ORM\Column(name="ffsf_uuid", type="string", unique=true, length=255)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $uuid;

    /**
     * Non-persistent storage of upload file
     *
     * @Assert\File(maxSize="6000000")
     * @var $file UploadedFile
     */
    public $file;

    /**
     * A string representation of the current value
     *
     * @return string
     */
    public function __toString()
    {
        return !empty($this->fileName) ? $this->fileName : "";
    }

    /**
     * Checks if a file has been uploaded
     *
     * @return bool
     */
    public function isNull()
    {
        return null === $this->file && empty($this->fileName);
    }

    /**
     * Move the file to the given uploadDir and save the filename
     *
     * @param string $uploadDir
     * @param string $webDir
     */
    public function upload($uploadDir, $webDir)
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        if (null === $this->uuid) {
            $this->uuid = uniqid();
        }

        // sanitize filename for security
        $safeFileName = $this->getSafeFileName($this->file);

        // move takes the target directory and then the target filename to move to
        $uploadDir .= '/' . $this->uuid;
        $this->file->move($uploadDir, $safeFileName);

        // set the path property to the filename where you'ved saved the file
        $this->fileName = $safeFileName;
        $this->url = $webDir . $this->uuid . '/' . $safeFileName;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * This function will be triggered if the form was successfully posted.
     *
     * @param Form                 $form        the Form
     * @param FormBuilderInterface $formBuilder the FormBuilder
     * @param Request              $request     the Request
     * @param ContainerInterface   $container   the Container
     */
    public function onValidPost(Form $form, FormBuilderInterface $formBuilder, Request $request, ContainerInterface $container)
    {
        $uploadDir = $container->getParameter('form_submission_rootdir');
        $webDir = $container->getParameter('form_submission_webdir');
        $this->upload($uploadDir, $webDir);
    }

    /**
     * Create a safe file name for the uploaded file, so that it can be saved safely on the disk.
     *
     * @return string
     */
    public function getSafeFileName()
    {
        $fileExtension = $this->file->getClientOriginalExtension();
        $mimeTypeExtension = $this->file->guessExtension();
        $newExtension = !empty($mimeTypeExtension) ? $mimeTypeExtension : $fileExtension;

        $baseName = !empty($fileExtension) ? basename($this->file->getClientOriginalName(), $fileExtension) : $this->file->getClientOriginalName();
        $safeBaseName = Urlizer::urlize($baseName);

        return $safeBaseName . (!empty($newExtension) ? '.' . $newExtension : '');
    }

    /**
     * Set the filename for the uploaded file
     *
     * @param string $fileName
     *
     * @return FileFormSubmissionField
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Returns the filename of the uploaded file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the url for the uploaded file
     *
     * @param string $url
     *
     * @return FileFormSubmissionField
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Returns the url of the uploaded file
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return FileFormSubmissionField
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Return the template for this field
     *
     * @return string
     */
    public function getSubmissionTemplate()
    {
        return "KunstmaanFormBundle:FileUploadPagePart:submission.html.twig";
    }

    /**
     * Returns the default form type for this FormSubmissionField
     *
     * @return FileFormSubmissionType
     */
    public function getDefaultAdminType()
    {
        return new FileFormSubmissionType();
    }

}
