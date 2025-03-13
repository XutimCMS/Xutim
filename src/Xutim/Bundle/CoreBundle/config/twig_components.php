<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\CoreBundle\Twig\Components\Admin\Alert;
use Xutim\CoreBundle\Twig\Components\Admin\Badge;
use Xutim\CoreBundle\Twig\Components\Admin\Breadcrumbs;
use Xutim\CoreBundle\Twig\Components\Admin\BreadcrumbsArticle;
use Xutim\CoreBundle\Twig\Components\Admin\BreadcrumbsPage;
use Xutim\CoreBundle\Twig\Components\Admin\Button;
use Xutim\CoreBundle\Twig\Components\Admin\DataTable;
use Xutim\CoreBundle\Twig\Components\Admin\FileUpload;
use Xutim\CoreBundle\Twig\Components\Admin\Icon;
use Xutim\CoreBundle\Twig\Components\Admin\LanguageContextBar;
use Xutim\CoreBundle\Twig\Components\Admin\ListGroup;
use Xutim\CoreBundle\Twig\Components\Admin\ListGroupItem;
use Xutim\CoreBundle\Twig\Components\Admin\LiveTable;
use Xutim\CoreBundle\Twig\Components\Admin\Modal;
use Xutim\CoreBundle\Twig\Components\Admin\ModalDialog;
use Xutim\CoreBundle\Twig\Components\Admin\ModalForm;
use Xutim\CoreBundle\Twig\Components\Admin\Placeholder;
use Xutim\CoreBundle\Twig\Components\Admin\Sidebar;
use Xutim\CoreBundle\Twig\Components\Admin\SidebarItem;
use Xutim\CoreBundle\Twig\Components\Admin\SidebarSection;
use Xutim\CoreBundle\Twig\Components\Admin\SidebarTab;
use Xutim\CoreBundle\Twig\Components\Admin\SidebarTabs;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services
        ->set(Alert::class)
        ->tag('twig.component', [
            
            'key' => 'Xutim:Admin:Alert',
            'template' => '@XutimCore/components/Admin/Alert.html.twig'
        ])
    ;

    $services
        ->set(Badge::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Badge',
            'template' => '@XutimCore/components/Admin/Badge.html.twig'
        ])
    ;

    $services
        ->set(Breadcrumbs::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Breadcrumbs',
            'template' => '@XutimCore/components/Admin/Breadcrumbs.html.twig'
        ])
    ;

    $services
        ->set(BreadcrumbsArticle::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:BreadcrumbsArticle',
            'template' => '@XutimCore/components/Admin/BreadcrumbsArticle.html.twig'
        ])
    ;

    $services
        ->set(BreadcrumbsPage::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:BreadcrumbsPage',
            'template' => '@XutimCore/components/Admin/BreadcrumbsPage.html.twig'
        ])
    ;

    $services
        ->set(Button::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Button',
            'template' => '@XutimCore/components/Admin/Button.html.twig'
        ])
    ;

    $services
        ->set(DataTable::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:DataTable',
            'template' => '@XutimCore/components/Admin/DataTable.html.twig'
        ])
    ;

    $services
        ->set(FileUpload::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:FileUpload',
            'template' => '@XutimCore/components/Admin/FileUpload.html.twig'
        ])
    ;

    $services
        ->set(Icon::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Icon',
            'template' => '@XutimCore/components/Admin/Icon.html.twig'
        ])
    ;

    $services
        ->set(LanguageContextBar::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:LanguageContextBar',
            'template' => '@XutimCore/components/Admin/LanguageContextBar.html.twig'
        ])
    ;

    $services
        ->set(ListGroup::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:ListGroup',
            'template' => '@XutimCore/components/Admin/ListGroup.html.twig'
        ])
    ;
    $services
        ->set(ListGroupItem::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:ListGroupItem',
            'template' => '@XutimCore/components/Admin/ListGroupItem.html.twig'
        ])
    ;
    $services
        ->set(LiveTable::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:LiveTable',
            'template' => '@XutimCore/components/Admin/LiveTable.html.twig'
        ])
    ;
    $services
        ->set(Modal::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Modal',
            'template' => '@XutimCore/components/Admin/Modal.html.twig'
        ])
    ;
    $services
        ->set(ModalDialog::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:ModalDialog',
            'template' => '@XutimCore/components/Admin/ModalDialog.html.twig'
        ])
    ;
    $services
        ->set(ModalForm::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:ModalForm',
            'template' => '@XutimCore/components/Admin/ModalForm.html.twig'
        ])
    ;
    $services
        ->set(Placeholder::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Placeholder',
            'template' => '@XutimCore/components/Admin/Placeholder.html.twig'
        ])
    ;
    $services
        ->set(Sidebar::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:Sidebar',
            'template' => '@XutimCore/components/Admin/Sidebar.html.twig'
        ])
    ;
    $services
        ->set(SidebarItem::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:SidebarItem',
            'template' => '@XutimCore/components/Admin/SidebarItem.html.twig'
        ])
    ;
    $services
        ->set(SidebarSection::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:SidebarSection',
            'template' => '@XutimCore/components/Admin/SidebarSection.html.twig'
        ])
    ;
    $services
        ->set(SidebarTab::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:SidebarTab',
            'template' => '@XutimCore/components/Admin/SidebarTab.html.twig'
        ])
    ;
    $services
        ->set(SidebarTabs::class)
        ->tag('twig.component', [
            'key' => 'Xutim:Admin:SidebarTabs',
            'template' => '@XutimCore/components/Admin/SidebarTabs.html.twig'
        ])
    ;
};
