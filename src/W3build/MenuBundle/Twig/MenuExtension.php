<?php
/**
 * Created by PhpStorm.
 * User: lukas_jahoda
 * Date: 13.1.15
 * Time: 20:38
 */

namespace W3build\MenuBundle\Twig;


use W3build\MenuBundle\MenuLoader;

class MenuExtension extends \Twig_Extension {

    /**
     * @var MenuLoader
     */
    private $menuLoader;

    public function __construct(MenuLoader $menuLoader){
        $this->menuLoader = $menuLoader;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('menu', array($this, 'render'), array('pre_escape' => 'html', 'is_safe' => array('html'), 'needs_environment' => true)),
        );
    }



    public function render(\Twig_Environment $twigEnvironment, $type, $template){
        return $twigEnvironment->render($template, array('menuItems' => $this->menuLoader->getMenu($type)));
    }

    public function getName()
    {
        return 'menu';
    }

}