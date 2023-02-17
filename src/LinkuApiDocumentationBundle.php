<?php
declare(strict_types=1);

namespace Linku\ApiDocumentationBundle;

use Linku\ApiDocumentationBundle\DependencyInjection\LinkuApiDocumentationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LinkuApiDocumentationBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new LinkuApiDocumentationExtension();
    }
}
