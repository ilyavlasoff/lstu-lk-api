<?php

namespace App\ArgumentResolver;

use App\Model\Mapping\Attachment;
use App\Model\Mapping\BinaryFile;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class FileDataValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return BinaryFile::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if($request->headers->get('Content-Type') === 'application/json') {
            /** @var Attachment $attachmentData */
            $attachmentData = $this->serializer->deserialize(
                $request->getContent(),
                Attachment::class,
                'json'
            );
            $fileContent = $attachmentData->toBinary();
        } else {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('attachment');
            $fileContent = new BinaryFile();
            $fileContent->setFilename($uploadedFile->getClientOriginalName());
            $fileContent->setFileContent(file_get_contents($fileContent->getFilename()));
        }

        yield $fileContent;
    }
}