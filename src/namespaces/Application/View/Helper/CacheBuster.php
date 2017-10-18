<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\View\Helper;

/**
 * Prevents browsers from serving outdated content via invalid cache entries.
 */
class CacheBuster
{
    private $bustfn = null;

    /**
     * Returns the default bust function that simply inserts the file's mtime
     * between the file extension and the file name i.e.: name.mtime.ext
     *
     * @return Function
     */
    public static function getDefaultBustFunction()
    {
        return function($path)
        {
            $str = $path;
            $realpath = getcwd() . "/public/$path";

            if(file_exists($realpath))
            {
                $split = explode('.', $path);
                if(count($split) > 1)
                {
                    $last = array_pop($split);
                    $str = ltrim(implode('.', $split), '\\/') . '.' . filemtime($realpath) . ".$last";
                }
            }

            return $str;
        };
    }
    
    public function __invoke($path = null)
    {
        if(isset($path))
        {
            clearstatcache();
            return $this->bustPath($path);
        }
        
        return $this;
    }

    /**
     * Take a file path that exists and apply the currently selected bust
     * function to it.
     *
     * @param  String $path The file to "bust"
     *
     * @return String       The busted path
     */
    public function bustPath($path)
    {
        $bustedpath = $path;
        $bustfn = $this->getBustFunction();

        if(is_callable($bustfn))
            $bustedpath = $bustfn($path);

        return $bustedpath;
    }

    /**
     * Set the path busting function that will be used by bustPath().
     */
    public function setBustFunction($bustfn)
    {
        $this->bustfn = $bustfn;
        return $this;
    }

    /**
     * Returns the path busting function that is currently used by bustPath().
     *
     * @return [type] [description]
     */
    public function getBustFunction()
    {
        if($this->bustfn === null)
            $this->bustfn = CacheBuster::getDefaultBustFunction();
        
        return $this->bustfn;
    }
}
