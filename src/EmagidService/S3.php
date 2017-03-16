<?php
/**
 * Created by PhpStorm.
 * User: zhou
 * Date: 3/16/17
 * Time: 11:00 AM
 */
namespace EmagidService;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3{

    private $s3;
    private $bucket;
    private $folder;

    /**
     * You have to define ACCESS KEY and SECRET KEY as const
     * TODO: change it use environment variable or read from file which will only exists on server
     */
    public function __construct($opt = [])
    {
        if(!isset($opt['key']) && !defined('AWS_ACCESS_KEY_ID')){
            throw new \Exception('AWS ACCESS KEY is required');
        }

        if(!isset($opt['secret']) && !defined('AWS_SECRET_ACCESS_KEY')){
            throw new \Exception('AWS SECRET KEY is required');
        }

        if(!isset($opt['bucket']) && !defined('S3_BUCKET')){
            throw new \Exception('AWS bucket name is required');
        }

        $key = isset($opt['key'])?$opt['key']:AWS_ACCESS_KEY_ID;
        $secret = isset($opt['secret'])?$opt['secret']:AWS_SECRET_ACCESS_KEY;
        $bucket = isset($opt['bucket'])?$opt['bucket']:S3_BUCKET;

        $this->s3 = S3Client::factory(['credentials' => ['key' => $key, 'secret' => $secret]]);
        $this->bucket = $bucket;
    }


    /**
     * $file either $_FILE[$file] or /path/to/file
     * @param $file
     * @param null $fileName
     * @return string | bool
     */
    public function upload($file, $fileName = null)
    {
        $openPath = is_array($file)?$file['tmp_name']:$file;

        if(!$fileName && is_array($file)){
            // $file is FILE and format filename
            $key = uniqid() . "_" . basename($file['name']);

            // replace (), - and space
            $key = str_replace(' ', '_', $key);
            $key = str_replace('-', '_', $key);
            $key = str_replace('(', '_', $key);
            $key = str_replace(')', '_', $key);
        } elseif(!is_array($file) && !$fileName) {
            // $file is STRING, take last part of /path/to/file
            $path = explode('/', $file);
            $key = $path[count($path) - 1];
        } else {
            $key = $fileName;
        }

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => fopen($openPath, 'r'),
                'ACL'    => 'public-read',
            ]);

            return $key;
        } catch (S3Exception $e) {
            // TODO: deal with s3 upload error notification
            return false;
        }
    }

    /**
     * @param $key
     * @return bool
     */
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

    /**
     * @param $key
     * @return bool
     */
    public function doesObjectExist($key)
    {
        return $this->s3->doesObjectExist($this->bucket, $key);
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