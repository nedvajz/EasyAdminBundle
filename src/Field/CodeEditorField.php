<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CodeEditorField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_INDENT_WITH_TABS = 'indentWithTabs';
    public const OPTION_LANGUAGE = 'language';
    public const OPTION_NUM_OF_ROWS = 'numOfRows';
    public const OPTION_TAB_SIZE = 'tabSize';

    private const ALLOWED_LANGUAGES = ['css', 'dockerfile', 'js', 'markdown', 'nginx', 'php', 'shell', 'sql', 'twig', 'xml', 'yaml-frontmatter', 'yaml'];

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFieldFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/code_editor')
            ->setFormType(CodeEditorType::class)
            ->addCssClass('field-code_editor')
            ->addCssFiles('bundles/easyadmin/form-type-code-editor.css')
            ->addJsFiles('bundles/easyadmin/form-type-code-editor.js')
            ->setCustomOption(self::OPTION_INDENT_WITH_TABS, false)
            ->setCustomOption(self::OPTION_LANGUAGE, 'markdown')
            ->setCustomOption(self::OPTION_NUM_OF_ROWS, null)
            ->setCustomOption(self::OPTION_TAB_SIZE, 4);
    }

    public function setIndentWithTabs(bool $useTabs): self
    {
        $this->setCustomOption(self::OPTION_INDENT_WITH_TABS, $useTabs);

        return $this;
    }

    public function setLanguage(string $language): self
    {
        if (!\in_array($language, self::ALLOWED_LANGUAGES, true)) {
            throw new \InvalidArgumentException(sprintf('The "%s" language is not available for code highlighting (allowed languages: %s).', __METHOD__, implode(', ', self::ALLOWED_LANGUAGES)));
        }

        $this->setCustomOption(self::OPTION_LANGUAGE, $language);

        return $this;
    }

    public function setNumOfRows(int $rows): self
    {
        if ($rows < 1) {
            throw new \InvalidArgumentException(sprintf('The argument of the "%s()" method must be 1 or higher (%d given).', __METHOD__, $rows));
        }

        $this->setCustomOption(self::OPTION_NUM_OF_ROWS, $rows);

        return $this;
    }

    public function setTabSize(int $tabSize): self
    {
        if ($tabSize < 1) {
            throw new \InvalidArgumentException(sprintf('The argument of the "%s()" method must be 1 or higher (%d given).', __METHOD__, $tabSize));
        }

        $this->setCustomOption(self::OPTION_TAB_SIZE, $tabSize);

        return $this;
    }
}
