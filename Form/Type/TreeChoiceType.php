<?php

namespace CyberApp\TreeChoiceBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Translation\TranslatorInterface;

use CyberApp\TreeChoiceBundle\Form\DataTransformer\ScalarToArrayTransformer;

class TreeChoiceType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @param TranslatorInterface $translator           Translator object
     * @param null                $translationDomain    Translation domain
     */
    public function __construct(TranslatorInterface $translator = null, $translationDomain = null)
    {
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ScalarToArrayTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['js_tree'] = [
            'core' => [
                'strings' => [
                    'Loading ...' => $this->translator->trans('tree_choice.loading', [], $this->translationDomain),
                ],
                'multiple' => false,
            ],
            'plugins' => ['wholerow', ],
        ];

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $levelPropertyPath = new PropertyPath($options['level_property']);
        $parentPropertyPath = new PropertyPath($options['parent_property']);

        if ($options['multiple']) {
            $view->vars['js_tree']['core']['multiple'] = true;
            $view->vars['js_tree']['plugins'][] = 'checkbox';
        }

        if ($options['search']) {
            $view->vars['js_tree']['search'] = ['show_only_matches' => true, ];
            $view->vars['js_tree']['plugins'][] = 'search';
        }

        $data = [];
        $hasRoot = false;
        $minLevel = PHP_INT_MAX;
        $options['js_tree']['core']['data'] = &$data;
        foreach ($view->vars['choices'] as $choice) { /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice **/
            if (($level = $propertyAccessor->getValue($choice->data, $levelPropertyPath)) < $minLevel) {
                $minLevel = $level;
            }

            $parent = $propertyAccessor->getValue($choice->data, $parentPropertyPath);

            $item = [
                'id' => $choice->value,
                'text' => $choice->label,
                'level' => $level,
            ];

            if (null === $parent) {
                $hasRoot = true;
                $item['parent'] = '#';
            } else {
                $item['parent'] = $parent->getId();
            }

            if (in_array($choice->value, (array) $view->vars['value'])) {
                $item['state']['selected'] = true;
            }

            $data[] = $item;
        }

        if (! $hasRoot) {
            foreach ($data as &$item) {
                if ($item['level'] === $minLevel) {
                    $item['parent'] = '#';
                }
            }
        }

        $view->vars['search'] = $options['search'];
        $view->vars['js_tree'] = array_merge_recursive($view->vars['js_tree'], $options['js_tree']);
        $view->vars['delimiter'] = $options['delimiter'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'search' => true,
                'js_tree' => [],
                'delimiter' => ', ',
                'level_property' => null,
                'parent_property' => null,
            ])
            ->setAllowedTypes([
                'search' => 'boolean',
                'js_tree' => 'array',
                'delimiter' => ['null', 'string'],
                'level_property' => 'string',
                'parent_property' => 'string',
            ])
            ->setRequired('parent_property');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tree_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }
}
