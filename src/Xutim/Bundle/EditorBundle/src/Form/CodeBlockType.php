<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Entity\Block\CodeBlock;

/**
 * @extends AbstractType<CodeBlock>
 */
final class CodeBlockType extends AbstractType
{
    private const array LANGUAGES = [
        'Plain text' => null,
        'PHP' => 'php',
        'JavaScript' => 'javascript',
        'TypeScript' => 'typescript',
        'HTML' => 'html',
        'CSS' => 'css',
        'SQL' => 'sql',
        'JSON' => 'json',
        'YAML' => 'yaml',
        'Bash' => 'bash',
        'Python' => 'python',
        'Ruby' => 'ruby',
        'Go' => 'go',
        'Rust' => 'rust',
        'Java' => 'java',
        'C#' => 'csharp',
        'C++' => 'cpp',
        'Markdown' => 'markdown',
        'XML' => 'xml',
        'Twig' => 'twig',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'class' => 'font-monospace',
                    'placeholder' => 'Paste code here...',
                ],
            ])
            ->add('language', ChoiceType::class, [
                'choices' => self::LANGUAGES,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CodeBlock::class,
        ]);
    }
}
