<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

use function Symfony\Component\String\u;

#[AsTwigComponent]
class Icon
{
    /**
     * Name of the icon file without extension (ex: `symfony-ux`).
     */
    public string $name;

    /**
     * The plain text version of the icon without any <i> or <span> wrapped element.
     */
    public bool $plain = false;

    /**
     * Accessibility label of the icon. Defaults to `name|title Icon` (ex: `Symfony Ux Icon`).
     */
    public ?string $label = null;

    /**
     * Remove XMLNS attribute on SVG tag (default).
     *
     * This attribute is required when the file is used as a distant asset (ex: an image src)
     * but is unnecessary when used directly in HTML code
     *
     * @see https://www.w3.org/TR/SVG/struct.html#Namespace
     */
    public bool $stripXmlns = true;

    protected string $iconDirectory;

    public function __construct()
    {
        $this->iconDirectory = __DIR__ . '/../../../../assets/admin/images/icons';
    }

    public function mount(string $name): void
    {
        $res = preg_match('/[A-Za-z0-9-]+/', $name);
        if ($res === 0 || $res === false) {
            throw new \InvalidArgumentException(sprintf('Icon name can only contain letters, digits or dashes, "%s" provided.', $this->name));
        }

        $this->name = $name;
    }

    #[ExposeInTemplate]
    public function getRawSvg(): string
    {
        $path = $this->iconDirectory . '/' . $this->name . '.svg';
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $path));
        }
        /** @var string $svg */
        $svg = file_get_contents($path);

        $svg = u($svg)->collapseWhitespace();

        if ($this->stripXmlns) {
            $svg = $svg->replace('xmlns="http://www.w3.org/2000/svg"', '')
                ->replace('xmlns:xlink="http://www.w3.org/1999/xlink"', '');
        }

        return $svg->toString();
    }

    #[ExposeInTemplate]
    public function getLabel(): string
    {
        return $this->label ??= u($this->name)->title(true)->append(' Icon')->toString();
    }
}
