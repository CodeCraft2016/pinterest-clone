<?php 
namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

class ImageManager
{
    private  $filesystem;


    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }


    public function removeImage($imagePath)
    {
        if($imagePath && is_file($imagePath))
        {
            try{

                $this->filesystem->remove($imagePath); // Delete the old image file

            }catch(\Exception $e)
            {
                dd($e);
            }
        }
    }
}