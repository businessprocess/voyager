<?php

namespace TCG\Voyager\Http\Controllers\ContentTypes;

use Illuminate\Http\Request;

abstract class BaseType
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var
     */
    protected $slug;

    /**
     * @var
     */
    protected $row;

    /**
     * @var
     */
    protected $options;

    /**
     * Password constructor.
     *
     * @param Request $request
     * @param $slug
     * @param $row
     */
    public function __construct(Request $request, $slug, $row, $options)
    {
        $this->request = $request;
        $this->slug = $slug;
        $this->row = $row;
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    abstract public function handle();

    /**
     * Resize keeping the aspect ratio; the "upsize": false option prevents enlarging the image.
     *
     * @param \Intervention\Image\Interfaces\ImageInterface $image
     *
     * @return \Intervention\Image\Interfaces\ImageInterface
     */
    protected function scale($image, $width, $height)
    {
        $width = in_array($width, [null, '', 'null'], true) ? null : (int) $width;
        $height = in_array($height, [null, '', 'null'], true) ? null : (int) $height;

        if ($width === null && $height === null) {
            return $image;
        }

        if (isset($this->options->upsize) && !$this->options->upsize) {
            return $image->scaleDown($width, $height);
        }

        return $image->scale($width, $height);
    }
}
