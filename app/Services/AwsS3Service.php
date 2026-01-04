<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Exception;

class AwsS3Service 
{
    private S3Client $s3;
    private string $bucket;
    private string $region;

    public function __construct()
    {
        $this->bucket = config('aws.bucket_name');
        $this->region = config('aws.default_region');

        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => [
                'key'    => config('aws.access_key'),
                'secret' => config('aws.secret_access_key')
            ]
        ]);
    }

    public function uploadImage(string $body, string $key, string $mime): string
    {
        try {
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => $body,
                'ContentType' => $mime,
            ]);

            return $key;
        } catch (AwsException $ex) {
            throw new Exception("Erro ao enviar para S3: {$ex->getAwsErrorMessage()}");
        }
    }

    public function getPublicUrl(string $key): string
    {        
        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
    }

    public function getSignetUrl(string $key, int $minutes = 10): string
    {
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $key
        ]);

        $request = $this->s3->createPresignedRequest($cmd, "+{$minutes} minutes");

        return (string) $request->getUri();
    }

    public function deleteImage(string $key): bool
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            return true;
        } catch (AwsException $ex) {
            throw new Exception("Erro ao deletar imagem: {$ex->getAwsErrorMessage()}");
        }
    }
}