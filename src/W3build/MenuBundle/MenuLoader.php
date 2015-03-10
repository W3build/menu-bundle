<?php
/**
 * Created by PhpStorm.
 * User: lukas_jahoda
 * Date: 13.1.15
 * Time: 20:46
 */

namespace W3build\MenuBundle;

use Doctrine\Common\Cache\ApcCache;
use kcfinder\dir;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use W3build\MenuBundle\Menu;

class MenuLoader {

    /**
     * @var ApcCache
     */
    private $appCache;

    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var Menu\MenuCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $environment;

    public function __construct(ApcCache $apcCache, $kernelRootDir, $environment){
        $this->appCache = $apcCache;
        $this->kernelRootDir = $kernelRootDir;
        $this->environment = $environment;
    }

    private function load($type){
        $src = realpath($this->kernelRootDir . '/../src');
        $w3build = realpath($this->kernelRootDir . '/../vendor/w3build');

        $finder = new Finder();
        $finder->files()
            ->in(array($w3build, $src))
            ->name($type . '_menu.yml')
            ->followLinks();

        $this->collection = new Menu\MenuCollection();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach($finder as $file){
            $this->createCollection(Yaml::parse(file_get_contents($file->getRealPath())));
        }

        $this->collection->build();

        if($this->environment == 'prod'){
            $this->appCache->save('menu_' . $type, $this->collection);
        }
        return $this->collection;
    }

    public function createCollection($configs, $parent = null){
        foreach($configs as $key => $config){
            if(isset($config['parent'])){
                $parent = $config['parent'];
            }

            $menuItem = new Menu\MenuItem();
            $menuItem->setId($key)
                ->setLabel($config['label'])
                ->setAttr(isset($config['attr']) ? $config['attr'] : array())
                ->setRoute(isset($config['route']) ? $config['route'] : null)
                ->setSymlink(isset($config['symlink']) ? $config['symlink'] : null);

            $this->collection->add($menuItem, $parent);

            if(isset($config['children'])){
                $this->createCollection($config['children'], $key);
            }
        }
    }

    public function getMenu($type){
        if($this->environment == 'prod'){
            if($this->appCache->contains('menu_' . $type)){
                return unserialize($this->appCache->fetch('menu_' . $type));
            }
        }

        return $this->load($type);
    }

}