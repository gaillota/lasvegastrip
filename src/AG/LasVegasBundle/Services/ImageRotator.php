<?php

namespace AG\LasVegasBundle\Services;


class ImageRotator
{
    public function rotate($filepath)
    {
        $source = __DIR__.'/../../../../web/uploads/photos/'.$filepath;

        $exif = exif_read_data($source);
        $orientation = isset($exif['Orientation']) ? $exif['Orientation'] : null;

        if (null === $orientation)
            return;

        $image = imagecreatefromjpeg($source);
        $rotate = null;

        switch($orientation)
        {
            case 3:
                $rotate = imagerotate($image, 180, 0);
                break;
            case 6:
                $rotate = imagerotate($image, -90, 0);
                break;
            case 8:
                $rotate = imagerotate($image, 90, 0);
                break;
        }

        if (null === $rotate)
            return;

        imagejpeg($rotate, $source);

        imagedestroy($image);
        imagedestroy($rotate);
    }

    public function rotateRight($filepath)
    {
        $source = __DIR__.'/../../../../web/uploads/photos/'.$filepath;

        $image = imagecreatefromjpeg($source);
        $rotate = imagerotate($image, -90, 0);
        imagejpeg($rotate, $source);

        imagedestroy($image);
        imagedestroy($rotate);
    }

    public function rotateLeft($filepath)
    {
        $source = __DIR__.'/../../../../web/uploads/photos/'.$filepath;

        $image = imagecreatefromjpeg($source);
        $rotate = imagerotate($image, 90, 0);
        imagejpeg($rotate, $source);

        imagedestroy($image);
        imagedestroy($rotate);
    }
} 