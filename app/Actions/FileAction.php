<?php


namespace App\Actions;


class FileAction
{
    /**
     * @param $request
     * @param string $path
     * @param string $name
     * @return object
     */
    public static function upload($request, $path = 'public/attachments', $name = 'file'): object
    {
        $file = new \stdClass();

        $file->path = $request->file($name)->store($path);

        $file->type = $request->file($name)->getMimeType();

        return $file;
    }
}
