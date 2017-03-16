<?php
/**
 * Created by PhpStorm.
 * User: zhou
 * Date: 3/16/17
 * Time: 11:00 AM
 */

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3Handler{

    private $s3;
    private $bucket;
    private $folder;

    /**
     * You have to define ACCESS KEY and SECRET KEY as const
     * TODO: change it use environment variable or read from file which will only exists on server
     */
    public function __construct()
    {
        $this->s3 = S3Client::factory(['credentials' => ['key' => AWS_ACCESS_KEY_ID, 'secret' => AWS_SECRET_ACCESS_KEY]]);
        $this->bucket = S3_BUCKET;
    }


    /**
     * $file has to be $_FILE[$file]
     * @param $file
     * @return bool
     */
    public function upload($file)
    {
        $key = uniqid() . "_" . basename($file['name']);

        // replace (), - and space
        $key = str_replace(' ', '_', $key);
        $key = str_replace('-', '_', $key);
        $key = str_replace('(', '_', $key);
        $key = str_replace(')', '_', $key);
        try {
            $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => fopen($file['tmp_name'], 'r'),
                'ACL'    => 'public-read',
            ]);

            return $key;
        } catch (S3Exception $e) {
            // TODO: deal with s3 upload error notification
            return false;
        }
    }

    public function delete($key)
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            return true;
        } catch (S3Exception $e) {
            // TODO: deal with s3 delete error notification
            return false;
        }
    }


    /**
     * @param $key
     * @return string
     */
    public function getUrlByKey($key)
    {
        return $this->s3->getObjectUrl($this->bucket, $key);
    }

    public static function doesObjectExist($key, $bucket = S3_BUCKET)
    {
        $s3 = S3Client::factory(['credentials' => ['key' => AWS_ACCESS_KEY_ID, 'secret' => AWS_SECRET_ACCESS_KEY]]);

        return $s3->doesObjectExist($bucket, $key);
    }


    /**
     * TODO: this is not implement yet
     * @param $folder
     * @return $this
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

}