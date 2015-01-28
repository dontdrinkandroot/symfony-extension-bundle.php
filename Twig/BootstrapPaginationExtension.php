<?php


namespace Dontdrinkandroot\Symfony\ExtensionBundle\Twig;

use Dontdrinkandroot\Pagination\Pagination;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BootstrapPaginationExtension extends \Twig_Extension
{

    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function getName()
    {
        return "ddr_bootstrap_pagination";
    }

    public function getFunctions()
    {
        return array(
            'pagination' => new \Twig_SimpleFunction(
                    'pagination',
                    array($this, 'generatePagination'),
                    array(
                        'is_safe' => array('html')
                    )
                )
        );
    }

    /**
     * @param Pagination $pagination
     *
     * @return string
     */
    public function generatePagination($pagination, $route, $params)
    {
        $html = '<ul class="pagination">' . "\n";

        /* Render prev page */
        $cssClasses = array();
        if ($pagination->getCurrentPage() == 1) {
            $cssClasses[] = 'disabled';
        }
        $html .= $this->renderLink($pagination->getCurrentPage() - 1, '&laquo;', $route, $params, $cssClasses);

        $surroundingStartIdx = max(1, $pagination->getCurrentPage() - 2);
        $surroundingEndIdx = min($pagination->getTotalPages(), $pagination->getCurrentPage() + 2);

        /* Render first page */
        if ($surroundingStartIdx > 1) {
            $html .= $this->renderLink(1, 1, $route, $params);
        }

        /* Render dots */
        if ($surroundingStartIdx > 2) {
            $html .= '<li class="disabled"><a href="#">&hellip;</a></li>' . "\n";
        }

        /* Render surrounding pages */
        if ($pagination->getTotalPages() > 0) {
            for ($i = $surroundingStartIdx; $i <= $surroundingEndIdx; $i++) {
                $cssClasses = array();
                if ($i == $pagination->getCurrentPage()) {
                    $cssClasses[] = 'active';
                }
                $html .= $this->renderLink($i, $i, $route, $params, $cssClasses);
            }
        }

        /* Render dots */
        if ($surroundingEndIdx < $pagination->getTotalPages() - 1) {
            $html .= '<li class="disabled"><a href="#">&hellip;</a></li>' . "\n";
        }

        /* Render last page */
        if ($surroundingEndIdx < $pagination->getTotalPages()) {
            $html .= $this->renderLink($pagination->getTotalPages(), $pagination->getTotalPages(), $route, $params);
        }

        /* Render next page */
        $cssClasses = array();
        if ($pagination->getCurrentPage() >= $pagination->getTotalPages()) {
            $cssClasses[] = 'disabled';
        }
        $html .= $this->renderLink($pagination->getCurrentPage() + 1, '&raquo;', $route, $params, $cssClasses);

        $html .= '</ul>' . "\n";

        return $html;
    }

    public function renderLink($page, $text, $route, $params, $cssClasses = array())
    {
        $params['page'] = $page;
        $html = '<li class="' . implode(' ', $cssClasses) . '">';
        $html .= '<a href="' . $this->getPath($route, $params) . '">' . $text . '</a>';
        $html .= '</li>' . "\n";

        return $html;
    }

    public function getPath($name, $parameters = array(), $relative = false)
    {
        return $this->generator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
} 